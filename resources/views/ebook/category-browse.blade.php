@extends('layout.app')

@section('title', $category->name . ' e-Books')
@section('body-class', 'ebook-category-page')

@section('content')
    <section class="category-browse-shell">
        <nav class="category-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ url('/home') }}">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="{{ url('/home#ebooksSection') }}">eBooks</a>
            <i class="bi bi-chevron-right"></i>
            <span>{{ $category->name }}</span>
        </nav>

        <div class="category-browse-head">
            <div class="category-title-mark">
                <i class="bi bi-bookmark"></i>
            </div>
            <div>
                <h1>{{ $category->name }}</h1>
                <p>Explore and access e-books from {{ strtolower($category->name) }}.</p>
            </div>
        </div>

        <form class="category-toolbar" method="GET" action="{{ route('ebooks.category', $category) }}">
            <label class="category-search">
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="Search {{ strtolower($category->name) }}...">
                <i class="bi bi-search"></i>
            </label>

            <select class="category-select" id="categoryQuickOpen">
                <option value="">All {{ $category->name }}</option>
                @foreach ($childCategories as $childCategory)
                    <option value="{{ url('/home') }}?category={{ $category->id }}&subcategory={{ $childCategory->id }}#ebooksSection">
                        {{ $childCategory->name }}
                    </option>
                @endforeach
            </select>

            <select class="category-select" name="sort" id="categorySort">
                <option value="az" @selected($sort === 'az')>Sort by: A to Z</option>
                <option value="za" @selected($sort === 'za')>Sort by: Z to A</option>
                <option value="count" @selected($sort === 'count')>Sort by: Most files</option>
            </select>

            <div class="category-view-toggle" aria-label="View options">
                <button type="button" class="active" title="Grid view">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </button>
                <button type="button" title="List view">
                    <i class="bi bi-list-ul"></i>
                </button>
            </div>
        </form>

        <div class="category-card-grid">
            @forelse ($childCategories as $childCategory)
                @php
                    $initial = strtoupper(substr($childCategory->name, 0, 1));
                    $cardUrl = url('/home') . '?category=' . $category->id . '&subcategory=' . $childCategory->id . '#ebooksSection';
                @endphp
                <a href="{{ $cardUrl }}" class="category-card">
                    <span class="category-logo-mark">{{ $initial }}</span>
                    <span class="category-card-copy">
                        <strong>{{ $childCategory->name }}</strong>
                        <small>Explore {{ strtolower($childCategory->name) }} files and e-books.</small>
                    </span>
                    <span class="category-card-meta">
                        <span><i class="bi bi-journal-text"></i> {{ $childCategory->ebook_count }} e-books</span>
                        <i class="bi bi-arrow-right category-card-arrow"></i>
                    </span>
                </a>
            @empty
                <div class="category-empty-state">
                    <i class="bi bi-folder2-open"></i>
                    <h2>No items found</h2>
                    <p>Try another search or browse all files from this category.</p>
                </div>
            @endforelse
        </div>
    </section>

    <div class="category-bottom-bar">
        <a href="{{ url('/home#ebooksSection') }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Browse All Files</span>
        </a>
        <a href="{{ url('/home') }}?category={{ $category->id }}#ebooksSection">
            <i class="bi bi-cloud-arrow-up"></i>
            <span>View Category Files</span>
        </a>
        {{-- <a href="{{ route('reported-issues.index') }}">
            <i class="bi bi-flag"></i>
            <span>Reported Issues</span>
        </a> --}}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.category-toolbar');
            const searchInput = form?.querySelector('input[name="search"]');
            const sortSelect = document.getElementById('categorySort');
            const quickOpen = document.getElementById('categoryQuickOpen');
            let searchTimer = null;

            if (searchInput && form) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => form.submit(), 450);
                });
            }

            if (sortSelect && form) {
                sortSelect.addEventListener('change', () => form.submit());
            }

            if (quickOpen) {
                quickOpen.addEventListener('change', function() {
                    if (this.value) {
                        window.location.href = this.value;
                    }
                });
            }
        });
    </script>
@endsection
