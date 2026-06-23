<!-- resources/views/partials/ebook-grid.blade.php -->
<div class="ebook-grid-container">
    @forelse ($ebooks as $book)
        @php
            $fileUrl = asset(ltrim(str_replace('\\', '/', $book->pdf_path), '/'));
            $isPdf = $book->isPdf();
            $isImage = $book->isImage();
            $isVideo = $book->isVideo();
            $extension = strtoupper($book->fileExtension() ?: 'FILE');
            $displayDate = $book->created_at?->format('d M Y');
        @endphp

        <div class="ebook-grid-item">
            <div class="book-wrapper media-card">
                <div class="media-preview">
                    @if ($isPdf)
                        <img src="{{ asset('images/homecover.png') }}" data-pdf-cover="1"
                            data-pdf-url="{{ $fileUrl }}" alt="{{ $book->title }} cover">
                        <div class="pdf-badge">{{ $extension }}</div>
                    @elseif ($isImage)
                        <a href="{{ route('ebook.view', $book->slug) }}" class="d-block h-100">
                            <img src="{{ $fileUrl }}" alt="{{ $book->title }} preview">
                        </a>
                    @elseif ($isVideo)
                        <video preload="metadata" muted playsinline data-home-video="1"
                            data-video-id="{{ $book->id }}" style="width:100%; height:100%; object-fit:cover;">
                            <source src="{{ $fileUrl }}">
                        </video>
                        <div class="video-preview-controls" aria-label="Video preview controls">
                            <button type="button" class="video-preview-btn home-video-play"
                                data-target-video="{{ $book->id }}" title="Play">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>
                    @else
                        <div
                            class="d-flex flex-column align-items-center justify-content-center h-100 w-100 bg-light px-3">
                            <i class="bi bi-file-earmark-text" style="font-size:3rem; color:#b0c1ce;"></i>
                            <span class="mt-2 fw-semibold text-secondary">{{ $extension }}</span>
                        </div>
                    @endif
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
                        @if ($canShareNow ?? false)
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
            <p class="text-muted">No ebooks found for this filter.</p>
        </div>
    @endforelse
</div>

<!-- PAGINATION -->
@if (method_exists($ebooks, 'links'))
    <div class="pagination-wrapper">
        {{ $ebooks->appends(request()->query())->links() }}
    </div>
@endif
