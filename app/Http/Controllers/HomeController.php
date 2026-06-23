<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ebook;
use App\Models\Category;
use App\Models\EbookIssueReport;

class HomeController extends Controller
{
    /**
     * USER HOME PAGE
     */
public function userHome(Request $request)
{ 
    $role = strtolower((string) session('user_role', 'guest'));
    $sessionRoles = collect(session('ikak_roles', []))
        ->map(fn ($role) => strtolower((string) $role))
        ->all();
    $isLoggedIn = session()->has('logged_in');
    $isMember = $role === 'member' || in_array('member', $sessionRoles, true);
    $isBranchChief = in_array($role, ['branch chief', 'bc'], true)
        || in_array('branch chief', $sessionRoles, true)
        || in_array('bc', $sessionRoles, true);
    $isOperator = $role === 'operator' || in_array('operator', $sessionRoles, true);
    $user = auth()->user()?->fresh();
    $canUploadNow = false;
    $canShareNow = false;

    // ✅ Admin and Operator always get upload & share permissions
    if ($isOperator) {
        $canUploadNow = true;
        $canShareNow = true;
    } else {
        // Existing permission checks for other roles (user, guest, etc.)
        if ($user) {
            $hasUnlimitedPdfAccess = $user->hasUnlimitedPdfAccess();
            $canUploadPermission = $hasUnlimitedPdfAccess || (bool) $user->can_upload;
            if ($canUploadPermission) {
                $uploadLimit = (int) $user->upload_limit;
                if ($hasUnlimitedPdfAccess || $uploadLimit === 0) {
                    $canUploadNow = true;
                } else {
                    $uploadedCount = Ebook::where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhere('uploaded_by', $user->id);
                    })
                    ->when($user->upload_reset_at, function ($q, $resetAt) {
                        $q->where('created_at', '>', $resetAt);
                    })
                    ->count();
                    $canUploadNow = $uploadedCount < $uploadLimit;
                }
            }

            $canSharePermission = $hasUnlimitedPdfAccess || (bool) $user->can_share;
            if ($canSharePermission) {
                $shareLimit = (int) $user->share_limit;
                if ($hasUnlimitedPdfAccess || $shareLimit === 0) {
                    $canShareNow = true;
                } else {
                    $activeShares = Ebook::where('shared_by', $user->id)
                        ->where('share_enabled', 1)
                        ->where(function ($q) {
                            $q->whereNull('share_expires_at')
                                ->orWhere('share_expires_at', '>', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('max_views')
                                ->orWhere('max_views', 0)
                                ->orWhereColumn('current_views', '<', 'max_views');
                        })
                        ->count();
                    $canShareNow = $activeShares < $shareLimit;
                }
            }
        }
    }

    // ----- FILTERING LOGIC STARTS HERE -----
    $selectedCategoryId = $request->integer('category') ?: null;
    $selectedSubcategoryId = $request->integer('subcategory') ?: null;
    $selectedRelatedSubcategoryId = $request->integer('related_subcategory') ?: null;
    $selectedYear = $request->integer('year') ?: null;

    if ($selectedCategoryId) {
        $isParentCategory = Category::where('id', $selectedCategoryId)
            ->whereNull('parent_id')
            ->exists();

        if (!$isParentCategory) {
            $selectedCategoryId = null;
            $selectedSubcategoryId = null;
            $selectedRelatedSubcategoryId = null;
        }
    }

    if ($selectedSubcategoryId) {
        $isValidSubcategory = Category::where('id', $selectedSubcategoryId)
            ->where('parent_id', $selectedCategoryId)
            ->exists();

        if (!$isValidSubcategory) {
            $selectedSubcategoryId = null;
            $selectedRelatedSubcategoryId = null;
        }
    }

