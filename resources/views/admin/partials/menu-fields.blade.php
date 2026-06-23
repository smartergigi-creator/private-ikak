@php
    $prefix = $prefix ?? '';
@endphp

<div class="mb-3">
    <label for="{{ $prefix }}menu_name" class="form-label">Menu Name</label>
    <input type="text" id="{{ $prefix }}menu_name" name="name" class="form-control" value="{{ old('name') }}"
        placeholder="Ex: News" required>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}menu_slug" class="form-label">Slug</label>
    <input type="text" id="{{ $prefix }}menu_slug" name="slug" class="form-control"
        value="{{ old('slug') }}" placeholder="Auto generated when empty">
    <small class="text-muted">Use <code>ebooks</code> for the existing eBooks mega menu item.</small>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}menu_url" class="form-label">URL</label>
    <input type="text" id="{{ $prefix }}menu_url" name="url" class="form-control"
        value="{{ old('url') }}" placeholder="/home, /websites, /contact">
</div>

<div class="mb-3">
    <label for="{{ $prefix }}menu_parent_id" class="form-label">Parent Menu</label>
    <select id="{{ $prefix }}menu_parent_id" name="parent_id" class="form-select">
        <option value="">None</option>
        @foreach ($parentMenus as $parentMenu)
            <option value="{{ $parentMenu->id }}" @selected(old('parent_id') == $parentMenu->id)>
                {{ $parentMenu->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}position_type" class="form-label">Menu Position</label>

    <select id="{{ $prefix }}position_type" name="position_type" class="form-select mb-2">
        <option value="after">After</option>
        <option value="before">Before</option>
    </select>

    <select id="{{ $prefix }}reference_menu_id" name="reference_menu_id" class="form-select">
        <option value="">-- First Menu --</option>

        @foreach ($parentMenus as $parentMenu)
            <option value="{{ $parentMenu->id }}">
                {{ $parentMenu->name }}
            </option>
        @endforeach
    </select>

    <small class="text-muted">
        Choose where this menu should appear.
    </small>
</div>

<div class="form-check form-switch">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" id="{{ $prefix }}menu_status" name="status" value="1" class="form-check-input"
        checked>
    <label class="form-check-label" for="{{ $prefix }}menu_status">Enabled</label>
</div>
