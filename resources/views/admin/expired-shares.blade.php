@extends('admin.layout')

@section('title', 'Expired Shares')

@section('content')
    @php
        $queryWithoutPage = request()->except('page');
        $sortUrl = function (string $key) use ($sort, $direction, $queryWithoutPage) {
            return route('admin.expiredShares', array_merge($queryWithoutPage, [
                'sort' => $key,
                'direction' => $sort === $key && $direction === 'asc' ? 'desc' : 'asc',
            ]));
        };
        $sortIcon = function (string $key) use ($sort, $direction) {
            if ($sort !== $key) {
                return 'bi-arrow-down-up';
            }

            return $direction === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
        };
    @endphp

    <style>
        .expired-shares-page .filters-card,
        .expired-shares-page .shares-card {
            border-radius: 8px;
        }

        .expired-shares-page .shares-card .card-header {
            background: #17a9bb;
            color: #fff;
            border-radius: 8px 8px 0 0;
        }

        .expired-shares-page .sort-link {
            color: inherit;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .expired-shares-page .share-url {
            background: #eaf5ff;
            border-radius: 6px;
            color: #005ea8;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            max-width: 220px;
            padding: .35rem .55rem;
            white-space: nowrap;
        }

        .expired-shares-page .share-url span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .expired-shares-page .expired-badge {
            background: #ffe2e5;
            color: #d71920;
            border-radius: 999px;
            padding: .35rem .75rem;
            font-weight: 700;
        }

        .expired-shares-page .action-btn {
            border: 1px solid #dce4ef;
            color: #20345f;
            height: 34px;
            width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="expired-shares-page">
        <div class="page-heading mb-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h3>Expired Shares</h3>
                    <p class="text-muted mb-0">View expired ebook share links.</p>
                </div>
                <div class="badge bg-light-danger text-danger fs-6">
                    Total: {{ $totalExpiredShares }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 filters-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.expiredShares') }}"
                    class="row g-3 align-items-end">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="col-12 col-lg-6">
                        <label for="expiredShareSearch" class="form-label">Search</label>
                        <input type="text" id="expiredShareSearch" name="search" value="{{ $search }}"
                            class="form-control" placeholder="Search by file, user, email, or link...">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label for="expiredSharePerPage" class="form-label">Entries</label>
                        <select id="expiredSharePerPage" name="per_page" class="form-select"
                            onchange="this.form.submit()">
                            @foreach ($allowedPerPage as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>

                    <div class="col-12 col-lg-2">
                        <a href="{{ route('admin.expiredShares') }}" class="btn btn-light w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 shares-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Expired Share Links List</h5>
                    <span>Showing {{ $ebooks->firstItem() ?? 0 }} to {{ $ebooks->lastItem() ?? 0 }} of
                        {{ $ebooks->total() }}</span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>
                                    <a href="{{ $sortUrl('file') }}" class="sort-link">
                                        File / Ebook <i class="bi {{ $sortIcon('file') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('shared_by') }}" class="sort-link">
                                        Shared By <i class="bi {{ $sortIcon('shared_by') }}"></i>
                                    </a>
                                </th>
                                <th>Share Link</th>
                                <th>
                                    <a href="{{ $sortUrl('share_date') }}" class="sort-link">
                                        Share Date <i class="bi {{ $sortIcon('share_date') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('expiry_date') }}" class="sort-link">
                                        Expiry Date <i class="bi {{ $sortIcon('expiry_date') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('views') }}" class="sort-link">
                                        Views <i class="bi {{ $sortIcon('views') }}"></i>
                                    </a>
                                </th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ebooks as $ebook)
                                @php
                                    $sharedBy = $ebook->sharedUser;
                                    $owner = $ebook->uploader ?? $ebook->uploadedByUser;
                                    $shareUrl = url('/flip-book/' . $ebook->slug);
                                    $shareDate = $ebook->created_at;
                                    $expiryDate = $ebook->share_expires_at
                                        ? \Carbon\Carbon::parse($ebook->share_expires_at)
                                        : null;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($ebooks->currentPage() - 1) * $ebooks->perPage() }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $ebook->title ?: $ebook->file_title }}</div>
                                        <small class="text-muted">{{ $ebook->file_title ?: '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $sharedBy->name ?? $owner->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $sharedBy->email ?? $owner->email ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="share-url"
                                            title="{{ $shareUrl }}">
                                            <span>{{ str_replace(['https://', 'http://'], '', $shareUrl) }}</span>
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </td>
                                    <td>{{ $shareDate?->format('d M Y h:i A') ?? '-' }}</td>
                                    <td>{{ $expiryDate?->format('d M Y h:i A') ?? '-' }}</td>
                                    <td>{{ (int) ($ebook->current_views ?? 0) }} / {{ (int) ($ebook->max_views ?? 0) ?: 'Unlimited' }}</td>
                                    <td><span class="expired-badge">Expired</span></td>
                                    <td>
                                        @if ($ebook->slug)
                                            <a href="{{ route('ebook.view', $ebook->slug) }}" target="_blank"
                                                rel="noopener" class="btn btn-sm action-btn" title="Preview ebook">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No expired shares found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $ebooks->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
