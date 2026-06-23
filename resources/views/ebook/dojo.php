// ==================================================
// DOJO DIRECTORY SHORTCODE – PERMANENT FIX VERSION
// (with enhanced search: name, instructor, country, continent)
// ==================================================

add_action('init', function() {
    if (isset($_GET['clear_dojo_cache']) && current_user_can('manage_options')) {
        delete_transient('ikak_dojos_all');
        delete_transient('ikak_dojos_backup');
        delete_transient('ikak_dojos_cooldown');
        wp_redirect(remove_query_arg('clear_dojo_cache'));
        exit;
    }
});

add_shortcode('dojo_wp', 'ikak_simple_dojo_directory');

function ikak_simple_dojo_directory() {
    ob_start();

    // -------------------------------------------------
    // Helper: Fetch all dojos from external API (with fixes)
    // -------------------------------------------------
    function ikak_fetch_all_dojos() {
        $cache_key = 'ikak_dojos_all';
        $backup_key = 'ikak_dojos_backup';
        $cooldown_key = 'ikak_dojos_cooldown';

        // 1. Try to get cached data
        $cached = get_transient($cache_key);
        if ($cached !== false && is_array($cached) && !empty($cached)) {
            return $cached;
        }

        // 2. Check cooldown (prevent hammering a failing API)
        if (get_transient($cooldown_key) !== false) {
            $backup = get_option($backup_key, []);
            return $backup;
        }

        // 3. Fetch fresh from API (loop until hasNext is false)
        $all_dojos = [];
        $page = 1;
        $page_size = 100;
        $api_success = false;

        do {
            $url = add_query_arg([
                'page'     => $page,
                'pageSize' => $page_size,
            ], 'https://api-members.ikak.net/api/dojos');

            $response = wp_remote_get($url, [
                'timeout' => 15,
                'headers' => ['Accept' => 'application/json']
            ]);

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                break;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (empty($data['result']) || $data['result'] !== true || empty($data['data'])) {
                break;
            }

            $api_success = true;

            foreach ($data['data'] as $dojo) {
                $all_dojos[] = [
                    'id'              => $dojo['id'] ?? 0,
                    'dojo_name'       => $dojo['dojoName'] ?? 'Dojo',
                    'dojo_country'    => $dojo['country'] ?? 'N/A',
                    'dojo_address'    => $dojo['address'] ?? '',
                    'dojo_instructor' => $dojo['instructorName'] ?? 'N/A',
                    'dojo_image'      => $dojo['dojoImage'] ?? '',
                ];
            }

            if (empty($data['hasNext'])) {
                break;
            }
            $page++;
        } while (true);

        // 4. Handle caching with fallback
        if ($api_success && !empty($all_dojos)) {
            set_transient($cache_key, $all_dojos, 15 * MINUTE_IN_SECONDS);
            update_option($backup_key, $all_dojos);
            delete_transient($cooldown_key);
            return $all_dojos;
        } else {
            set_transient($cooldown_key, true, 5 * MINUTE_IN_SECONDS);
            if ($cached === false) {
                set_transient($cache_key, [], 5 * MINUTE_IN_SECONDS);
            }
            $backup = get_option($backup_key, []);
            if (!empty($backup)) {
                return $backup;
            }
            return $all_dojos;
        }
    }

    $dojos = ikak_fetch_all_dojos();
    $total_dojos = count($dojos);

    $countries = array_unique(array_column($dojos, 'dojo_country'));
    $countries = array_filter($countries);
    sort($countries);

    $continent_map = [
        'Asia' => ['china','india','japan','korea','thailand','vietnam','indonesia','malaysia','singapore','philippines','pakistan','bangladesh','nepal','sri lanka','myanmar','kazakhstan','uzbekistan','turkmenistan','kyrgyzstan','tajikistan','mongolia','brunei','cambodia','laos','timor-leste','bhutan','maldives','taiwan','hong kong','macau','azerbaijan','bahrain','afghanistan','iran','uae','palestine','lebanon','syria','iraq','jordan','kuwait','oman','qatar','saudi arabia','yemen'],
        'Africa' => ['egypt','nigeria','south africa','kenya','morocco','ghana','algeria','tunisia','libya','sudan','ethiopia','tanzania','uganda','rwanda','congo','angola','mozambique','zambia','zimbabwe','botswana','namibia','senegal','ivory coast','cameroon','benin','burkina faso','mali','malawi','mauritania','eritrea','somalia','chad','central african republic','equatorial guinea','gabon','republic of the congo','djibouti','comoros','seychelles','mauritius','burundi','madagascar'],
        'Europe' => ['united kingdom','france','germany','italy','spain','portugal','netherlands','belgium','switzerland','austria','sweden','norway','denmark','finland','ireland','poland','czech republic','hungary','romania','bulgaria','greece','turkey','russia','ukraine','belarus','lithuania','latvia','estonia','slovakia','slovenia','croatia','bosnia','serbia','montenegro','albania','macedonia','kosovo','iceland','luxembourg','monaco','andorra','malta','cyprus'],
        'North America' => ['united states','canada','mexico','guatemala','belize','honduras','el salvador','nicaragua','costa rica','panama','cuba','jamaica','haiti','dominican republic','puerto rico','bahamas','trinidad and tobago','barbados','st lucia','grenada','antigua','dominica','st vincent'],
        'South America' => ['brazil','argentina','chile','peru','colombia','venezuela','ecuador','bolivia','paraguay','uruguay','guyana','suriname','french guiana'],
        'Oceania' => ['australia','new zealand','fiji','papua new guinea','solomon islands','vanuatu','samoa','tonga','micronesia','marshall islands','palau','nauru','tuvalu','kiribati'],
        'Antarctica' => []
    ];

    $country_continent = [];
    foreach ($countries as $country) {
        $lower_country = strtolower($country);
        $found = false;
        foreach ($continent_map as $continent => $list) {
            if (in_array($lower_country, $list)) {
                $country_continent[$country] = $continent;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $country_continent[$country] = 'Asia';
        }
    }

    $continents = array_unique(array_values($country_continent));
    if (!in_array('Antarctica', $continents)) $continents[] = 'Antarctica';
    if (($key = array_search('Other', $continents)) !== false) unset($continents[$key]);
    sort($continents);

    $dojos_json = json_encode($dojos);
    $countries_json = json_encode($countries);
    $country_continent_json = json_encode($country_continent);
    $continents_json = json_encode($continents);

    // ------ HTML / CSS / JS starts here (same as before, but with updated search) ------
    ?>
    <style>
        /* ========== BASE STYLES ========== */
        .dojo-container{max-width:1200px;margin:auto;padding:40px 20px;}
        .dojo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:30px;}
        .dojo-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.08);transition:.3s;text-decoration:none;color:#000;display:block;}
        .dojo-card:hover{transform:translateY(-8px);}
        .dojo-img-wrapper{position:relative;}
        .dojo-card img{width:100%;height:180px;object-fit:cover;background:#f5f5f5;}
        .country-badge{position:absolute;top:10px;left:10px;background:#111;color:#fff;font-size:11px;padding:4px 10px;border-radius:20px;}
        .dojo-body{padding:18px;}
        .dojo-title{font-size:17px;font-weight:600;margin-bottom:6px;}
        .dojo-location{font-size:12px;color:#777;display:flex;align-items:center;gap:5px;border-bottom:1px solid #eee;padding-bottom:8px;margin-bottom:8px;}
        .dojo-label{font-size:11px;color:#aaa;margin-top:10px;letter-spacing:1px;}
        .dojo-instructor{font-size:14px;font-weight:500;margin-top:2px;}
        .dojo-view-btn {
            display: inline-block;
            background: #e60023;
            color: #fff;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            border: none;
            cursor: pointer;
            text-align: center;
            width: 100%;
            margin-top: 16px;
        }
        .dojo-view-btn:hover {
            background: #b0001a;
            transform: translateY(-2px);
        }
        .dojo-pagination{text-align:center;margin-top:40px;display:flex;justify-content:center;gap:8px;flex-wrap:wrap;}
        .dojo-pagination button{margin:0 2px;padding:8px 14px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer;transition:0.2s;}
        .dojo-pagination button.active-page{background:#e60023;color:#fff;border:none;}
        .dojo-pagination button:disabled{opacity:0.5;cursor:not-allowed;}
        .dojo-banner{position:relative;height:250px;border-radius:16px;overflow:hidden;margin-bottom:30px;background:url('https://ikak.net/wp-content/uploads/2026/04/Section.jpg') center/cover no-repeat;}
        .dojo-banner-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.55);}
        .dojo-banner-content{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;align-items:center;color:#fff;text-align:center;}
        .dojo-banner-content h1{font-size:38px;font-weight:700;letter-spacing:1px;}
        .dojo-banner-content h1 span{color:#e60023;}
        .dojo-stats-wrap{margin:30px 0;}
        .dojo-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:20px;}
        .stat-card{background:#fff;padding:20px;border-radius:10px;border:1px solid #eee;transition:.3s;cursor:pointer;text-align:center;font-family:inherit;width:100%;}
        .stat-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,0.1);border-color:#e60023;}
        .stat-card h2{font-size:28px;font-weight:700;margin-bottom:5px;}
        .stat-card .label{font-size:12px;font-weight:600;letter-spacing:1px;}
        .stat-card.active{border:2px solid #e60023;}
        .dojo-search-advanced{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin:30px 0;}
        .dojo-search-advanced input,.dojo-search-advanced select{padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px;background:#fff;min-width:160px;}
        .dojo-search-advanced input{flex:1;min-width:200px;}
        .dojo-search-advanced button{background:#6c757d;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;}
        .dojo-search-advanced button#clearFiltersBtn{background:#e60023;}
        .dojo-search-advanced button#clearFiltersBtn:hover{background:#c4001d;}
        .hidden-tab{display:none;}
        .dojo-no-results{text-align:center;padding:60px 20px;background:#fff;border-radius:16px;}

        /* ========== COUNTRIES TAB - GRID STYLE ========== */
        .continent-section {
            margin-bottom: 50px;
            background: #fff;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }
        .continent-title {
            font-size: 26px;
            font-weight: 700;
            color: #111;
            border-left: 5px solid #e60023;
            padding-left: 16px;
            margin-bottom: 24px;
        }
        .countries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .country-card {
            background: #f9f9f9;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            transition: 0.2s;
            border: 1px solid #eee;
        }
        .country-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        .country-flag {
            font-size: 48px;
            margin-bottom: 8px;
        }
        .country-name {
            font-weight: 600;
            font-size: 16px;
            margin: 10px 0;
            color: #222;
        }
        .dojo-action-btn {
            background: #e60023;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .dojo-action-btn.vacant {
            background: #aaa;
            cursor: pointer;
            opacity: 0.7;
        }
        .dojo-action-btn.vacant:hover {
            background: #888;
        }
        .dojo-action-btn:hover:not(.vacant) {
            background: #b0001a;
        }
        .continent-pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .continent-pagination button {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
        }
        .continent-pagination button.active-page {
            background: #e60023;
            color: white;
            border: none;
        }
        .continent-pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 640px) {
            .countries-grid { grid-template-columns: 1fr 1fr; }
            .dojo-stats { grid-template-columns: 1fr; }
        }
        @media (min-width: 1600px) { .dojo-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); } }
        @media (min-width: 2000px) { .dojo-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); } }
        @media (min-width: 2400px) { .dojo-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); } }
    </style>

    <div class="dojo-banner">
        <div class="dojo-banner-overlay"></div>
        <div class="dojo-banner-content">
            <h1>DOJO <span>BRANCHES</span></h1>
            <p>Explore our global Kyokushin network across continents, countries, and dojos.</p>
        </div>
    </div>

    <div class="dojo-stats-wrap">
        <div class="dojo-stats">
            <button data-tab="dojos" class="stat-card active" id="tabDojosBtn">
                <h2><?php echo $total_dojos; ?></h2>
                <p class="label">DOJOS</p>
                <span>Active training halls worldwide</span>
            </button>
            <button data-tab="countries" class="stat-card" id="tabCountriesBtn">
                <h2><?php echo count($countries); ?></h2>
                <p class="label">COUNTRIES</p>
                <span>Nations under IKAK</span>
            </button>
            <button data-tab="continents" class="stat-card" id="tabContinentsBtn">
                <h2><?php echo count($continents); ?></h2>
                <p class="label">CONTINENTS</p>
                <span>Global presence</span>
            </button>
        </div>
    </div>

    <!-- DOJOS TAB -->
    <div id="dojosTab" class="dojo-tab-container">
        <div class="dojo-search-advanced">
            <input type="text" id="dojoSearch" placeholder="Search by name, instructor, country, continent...">
            <select id="dojoContinent">
                <option value="">All continents</option>
                <?php foreach ($continents as $c): ?>
                    <option value="<?php echo esc_attr($c); ?>"><?php echo esc_html($c); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="dojoCountry">
                <option value="">All countries</option>
            </select>
            <button id="clearFiltersBtn">Clear filters</button>
        </div>
        <div id="dojoResults"></div>
        <div id="dojoPagination" class="dojo-pagination"></div>
    </div>

    <!-- COUNTRIES TAB -->
    <div id="countriesTab" class="dojo-tab-container hidden-tab"></div>

    <!-- CONTINENTS TAB -->
    <div id="continentsTab" class="dojo-tab-container hidden-tab"></div>

    <script>
        // Data from PHP
        const dojos = <?php echo $dojos_json; ?>;
        const allCountries = <?php echo $countries_json; ?>;
        const countryContinent = <?php echo $country_continent_json; ?>;
        const allContinents = <?php echo $continents_json; ?>;

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        function getFlagEmoji(countryName) {
            const map = {
                "china": "🇨🇳", "india": "🇮🇳", "indonesia": "🇮🇩", "pakistan": "🇵🇰", "bangladesh": "🇧🇩",
                "japan": "🇯🇵", "philippines": "🇵🇭", "vietnam": "🇻🇳", "turkey": "🇹🇷", "iran": "🇮🇷",
                "thailand": "🇹🇭", "myanmar": "🇲🇲", "south korea": "🇰🇷", "iraq": "🇮🇶", "afghanistan": "🇦🇫",
                "nepal": "🇳🇵", "uzbekistan": "🇺🇿", "malaysia": "🇲🇾", "yemen": "🇾🇪", "north korea": "🇰🇵",
                "sri lanka": "🇱🇰", "kazakhstan": "🇰🇿", "syria": "🇸🇾", "cambodia": "🇰🇭", "jordan": "🇯🇴",
                "azerbaijan": "🇦🇿", "tajikistan": "🇹🇯", "kyrgyzstan": "🇰🇬", "turkmenistan": "🇹🇲", "singapore": "🇸🇬",
                "georgia": "🇬🇪", "armenia": "🇦🇲", "mongolia": "🇲🇳", "oman": "🇴🇲", "qatar": "🇶🇦",
                "kuwait": "🇰🇼", "lebanon": "🇱🇧", "timor-leste": "🇹🇱", "uae": "🇦🇪", "cyprus": "🇨🇾",
                "bhutan": "🇧🇹", "maldives": "🇲🇻", "brunei": "🇧🇳", "laos": "🇱🇦", "bahrain": "🇧🇭",
                "macao": "🇲🇴", "palestine": "🇵🇸", "taiwan": "🇹🇼",
                "nigeria": "🇳🇬", "ethiopia": "🇪🇹", "egypt": "🇪🇬", "dr congo": "🇨🇩", "south africa": "🇿🇦",
                "tanzania": "🇹🇿", "kenya": "🇰🇪", "uganda": "🇺🇬", "algeria": "🇩🇿", "sudan": "🇸🇩",
                "morocco": "🇲🇦", "angola": "🇦🇴", "mozambique": "🇲🇿", "ghana": "🇬🇭", "madagascar": "🇲🇬",
                "cameroon": "🇨🇲", "côte d'ivoire": "🇨🇮", "niger": "🇳🇪", "burkina faso": "🇧🇫", "mali": "🇲🇱",
                "malawi": "🇲🇼", "zambia": "🇿🇲", "senegal": "🇸🇳", "chad": "🇹🇩", "somalia": "🇸🇴",
                "zimbabwe": "🇿🇼", "south sudan": "🇸🇸", "rwanda": "🇷🇼", "tunisia": "🇹🇳", "benin": "🇧🇯",
                "burundi": "🇧🇮", "libya": "🇱🇾", "togo": "🇹🇬", "eritrea": "🇪🇷", "sierra leone": "🇸🇱",
                "central african republic": "🇨🇫", "congo": "🇨🇬", "liberia": "🇱🇷", "mauritania": "🇲🇷", "botswana": "🇧🇼",
                "namibia": "🇳🇦", "lesotho": "🇱🇸", "gabon": "🇬🇦", "guinea": "🇬🇳", "gambia": "🇬🇲",
                "mauritius": "🇲🇺", "swaziland": "🇸🇿", "djibouti": "🇩🇯", "comoros": "🇰🇲", "equatorial guinea": "🇬🇶",
                "cabo verde": "🇨🇻", "são tomé and príncipe": "🇸🇹", "seychelles": "🇸🇨",
                "germany": "🇩🇪", "france": "🇫🇷", "italy": "🇮🇹", "spain": "🇪🇸", "poland": "🇵🇱",
                "romania": "🇷🇴", "netherlands": "🇳🇱", "belgium": "🇧🇪", "czech republic": "🇨🇿", "sweden": "🇸🇪",
                "portugal": "🇵🇹", "hungary": "🇭🇺", "belarus": "🇧🇾", "austria": "🇦🇹", "switzerland": "🇨🇭",
                "bulgaria": "🇧🇬", "denmark": "🇩🇰", "finland": "🇫🇮", "slovakia": "🇸🇰", "norway": "🇳🇴",
                "ireland": "🇮🇪", "croatia": "🇭🇷", "moldova": "🇲🇩", "bosnia and herzegovina": "🇧🇦", "albania": "🇦🇱",
                "lithuania": "🇱🇹", "north macedonia": "🇲🇰", "slovenia": "🇸🇮", "latvia": "🇱🇻", "estonia": "🇪🇪",
                "montenegro": "🇲🇪", "luxembourg": "🇱🇺", "malta": "🇲🇹", "iceland": "🇮🇸", "andorra": "🇦🇩",
                "monaco": "🇲🇨", "liechtenstein": "🇱🇮", "san marino": "🇸🇲", "holy see": "🇻🇦", "serbia": "🇷🇸",
                "kosovo": "🇽🇰", "ukraine": "🇺🇦", "russia": "🇷🇺", "united kingdom": "🇬🇧",
                "usa": "🇺🇸", "mexico": "🇲🇽", "canada": "🇨🇦", "guatemala": "🇬🇹", "haiti": "🇭🇹",
                "dominican republic": "🇩🇴", "cuba": "🇨🇺", "honduras": "🇭🇳", "nicaragua": "🇳🇮", "el salvador": "🇸🇻",
                "costa rica": "🇨🇷", "panama": "🇵🇦", "jamaica": "🇯🇲", "trinidad and tobago": "🇹🇹", "belize": "🇧🇿",
                "bahamas": "🇧🇸", "barbados": "🇧🇧", "saint lucia": "🇱🇨", "grenada": "🇬🇩", "antigua and barbuda": "🇦🇬",
                "dominica": "🇩🇲", "saint kitts and nevis": "🇰🇳", "saint vincent and the grenadines": "🇻🇨",
                "brazil": "🇧🇷", "colombia": "🇨🇴", "argentina": "🇦🇷", "peru": "🇵🇪", "venezuela": "🇻🇪",
                "chile": "🇨🇱", "ecuador": "🇪🇨", "bolivia": "🇧🇴", "paraguay": "🇵🇾", "uruguay": "🇺🇾",
                "guyana": "🇬🇾", "suriname": "🇸🇷", "australia": "🇦🇺",
                "united states": "🇺🇸", "korea": "🇰🇷", "russian federation": "🇷🇺", "uk": "🇬🇧",
                "ivory coast": "🇨🇮", "congo brazzaville": "🇨🇬", "drc": "🇨🇩"
            };
            let key = countryName.toLowerCase().trim();
            if (key === "côte d'ivoire") key = "côte d'ivoire";
            if (key === "united states") key = "usa";
            if (key === "united kingdom") key = "united kingdom";
            return map[key] || "🏳️";
        }

        // ---------- DOJOS TAB ----------
        let currentDojoPage = 1;
        const dojoItemsPerPage = 16;

        // ===============================================================
        // 🔥 UPDATED: Search now includes country and continent as well
        // ===============================================================
        function getFilteredDojos() {
            const search = document.getElementById('dojoSearch').value.toLowerCase().trim();
            const continent = document.getElementById('dojoContinent').value;
            const country = document.getElementById('dojoCountry').value;
            return dojos.filter(dojo => {
                // Compute continent for this dojo
                const dojoContinent = countryContinent[dojo.dojo_country] || 'Other';
                // Match search on name, instructor, country, and continent
                const matchSearch = search === '' ||
                    dojo.dojo_name.toLowerCase().includes(search) ||
                    (dojo.dojo_instructor && dojo.dojo_instructor.toLowerCase().includes(search)) ||
                    (dojo.dojo_country && dojo.dojo_country.toLowerCase().includes(search)) ||
                    dojoContinent.toLowerCase().includes(search);
                const matchContinent = continent === '' || dojoContinent === continent;
                const matchCountry = country === '' || (dojo.dojo_country || '') === country;
                return matchSearch && matchContinent && matchCountry;
            });
        }
        // ===============================================================

        function renderDojos() {
            const filtered = getFilteredDojos();
            const totalPages = Math.ceil(filtered.length / dojoItemsPerPage);
            if (currentDojoPage > totalPages) currentDojoPage = totalPages || 1;
            const start = (currentDojoPage - 1) * dojoItemsPerPage;
            const pageDojos = filtered.slice(start, start + dojoItemsPerPage);
            const container = document.getElementById('dojoResults');
            if (pageDojos.length === 0) {
                container.innerHTML = '<div class="dojo-no-results"><h3>No dojos found</h3></div>';
                document.getElementById('dojoPagination').innerHTML = '';
                return;
            }
            let html = '<div class="dojo-grid">';
            for (let dojo of pageDojos) {
                const fallbackImg = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(dojo.dojo_name || 'Dojo') + '&background=e60023&color=fff&size=200';
                const img = dojo.dojo_image ? dojo.dojo_image : fallbackImg;
                const location = dojo.dojo_address ? dojo.dojo_address.split('\n')[0] : '';
                const detailUrl = `https://ikak.net/dojos/?dojo_id=${dojo.id}`;
                html += `
                    <div class="dojo-card">
                        <div class="dojo-img-wrapper">
                            <a href="${detailUrl}" target="_blank" style="display: block;">
                                <img src="${escapeHtml(img)}" alt="${escapeHtml(dojo.dojo_name)}" onerror="this.src='${fallbackImg}'">
                                <span class="country-badge">${escapeHtml(dojo.dojo_country || 'Unknown')}</span>
                            </a>
                        </div>
                        <div class="dojo-body">
                            <a href="${detailUrl}" target="_blank" style="text-decoration: none; color: inherit;">
                                <div class="dojo-title">${escapeHtml(dojo.dojo_name)}</div>
                                <div class="dojo-location">${escapeHtml(location)}</div>
                            </a>
                            <div class="dojo-label">INSTRUCTOR</div>
                            <div class="dojo-instructor">${escapeHtml(dojo.dojo_instructor || '—')}</div>
                            <a href="${detailUrl}" target="_blank" class="dojo-view-btn">View Dojo</a>
                        </div>
                    </div>
                `;
            }
            html += '</div>';
            container.innerHTML = html;

            // Pagination
            let pagHtml = '';
            if (currentDojoPage > 1) {
                pagHtml += `<button id="dojoPrevPage" data-page="${currentDojoPage-1}">‹ Prev</button>`;
            } else {
                pagHtml += `<button id="dojoPrevPage" disabled>‹ Prev</button>`;
            }
            const alwaysShowCount = 6;
            if (totalPages <= alwaysShowCount) {
                for (let i = 1; i <= totalPages; i++) {
                    pagHtml += `<button class="${i === currentDojoPage ? 'active-page' : ''}" data-page="${i}">${i}</button>`;
                }
            } else {
                for (let i = 1; i <= alwaysShowCount; i++) {
                    pagHtml += `<button class="${i === currentDojoPage ? 'active-page' : ''}" data-page="${i}">${i}</button>`;
                }
                pagHtml += `<span style="padding:0 6px;">...</span>`;
                pagHtml += `<button class="${totalPages === currentDojoPage ? 'active-page' : ''}" data-page="${totalPages}">${totalPages}</button>`;
            }
            if (currentDojoPage < totalPages) {
                pagHtml += `<button id="dojoNextPage" data-page="${currentDojoPage+1}">Next ›</button>`;
            } else {
                pagHtml += `<button id="dojoNextPage" disabled>Next ›</button>`;
            }
            document.getElementById('dojoPagination').innerHTML = pagHtml;

            document.getElementById('dojoPrevPage')?.addEventListener('click', (e) => {
                const page = parseInt(e.currentTarget.getAttribute('data-page'));
                if (!isNaN(page)) { currentDojoPage = page; renderDojos(); }
            });
            document.getElementById('dojoNextPage')?.addEventListener('click', (e) => {
                const page = parseInt(e.currentTarget.getAttribute('data-page'));
                if (!isNaN(page)) { currentDojoPage = page; renderDojos(); }
            });
            document.querySelectorAll('#dojoPagination button[data-page]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    currentDojoPage = parseInt(e.target.dataset.page);
                    renderDojos();
                });
            });
        }

        function populateCountrySelect() {
            const continent = document.getElementById('dojoContinent').value;
            const countrySelect = document.getElementById('dojoCountry');
            let options = '<option value="">All countries</option>';
            for (let country of allCountries) {
                if (continent === "" || (countryContinent[country] || 'Other') === continent) {
                    options += `<option value="${escapeHtml(country)}">${escapeHtml(country)}</option>`;
                }
            }
            countrySelect.innerHTML = options;
        }

        function refreshDojos() { currentDojoPage = 1; renderDojos(); }

        // ---------- COUNTRIES TAB ----------
        const continentCountriesMap = {
            "Asia": ["China", "India", "Indonesia", "Pakistan", "Bangladesh", "Japan", "Philippines", "Vietnam", "Turkey", "Iran", "Thailand", "Myanmar", "South Korea", "Iraq", "Afghanistan", "Nepal", "Uzbekistan", "Malaysia", "Yemen", "North Korea", "Sri Lanka", "Kazakhstan", "Syria", "Cambodia", "Jordan", "Azerbaijan", "Tajikistan", "Kyrgyzstan", "Turkmenistan", "Singapore", "Georgia", "Armenia", "Mongolia", "Oman", "Qatar", "Kuwait", "Lebanon", "Timor-Leste", "UAE", "Cyprus", "Bhutan", "Maldives", "Brunei", "Laos", "Bahrain", "Macao", "Palestine", "Taiwan"],
            "Africa": ["Nigeria", "Ethiopia", "Egypt", "DR Congo", "South Africa", "Tanzania", "Kenya", "Uganda", "Algeria", "Sudan", "Morocco", "Angola", "Mozambique", "Ghana", "Madagascar", "Cameroon", "Côte d'Ivoire", "Niger", "Burkina Faso", "Mali", "Malawi", "Zambia", "Senegal", "Chad", "Somalia", "Zimbabwe", "South Sudan", "Rwanda", "Tunisia", "Benin", "Burundi", "Libya", "Togo", "Eritrea", "Sierra Leone", "Central African Republic", "Congo", "Liberia", "Mauritania", "Botswana", "Namibia", "Lesotho", "Gabon", "Guinea", "Gambia", "Mauritius", "Swaziland", "Djibouti", "Comoros", "Equatorial Guinea", "Cabo Verde", "São Tomé and Príncipe", "Seychelles"],
            "Europe": ["Germany", "France", "Italy", "Spain", "Poland", "Romania", "Netherlands", "Belgium", "Czech Republic", "Sweden", "Portugal", "Hungary", "Belarus", "Austria", "Switzerland", "Bulgaria", "Denmark", "Finland", "Slovakia", "Norway", "Ireland", "Croatia", "Moldova", "Bosnia and Herzegovina", "Albania", "Lithuania", "North Macedonia", "Slovenia", "Latvia", "Estonia", "Montenegro", "Luxembourg", "Malta", "Iceland", "Andorra", "Monaco", "Liechtenstein", "San Marino", "Holy See", "Serbia", "Kosovo", "Ukraine", "Russia", "United Kingdom"],
            "North America": ["USA", "Mexico", "Canada", "Guatemala", "Haiti", "Dominican Republic", "Cuba", "Honduras", "Nicaragua", "El Salvador", "Costa Rica", "Panama", "Jamaica", "Trinidad and Tobago", "Belize", "Bahamas", "Barbados", "Saint Lucia", "Grenada", "Antigua and Barbuda", "Dominica", "Saint Kitts and Nevis", "Saint Vincent and the Grenadines"],
            "South America": ["Brazil", "Colombia", "Argentina", "Peru", "Venezuela", "Chile", "Ecuador", "Bolivia", "Paraguay", "Uruguay", "Guyana", "Suriname"],
            "Oceania": ["Australia"]
        };
        let continentPages = {};

        function getCountriesByContinent() {
            const groups = {};
            for (const [continent, countries] of Object.entries(continentCountriesMap)) {
                groups[continent] = [];
                for (const country of countries) {
                    const dojoCount = dojos.filter(d => (d.dojo_country || '').toLowerCase() === country.toLowerCase()).length;
                    groups[continent].push({ name: country, dojoCount: dojoCount });
                }
                groups[continent].sort((a,b) => a.name.localeCompare(b.name));
            }
            const order = ["Asia", "Africa", "Europe", "North America", "South America", "Oceania"];
            const sortedContinents = Object.keys(groups).sort((a,b) => {
                let ia = order.indexOf(a);
                let ib = order.indexOf(b);
                if (ia === -1) ia = 999;
                if (ib === -1) ib = 999;
                return ia - ib;
            });
            return { groups, sortedContinents };
        }

        function renderCountriesTab() {
            const { groups, sortedContinents } = getCountriesByContinent();
            const container = document.getElementById('countriesTab');
            if (!container) return;
            let html = '';
            const itemsPerContinentPage = 8;

            for (let continent of sortedContinents) {
                const countries = groups[continent];
                const totalItems = countries.length;
                const totalPages = Math.ceil(totalItems / itemsPerContinentPage);
                if (!continentPages[continent]) continentPages[continent] = 1;
                let page = continentPages[continent];
                if (page > totalPages) page = totalPages || 1;
                continentPages[continent] = page;
                const start = (page - 1) * itemsPerContinentPage;
                const pageCountries = countries.slice(start, start + itemsPerContinentPage);

                html += `<div class="continent-section" data-continent="${escapeHtml(continent)}">
                            <div class="continent-title"> ${escapeHtml(continent)} Continent</div>
                            <div class="countries-grid" id="grid-${escapeHtml(continent).replace(/\s/g, '')}">`;
                for (let c of pageCountries) {
                    const flag = getFlagEmoji(c.name);
                    const hasDojos = c.dojoCount > 0;
                    html += `<div class="country-card">
                                <div class="country-flag">${flag}</div>
                                <div class="country-name">${escapeHtml(c.name)}</div>`;
                    if (hasDojos) {
                        html += `<button class="dojo-action-btn" data-country="${escapeHtml(c.name)}">View Dojos</button>`;
                    } else {
                        html += `<a href="https://members.ikak.net/application/form" target="_blank" class="dojo-action-btn vacant">Vacant</a>`;
                    }
                    html += `</div>`;
                }
                html += `</div><div class="continent-pagination" id="pagination-${escapeHtml(continent).replace(/\s/g, '')}"></div></div>`;
            }
            container.innerHTML = html;

            for (let continent of sortedContinents) {
                const countries = groups[continent];
                const totalPages = Math.ceil(countries.length / itemsPerContinentPage);
                const currentPage = continentPages[continent];
                const pagContainer = document.getElementById(`pagination-${continent.replace(/\s/g, '')}`);
                if (totalPages <= 1) {
                    if (pagContainer) pagContainer.innerHTML = '';
                    continue;
                }
                let pagHtml = '';
                pagHtml += `<button class="cont-prev" data-continent="${escapeHtml(continent)}" ${currentPage === 1 ? 'disabled' : ''}>‹ Prev</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        pagHtml += `<button class="cont-page ${i === currentPage ? 'active-page' : ''}" data-continent="${escapeHtml(continent)}" data-page="${i}">${i}</button>`;
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        pagHtml += `<button disabled style="border:none; background:transparent;">...</button>`;
                    }
                }
                pagHtml += `<button class="cont-next" data-continent="${escapeHtml(continent)}" ${currentPage === totalPages ? 'disabled' : ''}>Next ›</button>`;
                if (pagContainer) pagContainer.innerHTML = pagHtml;
            }

            document.querySelectorAll('.cont-prev').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const continent = e.target.getAttribute('data-continent');
                    if (continentPages[continent] > 1) {
                        continentPages[continent]--;
                        renderCountriesTab();
                    }
                });
            });
            document.querySelectorAll('.cont-next').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const continent = e.target.getAttribute('data-continent');
                    const { groups } = getCountriesByContinent();
                    const totalPages = Math.ceil(groups[continent].length / itemsPerContinentPage);
                    if (continentPages[continent] < totalPages) {
                        continentPages[continent]++;
                        renderCountriesTab();
                    }
                });
            });
            document.querySelectorAll('.cont-page').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const continent = e.target.getAttribute('data-continent');
                    const page = parseInt(e.target.getAttribute('data-page'));
                    continentPages[continent] = page;
                    renderCountriesTab();
                });
            });

            document.querySelectorAll('#countriesTab .dojo-action-btn[data-country]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const country = e.target.getAttribute('data-country');
                    showTab('dojos');
                    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
                    document.getElementById('tabDojosBtn').classList.add('active');
                    const countrySelect = document.getElementById('dojoCountry');
                    if (countrySelect) {
                        countrySelect.value = country;
                        const continent = countryContinent[country] || '';
                        const continentSelect = document.getElementById('dojoContinent');
                        if (continentSelect && continent) continentSelect.value = continent;
                        refreshDojos();
                    }
                });
            });
        }

        // ---------- CONTINENTS TAB ----------
        function renderContinentsTab() {
            const container = document.getElementById('continentsTab');
            if (!container) return;
            let html = '<div class="dojo-grid">';
            for (let continent of allContinents) {
                const dojoCount = dojos.filter(d => (countryContinent[d.dojo_country] || 'Other') === continent).length;
                html += `
                    <div class="dojo-card continent-card" data-continent="${escapeHtml(continent)}" style="cursor:pointer;">
                        <div class="dojo-body">
                            <div class="dojo-title">${escapeHtml(continent)}</div>
                            <div class="dojo-instructor">${dojoCount} dojo(s)</div>
                        </div>
                    </div>
                `;
            }
            html += '</div>';
            container.innerHTML = html;

            document.querySelectorAll('#continentsTab .continent-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    const continent = card.getAttribute('data-continent');
                    showTab('dojos');
                    updateActiveStat('dojos');
                    const continentSelect = document.getElementById('dojoContinent');
                    if (continentSelect && continent) {
                        continentSelect.value = continent;
                        const countrySelect = document.getElementById('dojoCountry');
                        if (countrySelect) countrySelect.value = '';
                        refreshDojos();
                    }
                });
            });
        }

        // ---------- TAB SWITCHING ----------
        const dojosTab = document.getElementById('dojosTab');
        const countriesTabElem = document.getElementById('countriesTab');
        const continentsTabElem = document.getElementById('continentsTab');
        const tabDojos = document.getElementById('tabDojosBtn');
        const tabCountries = document.getElementById('tabCountriesBtn');
        const tabContinents = document.getElementById('tabContinentsBtn');

        function showTab(tabId) {
            dojosTab.classList.add('hidden-tab');
            countriesTabElem.classList.add('hidden-tab');
            continentsTabElem.classList.add('hidden-tab');
            if (tabId === 'dojos') dojosTab.classList.remove('hidden-tab');
            if (tabId === 'countries') { countriesTabElem.classList.remove('hidden-tab'); renderCountriesTab(); }
            if (tabId === 'continents') continentsTabElem.classList.remove('hidden-tab');
        }

        function updateActiveStat(active) {
            [tabDojos, tabCountries, tabContinents].forEach(btn => btn.classList.remove('active'));
            if (active === 'dojos') tabDojos.classList.add('active');
            if (active === 'countries') tabCountries.classList.add('active');
            if (active === 'continents') tabContinents.classList.add('active');
        }

        tabDojos.addEventListener('click', () => { showTab('dojos'); updateActiveStat('dojos'); });
        tabCountries.addEventListener('click', () => { showTab('countries'); updateActiveStat('countries'); });
        tabContinents.addEventListener('click', () => { showTab('continents'); updateActiveStat('continents'); if (!continentsTabElem.innerHTML) renderContinentsTab(); });

        // ---------- INIT ----------
        document.addEventListener('DOMContentLoaded', () => {
            populateCountrySelect();
            renderDojos();
            renderContinentsTab();
            renderCountriesTab();

            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab === 'countries') {
                showTab('countries');
                updateActiveStat('countries');
                renderCountriesTab();
            } else if (tab === 'dojos') {
                showTab('dojos');
                updateActiveStat('dojos');
            }

            const chiefName = urlParams.get('chief_name');
            if (chiefName) {
                const searchInput = document.getElementById('dojoSearch');
                if (searchInput) {
                    searchInput.value = chiefName;
                    refreshDojos();
                }
            }

            document.getElementById('dojoSearch').addEventListener('input', refreshDojos);
            document.getElementById('dojoContinent').addEventListener('change', () => { populateCountrySelect(); refreshDojos(); });
            document.getElementById('dojoCountry').addEventListener('change', refreshDojos);
            document.getElementById('clearFiltersBtn').addEventListener('click', () => {
                document.getElementById('dojoSearch').value = '';
                document.getElementById('dojoContinent').value = '';
                populateCountrySelect();
                refreshDojos();
            });
        });
    </script>
    <?php
    return ob_get_clean();
}