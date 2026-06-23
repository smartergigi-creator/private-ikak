@extends('admin.layout')

@section('title', 'Menu Management')

@section('content')
    <div class="admin-menu-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h3 class="mb-1">Menu Management</h3>
                <p class="text-muted mb-0">Create, edit, disable and order main navigation menu items</p>
            </div>
            <button type="button" class="btn login-style-btn btn-primary " data-bs-toggle="modal"
                data-bs-target="#addMenuModal">
                <i class="bi bi-plus-lg me-1"></i>
                Add New Menu
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Menu List</h5>
                <span class="badge bg-light text-dark">Total: {{ $menus->total() }}</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 menu-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center sticky-action-col">Actions</th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>URL</th>
                                <th>Parent</th>
                                <th>Order</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($menus as $menu)
                                <tr>
                                    <td class="sticky-action-col">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button"
                                                class="btn btn-sm action-btn action-btn-edit edit-menu-btn" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editMenuModal"
                                                data-update-url="{{ route('admin.menus.update', $menu->id) }}"
                                                data-menu-name="{{ $menu->name }}" data-menu-slug="{{ $menu->slug }}"
                                                data-menu-url="{{ $menu->url }}"
                                                data-parent-id="{{ $menu->parent_id ?? '' }}"
                                                data-sort-order="{{ $menu->sort_order }}" data-status="{{ $menu->status }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.menus.toggle', $menu->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm action-btn {{ $menu->status ? 'action-btn-disable' : 'action-btn-enable' }}"
                                                    title="{{ $menu->status ? 'Disable' : 'Enable' }}">
                                                    <i class="bi {{ $menu->status ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.menus.delete', $menu->id) }}"
                                                onsubmit="return confirm('Delete this menu? Child menu items will also be removed.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm action-btn action-btn-delete"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td>{{ $loop->iteration + ($menus->currentPage() - 1) * $menus->perPage() }}</td>
                                    <td class="fw-semibold">{{ $menu->name }}</td>
                                    <td><code>{{ $menu->slug }}</code></td>
                                    <td>{{ $menu->url ?: '-' }}</td>
                                    <td>{{ $menu->parent?->name ?? 'None' }}</td>
                                    <td>{{ $menu->sort_order }}</td>
                                    <td>
                                        <span class="badge {{ $menu->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $menu->status ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No menu items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $menus->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.menus.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMenuModalLabel">Add New Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.partials.menu-fields', [
                            'menu' => null,
                            'parentMenus' => $parentMenus,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn theme-outline-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn login-style-btn">Save Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-menu-form" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMenuModalLabel">Edit Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.partials.menu-fields', [
                            'menu' => null,
                            'parentMenus' => $parentMenus,
                            'prefix' => 'edit_',
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn theme-outline-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn login-style-btn">Update Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<style>
    .admin-menu-page .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
    }

    .admin-menu-page .menu-table {
        min-width: 980px;
    }

    .admin-menu-page .sticky-action-col {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 150px;
        background: #f4fbff;
        box-shadow: 2px 0 0 rgba(215, 230, 238, 0.95);
    }

    .admin-menu-page thead .sticky-action-col {
        z-index: 5;
    }

    .admin-menu-page .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #fff;
    }

    .admin-menu-page .action-btn-edit {
        background: #14c7bb;
        border-color: #14c7bb;
    }

    .admin-menu-page .action-btn-delete {
        background: #ef4444;
        border-color: #ef4444;
    }

    .admin-menu-page .action-btn-disable {
        background: #64748b;
        border-color: #64748b;
    }

    .admin-menu-page .action-btn-enable {
        background: #16a34a;
        border-color: #16a34a;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editForm = document.getElementById('edit-menu-form');
        const editName = document.getElementById('edit_menu_name');
        const editSlug = document.getElementById('edit_menu_slug');
        const editUrl = document.getElementById('edit_menu_url');
        const editParent = document.getElementById('edit_menu_parent_id');
        const editOrder = document.getElementById('edit_menu_sort_order');
        const editStatus = document.getElementById('edit_menu_status');

        document.querySelectorAll('.edit-menu-btn').forEach((button) => {
            button.addEventListener('click', () => {
                editForm.action = button.dataset.updateUrl;
                editName.value = button.dataset.menuName || '';
                editSlug.value = button.dataset.menuSlug || '';
                editUrl.value = button.dataset.menuUrl || '';
                editParent.value = button.dataset.parentId || '';
                editOrder.value = button.dataset.sortOrder || 0;
                editStatus.checked = button.dataset.status === '1';
            });
        });
    });
</script>