    if ($selectedRelatedSubcategoryId) {
        $isValidRelatedSubcategory = Category::where('id', $selectedRelatedSubcategoryId)
            ->where('parent_id', $selectedSubcategoryId)
            ->exists();

        if (!$isValidRelatedSubcategory) {
            $selectedRelatedSubcategoryId = null;
        }
    }

    // --- BASE QUERY FOR COUNTS AND MAIN GRID ---
    $baseQuery = Ebook::query()->with([
        'coverPage' => function ($query) {
            $query->select([
                'ebook_pages.id',
                'ebook_pages.ebook_id',
                'ebook_pages.page_no',
                'ebook_pages.image_path',
            ]);
        },
    ])->whereNotNull('pdf_path')->where('category_id', 4); // Karate eBooks only

    // --- DYNAMIC COUNTS (Unaffected by Search/Filter) ---
    $publicCount = (clone $baseQuery)
        ->where(function($q) {
            $q->whereIn('access_role', ['public', 'guest'])
              ->orWhereNull('access_role');
        })->count();

    $memberCount = (clone $baseQuery)->where('access_role', 'member')->count();
    $bcCount = (clone $baseQuery)->where('access_role', 'bc')->count();
    $operatorCount = (clone $baseQuery)->where('access_role', 'operator')->count();

    // --- APPLY USER SEARCH/FILTERS TO MAIN QUERY (Your requested replacement) ---
    $query = clone $baseQuery;

