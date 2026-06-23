@extends('layout.app')

@section('title', 'Websites')
@section('body-class', 'website-page')

@section('content')
    <section class="website-content">
        <div class="website-hero">
            <div>
                <span class="website-kicker">Directory</span>
                <h1>Website List</h1>
                <p>Open any company website directly from one place.</p>
            </div>

            <div class="website-summary-card">
                <span>Total Websites</span>
                <strong>{{ $websiteLinks->count() }}</strong>
            </div>
        </div>

        <div class="website-panel">

            <div class="website-panel-head">
                <div>
                    <h2>All Websites</h2>
                    <p>Click any website below to open it in a new tab.</p>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <select id="websiteSort" class="form-select" style="width: 150px;">
                        <option value="">Sort</option>
                        <option value="az">A - Z</option>
                        <option value="za">Z - A</option>
                    </select>

                    <input type="text" id="websiteSearch" class="form-control website-search"
                        placeholder="Search website...">

                    <a href="{{ url('/home') }}" class="btn btn-outline-primary rounded-pill px-3">
                        Back to Home
                    </a>
                </div>
            </div>

            <div class="website-table-container">
                <div class="website-table-wrap">
                    <table class="table website-table align-middle mb-0" id="websiteTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Website</th>
                                <th scope="col">URL</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($websiteLinks as $websiteLink)
                                <tr>
                                    <td>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>

                                    <td>
                                        <div class="website-name-cell">
                                            <span class="website-dot"></span>
                                            <div>
                                                <strong>{{ $websiteLink }}</strong>
                                                <small>Company Website</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="{{ 'https://' . $websiteLink }}" target="_blank" rel="noopener noreferrer"
                                            class="website-link-text">
                                            {{ 'https://' . $websiteLink }}
                                        </a>
                                    </td>

                                    <td class="text-end">
                                        <a href="{{ 'https://' . $websiteLink }}" target="_blank" rel="noopener noreferrer"
                                            class="btn btn-sm website-open-btn">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="noResults" class="text-center py-4 fw-semibold text-muted" style="display:none;">
                        No websites found.
                    </div>

                </div>
            </div>

        </div>
    </section>

    <style>
        .website-table-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .website-search {
            width: 280px;
            border-radius: 30px;
            padding: 10px 18px;
        }

        .website-table-wrap {
            overflow-x: auto;
        }

        .website-link-text {
            display: inline-block;
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const searchInput = document.getElementById('websiteSearch');
            const rows = document.querySelectorAll('#websiteTable tbody tr');
            const noResults = document.getElementById('noResults');

            searchInput.addEventListener('keyup', function() {

                const value = this.value.toLowerCase();
                let visibleRows = 0;

                rows.forEach(function(row) {

                    const text = row.textContent.toLowerCase();

                    if (text.includes(value)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }

                });

                noResults.style.display = visibleRows === 0 ? 'block' : 'none';

            });

        });

        const sortDropdown = document.getElementById('websiteSort');

        sortDropdown.addEventListener('change', function() {

            const tbody = document.querySelector('#websiteTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {

                const aText = a.querySelector('.website-name-cell strong')
                    .textContent.trim()
                    .toLowerCase();

                const bText = b.querySelector('.website-name-cell strong')
                    .textContent.trim()
                    .toLowerCase();

                if (this.value === 'az') {
                    return aText.localeCompare(bText);
                }

                if (this.value === 'za') {
                    return bText.localeCompare(aText);
                }

                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    </script>
@endsection
