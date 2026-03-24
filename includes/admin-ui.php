<?php
/**
 * ADMINISTRACIÓN Y AJUSTES - REAL REVIEWS SUITE
 */

if (!defined('ABSPATH')) exit;

/* ==============================================================
   PÁGINAS DEL PLUGIN
============================================================== */
define('RR_PAGES', ['rrsuite-main', 'real-reviews-settings', 'real-reviews-shortcode']);

/* ==============================================================
   1) MENÚ PRINCIPAL + SUBMENÚS
============================================================== */
add_action('admin_menu', 'rrsuite_build_menu');
function rrsuite_build_menu() {
    add_menu_page(
        'Real Reviews',
        'Real Reviews',
        'manage_options',
        'rrsuite-main',
        'rrsuite_dashboard_page',
        'dashicons-star-filled',
        3
    );
    add_submenu_page('rrsuite-main', 'Dashboard',   'Dashboard',   'manage_options', 'rrsuite-main',           'rrsuite_dashboard_page');
    add_submenu_page('rrsuite-main', 'Settings',    'Settings',    'manage_options', 'real-reviews-settings',  'real_reviews_settings_page');
    add_submenu_page('rrsuite-main', 'Shortcodes',  'Shortcodes',  'manage_options', 'real-reviews-shortcode', 'rrsuite_shortcode_page');
}

/* ==============================================================
   BODY CLASS — full-width mode
============================================================== */
add_filter('admin_body_class', function($classes) {
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (in_array($page, RR_PAGES, true)) {
        $classes .= ' rrsuite-active';
    }
    return $classes;
});

/* ==============================================================
   ADMIN STYLES
============================================================== */
add_action('admin_head', 'rrsuite_admin_inline_css');
function rrsuite_admin_inline_css() {
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (!in_array($page, RR_PAGES, true)) return;
    ?>
<style>
/* ==========================================================
   REAL REVIEWS SUITE — ADMIN UI
========================================================== */

/* Full-width override */
body.rrsuite-active #wpcontent       { padding-left: 0 !important; }
body.rrsuite-active #wpbody-content  { padding-bottom: 0 !important; }
body.rrsuite-active .wrap            { margin: 0 !important; padding: 0 !important; }
body.rrsuite-active #screen-meta,
body.rrsuite-active .notice,
body.rrsuite-active .updated,
body.rrsuite-active .error           { display: none !important; }

/* CSS vars */
:root {
    --rg: #8BC53F;
    --rb: #00AEEF;
    --rd: #131720;
    --rds: #1d2333;
    --rtext: #1e2a3a;
    --rmuted: #64748b;
    --rborder: #e2e8f0;
    --rcard: #ffffff;
    --rbg: #f1f4f8;
    --rradius: 12px;
    --rshadow: 0 1px 8px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
    --rfocus: 0 0 0 3px rgba(0,174,239,.18);
}

/* ==========================================================
   PANEL SHELL
========================================================== */
.rr-panel {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 32px);
    background: var(--rbg);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
    color: var(--rtext);
}

/* ── Top header ── */
.rr-header {
    background: linear-gradient(120deg, #131720 0%, #1d2845 60%, #162038 100%);
    padding: 0 32px;
    height: 62px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.rr-header-logo {
    display: flex;
    align-items: center;
    gap: 11px;
    text-decoration: none !important;
}
.rr-header-star {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg,rgba(139,197,63,.25),rgba(0,174,239,.2));
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.rr-brand {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.2px;
    line-height: 1;
}
.rr-brand .g { color: var(--rg); }
.rr-brand .b { color: var(--rb); }
.rr-brand .s { color: rgba(255,255,255,.38); font-weight: 400; font-size: 15px; }
.rr-header-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 10px;
}
.rr-live-dot {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(139,197,63,.15);
    border: 1px solid rgba(139,197,63,.3);
    color: var(--rg);
    font-size: 11px;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 20px;
    letter-spacing: .4px;
}
.rr-live-dot::before {
    content: '';
    width: 6px;
    height: 6px;
    background: var(--rg);
    border-radius: 50%;
    animation: rr-pulse 2s infinite;
}
@keyframes rr-pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(.7); }
}
.rr-version {
    color: rgba(255,255,255,.3);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .3px;
}