    // Search
    if ($request->filled('search')) {
        $search = trim($request->search);

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('file_title', 'like', "%{$search}%")
              ->orWhere('author_name', 'like', "%{$search}%");
        });
    }

    // Category filter (Parent category)
    if ($selectedCategoryId) {
        $subcategoryIds = Category::where('parent_id', $selectedCategoryId)
            ->pluck('id')
            ->all();

        $relatedSubcategoryIds = empty($subcategoryIds)
            ? []
            : Category::whereIn('parent_id', $subcategoryIds)
                ->pluck('id')
                ->all();

        $query->where(function ($q) use (
            $selectedCategoryId,
            $subcategoryIds,
            $relatedSubcategoryIds
        ) {
            $q->where('category_id', $selectedCategoryId);

            if (!empty($subcategoryIds)) {
                $q->orWhereIn('subcategory_id', $subcategoryIds);
            }

            if (!empty($relatedSubcategoryIds)) {
                $q->orWhereIn('related_subcategory_id', $relatedSubcategoryIds);
            }
        });
    }

    // Subcategory
    if ($selectedSubcategoryId) {
        $query->where('subcategory_id', $selectedSubcategoryId);
    }

    // Related subcategory
    if ($selectedRelatedSubcategoryId) {
        $query->where('related_subcategory_id', $selectedRelatedSubcategoryId);
    }

    // Year
    if ($selectedYear) {
        $query->where('year', $selectedYear);
    }

    // Sort
    switch ($request->get('sort')) {
        case 'oldest':
            $query->oldest();
            break;

        case 'az':
            $query->orderBy('title');
            break;

        case 'za':
            $query->orderByDesc('title');
            break;

        default:
            $query->latest();
            break;
    }

    // Pagination
    $ebooks = $query->paginate(12)->withQueryString();

    // --- CATEGORY DROPDOWNS ---
    $categories = Category::whereNull('parent_id')
        ->with([
            'children' => function ($query) {
                $query->orderBy('name');
            },
            'children.children' => function ($query) {
                $query->orderBy('name');
            },
        ])
        ->orderBy('name')
        ->get();

    $subcategories = collect();
    if ($selectedCategoryId) {
        $subcategories = Category::where('parent_id', $selectedCategoryId)
            ->orderBy('name')
            ->get();
    }

    $relatedSubcategories = collect();
    if ($selectedSubcategoryId) {
        $relatedSubcategories = Category::where('parent_id', $selectedSubcategoryId)
            ->orderBy('name')
            ->get();
    }

    // --- YEARS DROPDOWN ---
    $minFilterYear = 2024;
    $currentYear = (int) now()->year;

    $availableYears = Ebook::query()
        ->whereNotNull('year')
        ->where('year', '>=', $minFilterYear)
        ->distinct()
        ->orderByDesc('year')
        ->pluck('year')
        ->map(fn ($year) => (int) $year)
        ->filter()
        ->values();

    $defaultYears = $currentYear >= $minFilterYear
        ? collect(range($currentYear, $minFilterYear))
        : collect([$currentYear]);

    $availableYears = $availableYears
        ->merge($defaultYears)
        ->unique()
        ->sortDesc()
        ->values();

    if ($selectedYear && !$availableYears->contains((int) $selectedYear)) {
        $selectedYear = null;
    }

    // --- RETURN VIEW WITH NEW VARIABLES ---
    return view('ebook.home', compact(
        'ebooks',
        'categories',
        'subcategories',
        'relatedSubcategories',
        'availableYears',
        'canUploadNow',
        'canShareNow',
        'selectedCategoryId',
        'selectedSubcategoryId',
        'selectedRelatedSubcategoryId',
        'selectedYear',
        'role',
        'publicCount',
        'memberCount',
        'bcCount',
        'operatorCount'
    ));
}

    public function reportedIssues(Request $request)
    {
        $user = auth()->user();
        $search = trim((string) $request->query('search', ''));

        $issues = EbookIssueReport::query()
            ->with([
                'ebook:id,title,slug',
                'reporter:id,name,email',
                'recipient:id,name,email',
            ])
            ->where('recipient_id', $user->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', '%' . $search . '%')
                        ->orWhere('page', 'like', '%' . $search . '%')
                        ->orWhereHas('ebook', function ($ebookQuery) use ($search) {
                            $ebookQuery->where('title', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('reporter', function ($reporterQuery) use ($search) {
                            $reporterQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $openCount = EbookIssueReport::where('recipient_id', $user->id)->count();
        $todayCount = EbookIssueReport::where('recipient_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        return view('ebook.reported-issues', compact(
            'issues',
            'search',
            'openCount',
            'todayCount'
        ));
    }

    public function websites()
    {
        $websiteLinks = collect(config('websites.links', []))
            ->filter()
            ->values();

        return view('website.index', compact('websiteLinks'));
    }

    public function browseCategory(Request $request, Category $category)
    {
        abort_if($category->parent_id !== null, 404);

        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'az');

        $childCategories = Category::where('parent_id', $category->id)
            ->orderBy('name')
            ->get()
            ->map(function (Category $childCategory) use ($category) {
                $relatedSubcategoryIds = Category::where('parent_id', $childCategory->id)
                    ->pluck('id')
                    ->all();

                $ebookCount = Ebook::query()
                    ->whereNotNull('pdf_path')
                    ->where(function ($query) use ($category, $childCategory, $relatedSubcategoryIds) {
                        $query->where(function ($subcategoryQuery) use ($category, $childCategory) {
                            $subcategoryQuery->where('category_id', $category->id)
                                ->where('subcategory_id', $childCategory->id);
                        });

                        if (!empty($relatedSubcategoryIds)) {
                            $query->orWhereIn('related_subcategory_id', $relatedSubcategoryIds);
                        }
                    })
                    ->count();

                $childCategory->ebook_count = $ebookCount;

                return $childCategory;
            });

        if ($search !== '') {
            $searchLower = strtolower($search);
            $childCategories = $childCategories
                ->filter(fn (Category $childCategory) => str_contains(strtolower($childCategory->name), $searchLower))
                ->values();
        }

        $childCategories = match ($sort) {
            'za' => $childCategories->sortByDesc('name')->values(),
            'count' => $childCategories->sortByDesc('ebook_count')->values(),
            default => $childCategories->sortBy('name')->values(),
        };

        return view('ebook.category-browse', compact(
            'category',
            'childCategories',
            'search',
            'sort'
        ));
    }
    
}
