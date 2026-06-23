<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;




class UserController extends Controller
{

public function index(Request $request)
{
    $search = trim((string) $request->query('search', ''));

    $users = User::query()
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%');
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('admin.usermanage', compact('users', 'search'));
}
public function updateUserDetails(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        // 'email' => 'required|email|unique:users,email,' . $id,
        'role' => 'required|in:admin,user,specialproject',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $data = [
        'name' => $request->name,
        // 'email' => $request->email,
        'role' => $request->role,
    ];

    if ($request->hasFile('profile_photo')) {
        if (env('FILE_ROOT') === 'public_html') {
        $uploadDir = dirname(base_path()) . '/public_html/uploads/profile-photos';
        } else {
        $uploadDir = public_path('uploads/profile-photos');
        }

        if (!File::exists($uploadDir)) {
        File::makeDirectory($uploadDir, 0755, true);
        }
        if ($user->profile_photo) {

            if (env('FILE_ROOT') === 'public_html') {
                $oldPhoto = dirname(base_path()) . '/public_html/' . $user->profile_photo;
            } else {
                $oldPhoto = public_path($user->profile_photo);
            }

            if (File::exists($oldPhoto)) {
                File::delete($oldPhoto);
            }
        }

        $file = $request->file('profile_photo');
        $filename = 'user-' . $user->id . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);
        $data['profile_photo'] = 'uploads/profile-photos/' . $filename;
    }

    $user->update($data);

    return redirect()
        ->route('admin.users')
        ->with('success', 'User updated successfully.');
}
public function update(Request $request, $id)
{
    $user = \App\Models\User::findOrFail($id);

    $data = $request->validate([
        'upload_limit' => ['nullable', 'integer', 'min:0'],
        'share_limit' => ['nullable', 'integer', 'min:0'],
    ]);

    $requestedCanUpload = $request->has('can_upload');
    $requestedUploadLimit = $requestedCanUpload ? (int) ($data['upload_limit'] ?? 0) : 0;
    $requestedCanShare = $request->has('can_share');
    $requestedShareLimit = $requestedCanShare ? (int) ($data['share_limit'] ?? 0) : 0;

    $uploadedCount = Ebook::where(function ($q) use ($user) {
        $q->where('user_id', $user->id)
            ->orWhere('uploaded_by', $user->id);
    })
        ->when($user->upload_reset_at, function ($q, $resetAt) {
            $q->where('created_at', '>', $resetAt);
        })
        ->count();

    $currentUploadLimit = (int) $user->upload_limit;
    $uploadReached = (bool) $user->can_upload
        && $currentUploadLimit > 0
        && $uploadedCount >= $currentUploadLimit;

    if ($uploadReached && ($requestedCanUpload || $requestedUploadLimit > 0)) {
        return back()
            ->withErrors([
                'limits' => "Upload limit reached for {$user->email}. Use Reset Uploads before setting a new upload limit.",
            ])
            ->withInput();
    }

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

    $reachedShareLinks = Ebook::where('shared_by', $user->id)
        ->where('share_enabled', 1)
        ->where(function ($q) {
            $q->whereNull('share_expires_at')
                ->orWhere('share_expires_at', '>', now());
        })
        ->whereNotNull('max_views')
        ->where('max_views', '>', 0)
        ->whereColumn('current_views', '>=', 'max_views')
        ->count();

    $currentShareLimit = (int) $user->share_limit;
    $shareReached = (bool) $user->can_share
        && $currentShareLimit > 0
        && ($activeShares >= $currentShareLimit || $reachedShareLinks > 0);

    if ($shareReached && ($requestedCanShare || $requestedShareLimit > 0)) {
        return back()
            ->withErrors([
                'limits' => "Share limit reached for {$user->email}. Use Reset Shares before setting a new share limit.",
            ])
            ->withInput();
    }

    // Update user permissions
    $user->can_upload   = $requestedCanUpload;
    $user->can_share    = $requestedCanShare;
    $user->upload_limit = $requestedUploadLimit;
    $user->share_limit  = $requestedShareLimit;

    $user->save();

    /* ============================
       🔥 Sync Share Limit → Ebook
    ============================ */

    return back()->with(
        'success',
        'User permissions updated successfully'
    );
}

public function destroy($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'admin') {
        return back()->withErrors([
            'delete_user' => 'Admin user cannot be deleted.',
        ]);
    }

    DB::beginTransaction();

    try {
        $ebooks = Ebook::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('uploaded_by', $user->id);
        })->get();

        foreach ($ebooks as $ebook) {
            $ebook->pages()->delete();

            $folderPath = public_path("ebooks/{$ebook->folder_path}");
            if (File::exists($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            $ebook->delete();
        }

        // Remove share ownership markers on remaining rows (if any)
        Ebook::where('shared_by', $user->id)->update([
            'shared_by' => null,
            'share_enabled' => 0,
            'share_token' => null,
            'share_expires_at' => null,
            'current_views' => 0,
            'max_views' => null,
        ]);

        if (DB::getSchemaBuilder()->hasTable('ebook_shares')) {
            DB::table('ebook_shares')
                ->where('shared_by', $user->id)
                ->delete();
        }

        if (DB::getSchemaBuilder()->hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }

        $user->delete();

        DB::commit();

        return back()->with('success', 'User and related ebooks deleted successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->withErrors([
            'delete_user' => 'User delete failed: ' . $e->getMessage(),
        ]);
    }
}




}