/* ── Tab navigation ── */
.rr-tabs {
    background: #fff;
    border-bottom: 1px solid var(--rborder);
    padding: 0 32px;
    display: flex;
    align-items: flex-end;
    gap: 2px;
    flex-shrink: 0;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.rr-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 13px 18px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--rmuted) !important;
    text-decoration: none !important;
    border-bottom: 3px solid transparent;
    margin-bottom: -1px;
    transition: color .18s, border-color .18s;
    white-space: nowrap;
    border-radius: 0;
}
.rr-tab .rr-tab-ico {
    font-size: 15px;
    line-height: 1;
    opacity: .65;
    transition: opacity .18s;
}
.rr-tab:hover {
    color: var(--rtext) !important;
}
.rr-tab:hover .rr-tab-ico { opacity: 1; }
.rr-tab.is-active {
    color: var(--rb) !important;
    border-bottom-color: var(--rb);
}
.rr-tab.is-active .rr-tab-ico { opacity: 1; }

/* ── Content area ── */
.rr-content {
    flex: 1;
    padding: 28px 32px;
    max-width: 1100px;
    width: 100%;
    box-sizing: border-box;
}

/* ── WP notices inside panel ── */
.rr-wp-notices { margin-bottom: 20px; }
.rr-wp-notices .notice,
.rr-wp-notices .updated,
.rr-wp-notices .error {
    display: block !important;
    position: static !important;
    margin: 0 0 10px !important;
    border-radius: 8px !important;
}

/* ── Inline notice ── */
.rr-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 22px;
}
.rr-notice.info    { background:#eff8ff; border:1px solid #bfdbfe; color:#1e40af; }
.rr-notice.success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }

/* ==========================================================
   SETTINGS — CARDS
========================================================== */
.rr-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 22px;
}
.rr-card {
    background: var(--rcard);
    border-radius: var(--rradius);
    border: 1px solid var(--rborder);
    box-shadow: var(--rshadow);
    overflow: hidden;
}
.rr-card-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 15px 20px 14px;
    border-bottom: 1px solid var(--rborder);
    background: #fafbfd;
}
.rr-cico {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.rr-cico.c-blue  { background: rgba(0,174,239,.1); }
.rr-cico.c-green { background: rgba(139,197,63,.1); }
.rr-cico.c-gray  { background: rgba(100,116,139,.08); }
.rr-cico.c-amber { background: rgba(245,158,11,.1); }
.rr-card-head-text h3 { margin:0; font-size:14px; font-weight:700; color:var(--rtext); }
.rr-card-head-text p  { margin:2px 0 0; font-size:11px; color:var(--rmuted); }
.rr-card-body { padding: 22px; }

/* ── Fields ── */
.rr-field { margin-bottom: 20px; }
.rr-field:last-child { margin-bottom: 0; }
.rr-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--rmuted);
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-bottom: 7px;
}
.rr-input {
    width: 100%;
    padding: 9px 13px;
    border: 1.5px solid var(--rborder);
    border-radius: 8px;
    font-size: 14px;
    color: var(--rtext);
    background: #fff;
    box-sizing: border-box;
    transition: border-color .18s, box-shadow .18s;
    font-family: inherit;
    line-height: 1.5;
}
.rr-input:focus {
    outline: none;
    border-color: var(--rb);
    box-shadow: var(--rfocus);
}
.rr-color-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rr-input-color {
    width: 46px;
    height: 42px;
    padding: 3px;
    border: 1.5px solid var(--rborder);
    border-radius: 8px;
    cursor: pointer;
    flex-shrink: 0;
    background: #fff;
}
.rr-color-hex {
    flex: 1;
    padding: 9px 13px;
    border: 1.5px solid var(--rborder);
    border-radius: 8px;
    font-family: 'SFMono-Regular', 'Fira Code', monospace;
    font-size: 13px;
    color: var(--rmuted);
    background: #f7f9fc;
    user-select: all;
    cursor: default;
}
.rr-hint {
    font-size: 11px;
    color: var(--rmuted);
    margin-top: 5px;
    line-height: 1.5;
}
.rr-hint code {
    background: #f1f4f8;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 11px;
    color: var(--rb);
}

/* ── Actions bar ── */
.rr-action-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 18px 22px;
    background: var(--rcard);
    border-radius: var(--rradius);
    border: 1px solid var(--rborder);
    box-shadow: var(--rshadow);
}
.rr-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: linear-gradient(135deg, var(--rb) 0%, #0094cc 100%);
    color: #fff !important;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 24px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    text-decoration: none !important;
    box-shadow: 0 3px 10px rgba(0,174,239,.28);
    transition: opacity .18s, transform .15s;
    letter-spacing: .15px;
    font-family: inherit;
}
.rr-btn-primary:hover { opacity: .87; transform: translateY(-1px); color: #fff !important; }
.rr-btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    color: #b91c1c !important;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 8px;
    border: 1.5px solid #fecaca;
    cursor: pointer;
    text-decoration: none !important;
    transition: all .18s;
    font-family: inherit;
}
.rr-btn-danger:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c !important; }

