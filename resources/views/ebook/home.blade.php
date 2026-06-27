@extends('layout.app')

@section('title', 'Private IKAK')
@section('body-class', 'ebook-home')

@section('content')

    <style>
        /* --- HIDE SIDEBAR ONLY - KEEP TOP NAVBAR --- */
        body.ebook-home .sidebar,
        body.ebook-home .side-nav,
        body.ebook-home .app-sidebar,
        body.ebook-home aside,
        body.ebook-home .sidebar-wrapper {
            display: none !important;
        }

        body.ebook-home .main-content,
        body.ebook-home .content-wrapper,
        body.ebook-home .page-content {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* --- OVERALL LAYOUT --- */
        body.ebook-home .container {
            max-width: 1440px;
            padding: 20px 25px 0 25px;
            margin-top: 0;
        }

        /* --- TOP HEADER --- */
        .library-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 55px;
        }

        .header-left .greeting {
            color: #e67e22;
            font-weight: 700;
            margin-bottom: -5px;
        }

        .header-left h1 {
            font-size: 2.6rem;
            font-weight: 800;
            color: #1c2e3a;
            margin-bottom: 5px;
        }

        .header-left .sub-text {
            color: #8a9baa;
            font-size: 1rem;
        }

        .header-right {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            margin-top: 10px;
        }

        /* Global Search */
        .global-search-wrapper {
            display: flex;
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            width: 100%;
            max-width: 420px;
        }

        .global-search-wrapper .input-group-text {
            background: transparent;
            border: none;
            color: #8a9baa;
            padding: 0 14px;
        }

        .global-search-wrapper input {
            border: none;
            padding: 11px 12px 11px 0;
            width: 100%;
            outline: none;
            font-size: 0.95rem;
        }

        .global-search-wrapper input::placeholder {
            color: #b0c1ce;
        }

        .btn-upload-main {
            background: #a6714b;
            color: #fff;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-upload-main:hover {
            background: #8c5b39;
            color: #fff;
        }

        /* --- 3 ROLE CARDS (LEFT ALIGNED) --- */
        .role-cards-wrapper {
            margin-bottom: 40px;
        }

        .role-card {
            position: relative;
            min-height: 260px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            padding: 30px 35px 40px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease;
            overflow: hidden;
            cursor: pointer;
        }

        .role-card:hover {
            transform: translateY(-5px);
        }

        .role-card-bc {
            background: #19375b;
            color: #fff;
        }

        .role-card-member {
            background: #efe3d2;
            color: #2c3e50;
        }

        .role-card-public {
            background: #a61d36;
            color: #fff;
        }

        .role-card-icon {
            font-size: 2.5rem;
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .role-card-member .role-card-icon {
            background: rgba(255, 255, 255, 0.6);
            color: #2c3e50;
        }

        .role-card h3 {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 10px;
        }

        .role-card p {
            font-size: 1rem;
            margin-bottom: 25px;
            opacity: 0.8;
        }

        .role-card .btn-explore {
            background: #fff;
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            font-weight: 600;
            color: #1c2e3a;
            text-decoration: none;
            transition: all 0.2s;
        }

        .role-card-member .btn-explore {
            background: #1c2e3a;
            color: #fff;
        }

        .role-card .btn-explore:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .wood-shelf {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 22px;
            background: #68462d;
            background-image: repeating-linear-gradient(90deg, transparent, transparent 12px, rgba(255, 255, 255, 0.05) 12px, rgba(255, 255, 255, 0.05) 13px);
        }

        /* --- SUB-FILTER & EBOOK GRID --- */
        .sub-filter-container {
            background: #fff;
            border-radius: 16px;
            padding: 25px 30px 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef3f8;
            margin-bottom: 30px;
        }

        .sub-filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 5px;
        }

        .sub-filter-header h4 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1c2e3a;
            margin-bottom: 2px;
        }

        .sub-filter-header small {
            color: #8a9baa;
        }

        /* --- 4-COLUMN GRID --- */
        .ebook-grid-container {
            display: flex !important;
            flex-wrap: wrap !important;
            margin: 0 -10px !important;
            width: 100% !important;
        }

        .ebook-grid-item {
            padding: 0 10px !important;
            margin-bottom: 24px !important;
            flex: 0 0 25% !important;
            max-width: 25% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            width: 100%;
        }

        @media (max-width: 1200px) {
            .ebook-grid-item {
                flex: 0 0 33.33% !important;
                max-width: 33.33% !important;
            }
        }

        @media (max-width: 992px) {
            .ebook-grid-item {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
        }

        @media (max-width: 576px) {
            .ebook-grid-item {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        /* Card Styling */
        .media-card {
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f4f8;
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .media-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .media-preview {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4.2;
            overflow: hidden;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pdf-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #e9573c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 10;
        }

        .role-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 8px !important;
            font-weight: 700;
            padding: 4px 6px !important;
            border-radius: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #fff;
            white-space: nowrap;
            max-width: 80px;
            text-overflow: ellipsis;
            overflow: hidden;
            z-index: 10;
        }

        .date-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(255, 255, 255, 0.9);
            color: #1c2e3a;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .media-content {
            padding: 16px 16px 18px;
            text-align: left;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            gap: 8px;
        }

        .media-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
            color: #1c2e3a;
        }

        .media-meta {
            font-size: 0.85rem;
            color: #7b8b9e;
        }

        .media-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
        }

        .btn-view-book {
            flex: 1;
            background: #a6714b;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-view-book:hover {
            background: #8c5b39;
            color: #fff;
        }

        .btn-share-book {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7b8b9e;
            transition: 0.2s;
        }

        .btn-share-book:hover {
            background: #f8fafc;
            border-color: #b0c1ce;
        }

        /* --- FILTERS & PAGINATION --- */
        .filter-toolbar .row {
            align-items: flex-end;
            width: 100%;
            margin: 0;
        }

        .filter-toolbar .row>div {
            padding-left: 6px;
            padding-right: 6px;
        }

        .filter-toolbar .form-control,
        .filter-toolbar .form-select,
        .filter-toolbar .btn-filters-brown,
        .filter-toolbar .btn-primary.btn-sm {
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            font-size: 0.9rem;
        }

        .filter-toolbar .btn-filters-brown {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: #34495e;
            padding: 0 16px;
        }

        .filter-toolbar .btn-link {
            height: 38px;
            display: flex;
            align-items: center;
        }

        .filter-toolbar .input-group-text {
            height: 38px;
            border-radius: 6px 0 0 6px !important;
        }

        .filter-toolbar .form-control.border-start-0 {
            border-radius: 0 6px 6px 0 !important;
        }





        .pagination-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            gap: 8px;
            margin-bottom: 0;
        }

        .pagination .page-link {
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            color: #1c2e3a !important;
            border-radius: 8px !important;
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
            transition: all .2s ease;
        }

        .pagination .page-link:hover {
            background: #a6714b !important;
            border-color: #a6714b !important;
            color: #fff !important;
        }

        .pagination .page-item.active .page-link {
            background: #a6714b !important;
            border-color: #a6714b !important;
            color: #fff !important;
        }

        /* .pagination .page-item.disabled .page-link {
                                                                                        background: #f8fafc !important;
                                                                                        color: #b0b7c3 !important;
                                                                                        border-color: #e2e8f0 !important;
                                                                                    } */

        @media (max-width: 768px) {
            .header-left h1 {
                font-size: 2rem;
            }

            .global-search-wrapper {
                max-width: 100%;
            }

            .sub-filter-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-toolbar .row>div {
                width: 100%;
                margin-bottom: 10px;
            }

            .role-card {
                min-height: 200px;
                padding: 20px;
            }

            .wood-shelf {
                height: 14px;
            }
        }

        @media (max-width: 576px) {
            .header-right {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .header-right .btn,
            .header-right .global-search-wrapper {
                width: 100%;
                justify-content: center;
            }

            .role-card {
                min-height: 160px;
            }

            .sub-filter-container {
                padding: 15px;
            }
        }

        .access-role-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .access-role-card {
            position: relative;
            cursor: pointer;
        }

        .access-role-card input {
            display: none;
        }

        .access-role-card span {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            background: #fff;
            font-weight: 600;
            color: #555;
            transition: .3s;
        }

        .access-role-card input:checked+span {
            background: #0dcaf0;
            color: #fff;
            border-color: #0dcaf0;
            box-shadow: 0 8px 20px rgba(13, 202, 240, .25);
        }

        .access-role-card:hover span {
            transform: translateY(-2px);
        }
    </style>

    <!-- 1. TOP HEADER -->
    <div class="library-header">
        <div class="header-left">
            <h1>IKAK EBOOK LIBRARY</h1>
            <div class="sub-text">Access and manage ebooks across different categories and views.</div>
        </div>
        <div class="header-right">

            {{-- @if ($canUploadNow) --}}
            {{-- <button type="button" class="btn-upload-main" id="openUploadMetaModal">
                    <i class="bi bi-cloud-upload-fill"></i> UPLOAD EBOOK
                </button> --}}
            {{-- @endif --}}
        </div>
    </div>

    <!-- 2. THREE ROLE CARDS (LEFT ALIGNED & SAFE COUNTS) -->
    <div class="row g-4 role-cards-wrapper">
        <div class="col-xl-4 col-md-4">
            <div class="role-card role-card-public">
                <div class="role-card-icon"><i class="bi bi-globe"></i></div>
                <h3>PUBLIC<br>VIEW</h3>
                <p>{{ $publicCount ?? 0 }} Ebooks</p>
                <a href="#" class="btn-explore">Explore</a>
                <div class="wood-shelf"></div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="role-card role-card-member">
                <div class="role-card-icon"><i class="bi bi-people"></i></div>
                <h3>MEMBERS<br>VIEW</h3>
                <p>{{ $memberCount ?? 0 }} Ebooks</p>
                <a href="#" class="btn-explore">Explore</a>
                <div class="wood-shelf"></div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="role-card role-card-bc">
                <div class="role-card-icon"><i class="bi bi-book"></i></div>
                <h3>BRANCH CHIEF<br>VIEW</h3>
                <p>{{ $bcCount ?? 0 }} Ebooks</p>
                <a href="#" class="btn-explore">Explore</a>
                <div class="wood-shelf"></div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN EBOOK SECTION -->
    <div id="ebooksSection" style="mt-3">
        <div class="sub-filter-container">
            <div class="sub-filter-header">
                <div>
                    <h4>ALL AVAILABLE EBOOKS</h4>
                    <small>Showing ebooks accessible to your current role</small>
                </div>
                @if ($canUploadNow)
                    <button type="button" class="btn btn-sm btn-light" id="openUploadMetaModal"
                        style="border-radius:6px; padding:6px 14px;">
                        <i class="bi bi-cloud-upload"></i> + Upload
                    </button>
                @endif
            </div>

            <!-- CLEAN FILTER FORM (NO HIDDEN INPUT) -->
            <form method="GET" action="{{ url('/home') }}" id="ebookFilterForm">
                <div class="filter-toolbar">
                    <div class="row g-3 align-items-end w-100">
                        <!-- Search -->
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-search text-muted"></i></span>
                                <input type="text" id="bottomSearchInput" name="search"
                                    class="form-control border-start-0" placeholder="Search ebooks..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Category -->
                        {{-- <div class="col-xl-2 col-lg-4 col-md-6">
                            <select class="form-select" name="category" id="categorySelect">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($selectedCategoryId == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}

                        <!-- Year -->
                        <div class="col-xl-2 col-lg-4 col-md-6">
                            <select class="form-select" name="year" id="yearSelect">
                                <option value="">All Years</option>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected((int) ($selectedYear ?? 0) === (int) $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filters & Clear -->
                        <div class="col-xl-2 col-lg-4 col-md-6 d-flex gap-2">
                            <button type="button" class="btn-filters-brown">
                                <i class="bi bi-funnel-fill text-warning"></i> Filters
                            </button>
                            <a href="{{ url('/home') }}" class="btn btn-link text-decoration-none text-secondary"
                                style="font-size:0.9rem;" id="clearFilterBtn">
                                Clear
                            </a>
                        </div>

                        <!-- Sort & Grid View -->
                        <div
                            class="col-xl-3 col-lg-4 col-md-6 d-flex justify-content-xl-end align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">Sort by:</span>
                            <select class="form-select form-select-sm" name="sort"
                                style="width: auto; min-width: 110px; border-color:#e2e8f0; border-radius:6px;">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm"
                                style="background:#a6714b; border:none; border-radius:6px; padding:0 10px; height:34px;">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- EBOOK GRID -->
            <div id="ebookResults" class="mt-4">
                <div class="ebook-grid-container">
                    @forelse ($ebooks as $book)
                        @php
                            $fileUrl = asset(ltrim(str_replace('\\', '/', $book->pdf_path), '/'));
                            $extension = strtoupper(pathinfo($book->pdf_path, PATHINFO_EXTENSION) ?: 'FILE');
                            $displayDate = $book->created_at?->format('d M Y');
                            $roleColors = [
                                'public' => '#a61d36',
                                'member' => '#f39c12',
                                'bc' => '#19375b',
                                'operator' => '#444444',
                            ];
                            $roleLabel = strtoupper($book->access_role);
                            if ($roleLabel == 'BC') {
                                $roleLabel = 'BRANCH CHIEF';
                            }
                            if ($roleLabel == 'MEMBER') {
                                $roleLabel = 'MEMBER';
                            }
                            if ($roleLabel == 'PUBLIC') {
                                $roleLabel = 'PUBLIC';
                            }
                            if ($roleLabel == 'OPERATOR') {
                                $roleLabel = 'OPERATOR';
                            }
                        @endphp

                        <div class="ebook-grid-item">
                            <div class="book-wrapper media-card">
                                <div class="media-preview">
                                    <img src="{{ asset('images/homecover.png') }}" data-pdf-cover="1"
                                        data-pdf-url="{{ $fileUrl }}" alt="{{ $book->title }} cover">
                                    {{-- <div class="pdf-badge">{{ $extension }}</div> --}}
                                    <div class="role-badge"
                                        style="background-color: {{ $roleColors[$book->access_role] ?? '#555' }};">
                                        {{ $roleLabel }}
                                    </div>
                                    <div class="date-badge">{{ filled($book->year) ? $book->year : $displayDate }}</div>
                                </div>
                                <div class="media-content">
                                    <h6 class="media-title">{{ $book->title }}</h6>
                                    @if (filled($book->author_name))
                                        <div class="media-meta">Author: {{ $book->author_name }}</div>
                                    @endif
                                    <div class="media-actions">
                                        <a href="{{ route('ebook.view', $book->slug) }}" class="btn-view-book">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @if ($canShareNow)
                                            <button type="button" class="btn-share-book share-action-btn"
                                                onclick="openShareModal('{{ $book->slug }}')" title="Share">
                                                <i class="bi bi-share"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">No ebooks found.</p>
                        </div>
                    @endforelse
                </div>
                <div class="pagination-wrapper">
                    {{ $ebooks->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <input type="text" id="shareLinkInput" class="form-control d-none" readonly tabindex="-1" aria-hidden="true">

    <!-- ========================================== -->
    <!-- UPLOAD MODAL (Preserved)                     -->
    <!-- ========================================== -->
    @if ($canUploadNow)
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="modal fade" id="uploadMetaModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Upload eBook</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="file" id="pdfInput" name="pdfs[]" multiple hidden>
                            <input type="file" id="folderInput" name="pdfs[]" webkitdirectory directory multiple
                                hidden>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <button type="button" class="btn btn-outline-primary" id="selectFiles">
                                    Select File(s)
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="selectFolder">
                                    Select Folder
                                </button>
                            </div>

                            <div id="fileList" class="border rounded p-3 mb-3" style="display:none;">
                                <strong>Selected Files (<span id="fileCount">0</span>)</strong>
                                <ul id="fileItems" class="mb-0 mt-2"></ul>
                            </div>

                            <p class="text-muted small mb-3">
                                All file types can be uploaded here. PDF files open as ebooks, and
                                photos/videos/other files appear as common items on the home page.
                            </p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="ebook_name" class="form-control" placeholder="Enter title"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Author Name</label>
                                <input type="text" name="author_name" class="form-control"
                                    placeholder="Enter author name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date</label>
                                <input type="date" name="achievement_date" class="form-control"
                                    value="{{ old('achievement_date', request('achievement_date')) }}">
                            </div>

                            <input type="hidden" name="category_id" value="4">

                            <div class="mb-3" id="uploadSubCategoryField" style="display:none;">
                                <label class="form-label fw-semibold">Sub Category</label>
                                <select name="subcategory_id" id="uploadSubCategorySelect" class="form-select">
                                    <option value="">Select Subcategory</option>
                                </select>
                            </div>

                            <div class="mb-3" id="uploadRelatedSubCategoryField" style="display:none;">
                                <label class="form-label fw-semibold">Related Sub Category</label>
                                <select name="related_subcategory_id" id="uploadRelatedSubCategorySelect"
                                    class="form-select">
                                    <option value="">Select Related Subcategory</option>
                                </select>
                            </div>

                            <div id="uploadStatus" class="upload-status mt-3" style="display:none;">
                                <span class="spinner"></span>
                                <span class="text">Uploading ebook... Please wait</span>
                            </div>
                        </div>

                        <div class="mb-3 ms-4">
                            <label class="form-label fw-semibold d-block mb-2">
                                Access Role
                            </label>

                            <div class="access-role-group">

                                <label class="access-role-card">
                                    <input type="radio" name="access_role" value="public" required>
                                    <span>🌐 Public</span>
                                </label>

                                <label class="access-role-card">
                                    <input type="radio" name="access_role" value="member">
                                    <span>👥 Members</span>
                                </label>

                                <label class="access-role-card">
                                    <input type="radio" name="access_role" value="branch_chief">
                                    <span>🥋 Branch Chief</span>
                                </label>

                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Upload & Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <!-- ========================================== -->
    <!-- SCRIPTS (Fixed Filter Submissions)          -->
    <!-- ========================================== -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.pdfjsLib) return;

            const coverImages = Array.from(document.querySelectorAll('img[data-pdf-cover="1"][data-pdf-url]'));
            if (!coverImages.length) return;

            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            const renderCover = async (imgEl) => {
                const pdfUrl = imgEl.getAttribute('data-pdf-url');
                if (!pdfUrl) return;

                try {
                    const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({
                        scale: 0.8
                    });

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = Math.floor(viewport.width);
                    canvas.height = Math.floor(viewport.height);

                    await page.render({
                        canvasContext: ctx,
                        viewport
                    }).promise;

                    imgEl.src = canvas.toDataURL('image/jpeg', 0.9);
                } catch (e) {
                    // Keep default placeholder image if PDF render fails.
                }
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        renderCover(entry.target);
                        obs.unobserve(entry.target);
                    });
                }, {
                    rootMargin: '120px 0px'
                });

                coverImages.forEach((imgEl) => observer.observe(imgEl));
            } else {
                coverImages.forEach((imgEl) => renderCover(imgEl));
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // ... (keep the PDF cover rendering, filter form, video handling code above as is) ...

            // -------------------- UPLOAD MODAL --------------------
            const uploadForm = document.getElementById('uploadForm');
            const openModalBtn = document.getElementById('openUploadMetaModal');
            const uploadModalEl = document.getElementById('uploadMetaModal');
            const pdfInput = document.getElementById('pdfInput');
            const folderInput = document.getElementById('folderInput');

            // ✅ Removed: const categorySelect = document.getElementById('uploadCategorySelect');
            const subCategorySelect = document.getElementById('uploadSubCategorySelect');
            const relatedSubCategorySelect = document.getElementById('uploadRelatedSubCategorySelect');

            const subCategoryField = document.getElementById('uploadSubCategoryField');
            const relatedSubCategoryField = document.getElementById('uploadRelatedSubCategoryField');

            // ✅ Updated condition – no longer checks categorySelect
            if (!uploadForm || !openModalBtn || !uploadModalEl || !subCategorySelect) return;

            const uploadModal = new bootstrap.Modal(uploadModalEl);

            const toggleField = (field, show) => {
                if (!field) return;
                field.style.display = show ? '' : 'none';
            };

            const resetSubCategories = () => {
                subCategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            };

            const resetRelatedSubCategories = () => {
                if (!relatedSubCategorySelect) return;
                relatedSubCategorySelect.innerHTML = '<option value="">Select Related Subcategory</option>';
            };

            const loadChildren = (parentId, targetSelect, placeholder) => {
                targetSelect.innerHTML = `<option value="">${placeholder}</option>`;
                if (!parentId) return Promise.resolve([]);

                return fetch('/get-subcategories/' + encodeURIComponent(parentId), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json'
                        }
                    })
                    .then((res) => res.json())
                    .then((items) => {
                        if (!Array.isArray(items)) return [];
                        items.forEach((item) => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            targetSelect.appendChild(option);
                        });
                        return items;
                    })
                    .catch(() => []);
            };

            // ✅ Subcategory loading now happens when the modal is opened
            openModalBtn.addEventListener('click', function() {
                uploadModal.show();

                const KARATE_CATEGORY_ID = 4;

                // Reset both subcategory selects
                resetSubCategories();
                resetRelatedSubCategories();

                // Load subcategories for the fixed category (Karate)
                loadChildren(
                    KARATE_CATEGORY_ID,
                    subCategorySelect,
                    'Select Sub Category'
                ).then((items) => {
                    toggleField(subCategoryField, items.length > 0);
                    subCategorySelect.required = items.length > 0;
                });

                // Initially hide related subcategory field
                toggleField(relatedSubCategoryField, false);
                relatedSubCategorySelect.required = false;
            });

            // Subcategory change → load related subcategories
            subCategorySelect.addEventListener('change', function() {
                const subCategoryId = this.value;
                if (!relatedSubCategorySelect) return;

                resetRelatedSubCategories();
                relatedSubCategorySelect.required = false;

                if (!subCategoryId) {
                    toggleField(relatedSubCategoryField, false);
                    return;
                }

                loadChildren(subCategoryId, relatedSubCategorySelect, 'Select Related Subcategory').then((
                    items) => {
                    const hasItems = items.length > 0;
                    toggleField(relatedSubCategoryField, hasItems);
                    relatedSubCategorySelect.required = hasItems;
                });
            });

            // Upload form submission
            uploadForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(uploadForm);
                const selectedFiles = [];

                if (pdfInput && pdfInput.files?.length) {
                    selectedFiles.push(...Array.from(pdfInput.files));
                }
                if (folderInput && folderInput.files?.length) {
                    selectedFiles.push(...Array.from(folderInput.files));
                }

                if (!selectedFiles.length) {
                    alert('Please select at least one file.');
                    return;
                }

                formData.delete('pdfs[]');
                selectedFiles.forEach((file) => {
                    formData.append('pdfs[]', file);
                });

                const uploadStatus = document.getElementById('uploadStatus');
                if (uploadStatus) uploadStatus.style.display = 'flex';

                try {
                    const res = await fetch('/ebooks/upload', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    const data = await res.json().catch(() => ({}));

                    if (!res.ok || !data.status) {
                        throw new Error(data.message || 'Upload failed. Check file size or format.');
                    }

                    alert(data.message || 'Upload successful');
                    window.location.reload();
                } catch (err) {
                    alert(err.message || 'Upload failed');
                } finally {
                    if (uploadStatus) uploadStatus.style.display = 'none';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const canShareNow = @json((bool) $canShareNow);
            if (!canShareNow) {
                document.querySelectorAll('.share-action-btn').forEach((btn) => btn.remove());
            }

            const videoCards = Array.from(document.querySelectorAll('video[data-home-video="1"][data-video-id]'));

            const stopOtherVideos = (activeId = null) => {
                videoCards.forEach((videoEl) => {
                    if (activeId !== null && videoEl.dataset.videoId === String(activeId)) {
                        return;
                    }

                    videoEl.pause();
                    videoEl.currentTime = 0;
                });
            };

            document.querySelectorAll('.home-video-play').forEach((button) => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.targetVideo;
                    const targetVideo = document.querySelector(
                        `video[data-video-id="${targetId}"]`);
                    if (!targetVideo) return;

                    stopOtherVideos(targetId);
                    targetVideo.play().catch(() => {});
                });
            });

            document.querySelectorAll('.home-video-stop').forEach((button) => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.targetVideo;
                    const targetVideo = document.querySelector(
                        `video[data-video-id="${targetId}"]`);
                    if (!targetVideo) return;

                    targetVideo.pause();
                    targetVideo.currentTime = 0;
                });
            });

            const uploadForm = document.getElementById('uploadForm');
            const openModalBtn = document.getElementById('openUploadMetaModal');
            const uploadModalEl = document.getElementById('uploadMetaModal');
            const pdfInput = document.getElementById('pdfInput');
            const folderInput = document.getElementById('folderInput');

            const categorySelect = document.getElementById('uploadCategorySelect');
            const subCategorySelect = document.getElementById('uploadSubCategorySelect');
            const relatedSubCategorySelect = document.getElementById('uploadRelatedSubCategorySelect');

            const subCategoryField = document.getElementById('uploadSubCategoryField');
            const relatedSubCategoryField = document.getElementById('uploadRelatedSubCategoryField');

            if (!uploadForm || !openModalBtn || !uploadModalEl || !categorySelect || !subCategorySelect) return;

            const uploadModal = new bootstrap.Modal(uploadModalEl);

            const toggleField = (field, show) => {
                if (!field) return;
                field.style.display = show ? '' : 'none';
            };

            const resetSubCategories = () => {
                subCategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            };

            const resetRelatedSubCategories = () => {
                if (!relatedSubCategorySelect) return;
                relatedSubCategorySelect.innerHTML = '<option value="">Select Related Subcategory</option>';
            };

            const loadChildren = (parentId, targetSelect, placeholder) => {
                targetSelect.innerHTML = `<option value="">${placeholder}</option>`;

                if (!parentId) return Promise.resolve([]);

                return fetch('/get-subcategories/' + encodeURIComponent(parentId), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json'
                        }
                    })
                    .then((res) => res.json())
                    .then((items) => {
                        if (!Array.isArray(items)) return [];

                        items.forEach((item) => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            targetSelect.appendChild(option);
                        });

                        return items;
                    })
                    .catch(() => []);
            };

            openModalBtn.addEventListener('click', function() {
                uploadModal.show();
            });

            const KARATE_CATEGORY_ID = 4;

            loadChildren(
                KARATE_CATEGORY_ID,
                subCategorySelect,
                'Select Sub Category'
            ).then((items) => {

                toggleField(subCategoryField, items.length > 0);

                subCategorySelect.required = items.length > 0;
            });

            subCategorySelect.addEventListener('change', function() {
                const subCategoryId = this.value;
                if (!relatedSubCategorySelect) return;

                resetRelatedSubCategories();
                relatedSubCategorySelect.required = false;

                if (!subCategoryId) {
                    toggleField(relatedSubCategoryField, false);
                    return;
                }

                loadChildren(subCategoryId, relatedSubCategorySelect, 'Select Related Subcategory').then((
                    items) => {
                    const hasItems = items.length > 0;
                    toggleField(relatedSubCategoryField, hasItems);
                    relatedSubCategorySelect.required = hasItems;
                });
            });

            uploadForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(uploadForm);
                const selectedFiles = [];

                if (pdfInput && pdfInput.files?.length) {
                    selectedFiles.push(...Array.from(pdfInput.files));
                }
                if (folderInput && folderInput.files?.length) {
                    selectedFiles.push(...Array.from(folderInput.files));
                }

                if (!selectedFiles.length) {
                    alert('Please select at least one file.');
                    return;
                }

                formData.delete('pdfs[]');
                selectedFiles.forEach((file) => {
                    formData.append('pdfs[]', file);
                });

                const uploadStatus = document.getElementById('uploadStatus');
                if (uploadStatus) uploadStatus.style.display = 'flex';

                try {
                    const res = await fetch('/ebooks/upload', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')
                                ?.getAttribute('content') || '',
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    const data = await res.json().catch(() => ({}));

                    if (!res.ok || !data.status) {
                        throw new Error(data.message || 'Upload failed. Check file size or format.');
                    }

                    alert(data.message || 'Upload successful');
                    window.location.reload();
                } catch (err) {
                    alert(err.message || 'Upload failed');
                } finally {
                    if (uploadStatus) uploadStatus.style.display = 'none';
                }
            });
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

@endsection
