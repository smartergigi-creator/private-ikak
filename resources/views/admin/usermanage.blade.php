@extends('admin.layout')

@section('title', 'User Management')

@section('content')
    <div class="admin-category-page">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h3 class="mb-1">User Management</h3>
                <p class="text-muted mb-0">
                    Manage users, roles, and account access settings.
                </p>
            </div>

            <form class="user-search-form" method="GET" action="{{ route('admin.users') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search users..."
                        value="{{ $search ?? '' }}">
                    <button class="btn btn-info" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    @if (filled($search ?? ''))
                        <a class="btn btn-outline-secondary" href="{{ route('admin.users') }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show auto-fade-alert" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 user-table">
                        <thead>
                            <tr>
                                <th class="sticky-action text-center">Actions</th>
                                <th>#</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $index => $user)
                                <tr>
                                    <td class="sticky-action text-center">
                                        <div class="action-btn-group">

                                            @if (strtolower($user->name) !== 'steven')
                                                <!-- Edit -->
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal{{ $user->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <!-- Delete -->
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    ⭐ Protected
                                                </span>
                                            @endif

                                        </div>
                                    </td>

                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="user-profile-cell">
                                            <img src="{{ $user->profile_photo ? asset($user->profile_photo) : asset('admin/dist/assets/images/logo/userlogo.webp') }}"
                                                alt="{{ $user->name }} profile" class="user-profile-thumb">
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst($user->role) }}

                                            @if (strtolower($user->name) === 'steven' && strtolower($user->role) === 'admin')
                                                ⭐
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <form action="{{ route('admin.users.editDetails', $user->id) }}" method="POST"
                                                enctype="multipart/form-data">
                                                @csrf

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">

                                                    <div class="profile-upload-preview mb-3">
                                                        <img src="{{ $user->profile_photo ? asset($user->profile_photo) : asset('admin/dist/assets/images/logo/userlogo.webp') }}"
                                                            alt="{{ $user->name }} profile">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Profile Photo</label>
                                                        <input type="file" name="profile_photo" class="form-control"
                                                            accept="image/png,image/jpeg,image/webp">
                                                        <small class="text-muted">JPG, PNG or WEBP. Maximum 2 MB.</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">User Name</label>
                                                        <input type="text" name="name" class="form-control"
                                                            value="{{ $user->name }}" readonly required>
                                                    </div>



                                                    <div class="mb-3">
                                                        <label class="form-label">Role</label>
                                                        <select name="role" class="form-control" required>
                                                            <option value="user"
                                                                {{ $user->role == 'user' ? 'selected' : '' }}>
                                                                User
                                                            </option>

                                                            <option value="admin"
                                                                {{ $user->role == 'admin' ? 'selected' : '' }}>
                                                                Admin
                                                            </option>

                                                            <option value="specialproject"
                                                                {{ $user->role == 'specialproject' ? 'selected' : '' }}>
                                                                Special Project
                                                            </option>
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>

                                                    <button type="submit" class="btn btn-primary">
                                                        Update User
                                                    </button>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>

    </div>
@endsection

<style>
    .user-table {
        min-width: 1000px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .user-search-form {
        width: min(100%, 360px);
    }

    .user-table thead th {
        background: #f8fafc;
        font-weight: 600;
        white-space: nowrap;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .user-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        white-space: nowrap;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }

    .sticky-action {
        position: sticky;
        left: 0;
        z-index: 20;
        background: #fff !important;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        min-width: 130px;
    }

    .user-table thead .sticky-action {
        z-index: 30;
        background: #f8fafc !important;
    }

    .action-btn-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .action-btn-group .btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-profile-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .user-profile-thumb {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #d9f3fb;
        background: #f3fcfe;
        flex: 0 0 auto;
    }

    .profile-upload-preview {
        display: flex;
        justify-content: center;
    }

    .profile-upload-preview img {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #d9f3fb;
        background: #f3fcfe;
    }
</style>