/* ==========================================================
   SHORTCODES
========================================================== */
.rr-sc-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.rr-sc-item {
    background: var(--rcard);
    border-radius: var(--rradius);
    border: 1px solid var(--rborder);
    box-shadow: var(--rshadow);
    overflow: hidden;
    transition: box-shadow .2s;
}
.rr-sc-item:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.1);
}
.rr-sc-head {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px 22px;
    border-bottom: 1px solid var(--rborder);
    background: #fafbfd;
}
.rr-sc-num {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
}
.rr-sc-num.n1 { background: linear-gradient(135deg,var(--rb),#0094cc); }
.rr-sc-num.n2 { background: linear-gradient(135deg,var(--rg),#6ea82d); }
.rr-sc-num.n3 { background: linear-gradient(135deg,#f59e0b,#d97706); }
.rr-sc-num.n4 { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
.rr-sc-num.n5 { background: linear-gradient(135deg,#ec4899,#be185d); }
.rr-sc-num.n6 { background: linear-gradient(135deg,#10b981,#059669); }
.rr-sc-head-text h3 { margin:0; font-size:14px; font-weight:700; color:var(--rtext); }
.rr-sc-head-text p  { margin:2px 0 0; font-size:11.5px; color:var(--rmuted); }
.rr-sc-body { padding: 18px 22px; }
.rr-sc-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rr-sc-code {
    flex: 1;
    background: #f7f9fc;
    border: 1.5px solid var(--rborder);
    border-radius: 8px;
    padding: 10px 16px;
    font-family: 'SFMono-Regular', 'Fira Code', 'Courier New', monospace;
    font-size: 14px;
    font-weight: 700;
    color: var(--rb);
    user-select: all;
    cursor: text;
    overflow-x: auto;
    white-space: nowrap;
    line-height: 1.4;
}
.rr-copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--rd);
    color: rgba(255,255,255,.9) !important;
    font-size: 12px;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    text-decoration: none !important;
    white-space: nowrap;
    transition: background .18s;
    font-family: inherit;
    letter-spacing: .15px;
}
.rr-copy-btn:hover  { background: #2a3450; }
.rr-copy-btn.copied { background: var(--rg) !important; color: #fff !important; }

/* ==========================================================
   DASHBOARD IFRAME
========================================================== */

/* Panel en modo iframe ocupa todo el alto restante */
body.rrsuite-active .rr-panel--iframe {
    height: calc(100vh - 32px);
}
body.rrsuite-active .rr-panel--iframe .rr-content {
    flex: 1;
    padding: 0;
    max-width: none;
    width: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.rr-iframe-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-top: 1px solid var(--rborder);
}
.rr-iframe-container iframe {
    display: block;
    width: 100%;
    flex: 1;
    border: none;
}

/* ==========================================================
   RESPONSIVE
========================================================== */
@media (max-width: 782px) {
    .rr-header  { padding: 0 16px; }
    .rr-tabs    { padding: 0 16px; }
    .rr-content { padding: 20px 16px; }
    .rr-cards   { grid-template-columns: 1fr; }
    .rr-tab span.rr-tab-lbl { display: none; }
}
</style>
<?php }

/* ==============================================================
   COMPONENTE: HEADER + TABS
============================================================== */
function rrsuite_panel_header($active_page) {
    $tabs = [
        'rrsuite-main'           => ['label' => 'Dashboard',  'ico' => '🖥'],
        'real-reviews-settings'  => ['label' => 'Settings',   'ico' => '⚙️'],
        'real-reviews-shortcode' => ['label' => 'Shortcodes', 'ico' => '📋'],
    ];
    ?>
    <div class="rr-header">
        <a class="rr-header-logo" href="<?php echo esc_url(admin_url('admin.php?page=rrsuite-main')); ?>">
            <div class="rr-header-star">⭐</div>
            <div>
                <div class="rr-brand">
                    <span class="g">Real</span><span class="b">Reviews</span><span class="s"> Suite</span>
                </div>
            </div>
        </a>
        <div class="rr-header-right">
            <span class="rr-live-dot">LIVE</span>
            <span class="rr-version">v4.0</span>
        </div>
    </div>

    <div class="rr-tabs">
        <?php foreach ($tabs as $slug => $t):
            $url      = admin_url('admin.php?page=' . $slug);
            $is_active = ($active_page === $slug);
        ?>
        <a href="<?php echo esc_url($url); ?>"
           class="rr-tab <?php echo $is_active ? 'is-active' : ''; ?>">
            <span class="rr-tab-ico"><?php echo $t['ico']; ?></span>
            <span class="rr-tab-lbl"><?php echo esc_html($t['label']); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php
}

/* ==============================================================
   PANEL EXTERNO (IFRAME) — Dashboard
============================================================== */
function rrsuite_dashboard_page() { ?>
<div class="wrap" style="overflow:hidden;height:calc(100vh - 32px);display:flex;flex-direction:column;">
<div class="rr-panel rr-panel--iframe" style="flex:1;overflow:hidden;">

    <?php rrsuite_panel_header('rrsuite-main'); ?>

    <div class="rr-content">
        <div class="rr-iframe-container">
            <iframe src="<?php echo esc_url('https://client-area.realreviewsbyrp.com/'); ?>"
                    loading="lazy"
                    allow="fullscreen"></iframe>
        </div>
    </div>

</div>
</div>
<?php }

/* ==============================================================
   SHORTCODE PAGE
============================================================== */
function rrsuite_shortcode_page() { ?>
<div class="wrap">
<div class="rr-panel">

    <?php rrsuite_panel_header('real-reviews-shortcode'); ?>

    <div class="rr-content">

        <div class="rr-notice info">
            ℹ️&nbsp; Copy any shortcode and paste it into any page, post, or block widget.
        </div>

        <div class="rr-sc-grid">

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n1">1</div>
                    <div class="rr-sc-head-text">
                        <h3>Reviews Grid — Masonry</h3>
                        <p>Full grid with platform tabs, star summary and all reviews.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                    <p class="rr-hint" style="margin-top:10px;">Optional: <code>source="Google"</code> <code>min_stars="4"</code> <code>limit="6"</code> <code>title="Our Reviews"</code></p>
                </div>
            </div>

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n2">2</div>
                    <div class="rr-sc-head-text">
                        <h3>Review Submission Form</h3>
                        <p>Lets customers submit a new review directly from your site.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews_form]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews_form]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n3">3</div>
                    <div class="rr-sc-head-text">
                        <h3>Reviews Carousel</h3>
                        <p>Horizontal scrollable carousel — ideal for homepages and hero sections.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews_carousel]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews_carousel]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                    <p class="rr-hint" style="margin-top:10px;">Optional: <code>source="Google"</code> <code>min_stars="4"</code> <code>limit="6"</code></p>
                </div>
            </div>

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n4">4</div>
                    <div class="rr-sc-head-text">
                        <h3>Trust Badge</h3>
                        <p>Floating badge with score and stars. Fixed bottom-right by default.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews_badge]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews_badge]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                    <p class="rr-hint" style="margin-top:10px;">Optional: <code>position="bottom-right"</code> <code>position="bottom-left"</code> <code>position="static"</code></p>
                </div>
            </div>

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n5">5</div>
                    <div class="rr-sc-head-text">
                        <h3>Featured Review</h3>
                        <p>Highlights your best review in a large quote format — perfect for landing pages.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews_featured]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews_featured]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                    <p class="rr-hint" style="margin-top:10px;">Optional: <code>source="Google"</code> <code>min_stars="5"</code> <code>random="true"</code></p>
                </div>
            </div>

            <div class="rr-sc-item">
                <div class="rr-sc-head">
                    <div class="rr-sc-num n6">6</div>
                    <div class="rr-sc-head-text">
                        <h3>Inline Score</h3>
                        <p>Compact score + stars to embed inside any text, heading or button.</p>
                    </div>
                </div>
                <div class="rr-sc-body">
                    <div class="rr-sc-row">
                        <div class="rr-sc-code">[real_reviews_score]</div>
                        <button class="rr-copy-btn" onclick="rrCopy(this,'[real_reviews_score]')">
                            <span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span>
                            Copy
                        </button>
                    </div>
                    <p class="rr-hint" style="margin-top:10px;">Optional: <code>source="Google"</code> <code>show_count="false"</code> <code>label="Based on"</code></p>
                </div>
            </div>

        </div>
    </div>

</div>
</div>

<script>
function rrCopy(btn, text) {
    navigator.clipboard.writeText(text).then(function() {
        btn.classList.add('copied');
        btn.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span> Copied!';
        setTimeout(function() {
            btn.classList.remove('copied');
            btn.innerHTML = '<span class="dashicons dashicons-clipboard" style="font-size:13px;width:13px;height:13px;line-height:1;vertical-align:middle;"></span> Copy';
        }, 2200);
    });
}
</script>
<?php }

/* ==============================================================
   AJUSTES EN WP-ADMIN
============================================================== */
add_action('admin_init', function() {
    register_setting('real_reviews_options', 'real_reviews_accent_color',   ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('real_reviews_options', 'real_reviews_section_title',  ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('real_reviews_options', 'real_reviews_company_id',     ['sanitize_callback' => 'sanitize_text_field']);

    if (
        isset($_GET['page']) && $_GET['page'] === 'real-reviews-settings'
        && isset($_GET['flush_cache'])
        && current_user_can('manage_options')
    ) {
        delete_transient('rr_cache_' . md5(rr_get_api_url()));
        set_transient('rrsuite_flash', ['type' => 'success', 'msg' => '✅ Review cache cleared — fresh data will load on the next visit.'], 30);
        wp_redirect(admin_url('admin.php?page=real-reviews-settings'));
        exit;
    }
});

function real_reviews_settings_page() {
    $accent  = get_option('real_reviews_accent_color',  '#f39c12');
    $title   = get_option('real_reviews_section_title', 'Customer Reviews');
    $comp_id = get_option('real_reviews_company_id',    'Com0000DEMO');

    // Flash notice (after cache flush redirect)
    $flash = get_transient('rrsuite_flash');
    if ($flash) delete_transient('rrsuite_flash');

    // Save success detection
    $saved = isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true';
    ?>
<div class="wrap">
<div class="rr-panel">

    <?php rrsuite_panel_header('real-reviews-settings'); ?>

    <div class="rr-content">

        <?php if ($saved): ?>
        <div class="rr-notice success">
            ✅&nbsp; Settings saved successfully.
        </div>
        <?php endif; ?>

        <?php if ($flash): ?>
        <div class="rr-notice <?php echo esc_attr($flash['type']); ?>">
            <?php echo esc_html($flash['msg']); ?>
        </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('real_reviews_options'); ?>

            <div class="rr-cards">

                <!-- Company -->
                <div class="rr-card">
                    <div class="rr-card-head">
                        <div class="rr-cico c-blue">🏢</div>
                        <div class="rr-card-head-text">
                            <h3>Company</h3>
                            <p>API connection settings</p>
                        </div>
                    </div>
                    <div class="rr-card-body">
                        <div class="rr-field">
                            <label class="rr-label">Company ID</label>
                            <input class="rr-input" type="text"
                                   name="real_reviews_company_id"
                                   value="<?php echo esc_attr($comp_id); ?>"
                                   placeholder="Com0000DEMO">
                            <p class="rr-hint">Your unique ID from the Real Reviews dashboard (e.g. <code>Com0000DEMO</code>).</p>
                        </div>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="rr-card">
                    <div class="rr-card-head">
                        <div class="rr-cico c-green">🎨</div>
                        <div class="rr-card-head-text">
                            <h3>Appearance</h3>
                            <p>Widget display customization</p>
                        </div>
                    </div>
                    <div class="rr-card-body">
                        <div class="rr-field">
                            <label class="rr-label">Section Title</label>
                            <input class="rr-input" type="text"
                                   name="real_reviews_section_title"
                                   value="<?php echo esc_attr($title); ?>"
                                   placeholder="Customer Reviews">
                        </div>
                        <div class="rr-field">
                            <label class="rr-label">Accent Color</label>
                            <div class="rr-color-wrap">
                                <input type="color"
                                       class="rr-input-color"
                                       name="real_reviews_accent_color"
                                       id="rr_accent"
                                       value="<?php echo esc_attr($accent); ?>"
                                       oninput="document.getElementById('rr_hex').textContent=this.value">
                                <div class="rr-color-hex" id="rr_hex"><?php echo esc_html($accent); ?></div>
                            </div>
                            <p class="rr-hint">Applied to stars and highlight elements in the reviews widget.</p>
                        </div>
                    </div>
                </div>

            </div><!-- /.rr-cards -->

            <!-- Action bar -->
            <div class="rr-action-bar">
                <button type="submit" class="rr-btn-primary">
                    <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:1;vertical-align:middle;"></span>
                    Save Settings
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=real-reviews-settings&flush_cache=1')); ?>"
                   class="rr-btn-danger"
                   onclick="return confirm('Clear the review cache?\n\nFresh data will be fetched on the next page load.');">
                    🗑 Clear Review Cache
                </a>
            </div>

        </form>
    </div>

</div>
</div>
<?php }
