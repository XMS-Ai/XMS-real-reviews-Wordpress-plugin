<?php
/**
 * SHORTCODES - REAL REVIEWS SUITE v4.6
 */

if (!defined('ABSPATH')) exit;

/* ==============================================================
 1.  [real_reviews]  —  Masonry Grid
     Params: source, min_stars, limit, title
============================================================== */
add_shortcode('real_reviews', function ($atts) {
    $atts = shortcode_atts([
        'source'    => '',
        'min_stars' => 0,
        'limit'     => 0,
        'title'     => '',
    ], $atts, 'real_reviews');

    $api_url = rr_get_api_url();
    $accent  = get_option('real_reviews_accent_color', '#f39c12');
    $title   = !empty($atts['title']) ? $atts['title'] : get_option('real_reviews_section_title', 'Customer Reviews');
    $data    = real_reviews_fetch_and_decode($api_url);

    if (is_wp_error($data))
        return '<p>Error: ' . esc_html($data->get_error_message()) . '</p>';
    if (!is_array($data) || empty($data))
        return '<p>No reviews found.</p>';

    $reviews = array_values(array_filter($data, fn($r) => is_array($r) && (isset($r['comment']) || isset($r['guest_name']))));
    if (empty($reviews)) return '<p>No valid reviews found.</p>';

    /* Apply shortcode filters */
    if (!empty($atts['source'])) {
        $reviews = array_values(array_filter($reviews, fn($r) => ($r['reviewSite'] ?? 'Other') === $atts['source']));
    }
    if (intval($atts['min_stars']) > 0) {
        $reviews = array_values(array_filter($reviews, fn($r) => intval($r['product_evaluation'] ?? 0) >= intval($atts['min_stars'])));
    }
    if (intval($atts['limit']) > 0) {
        $reviews = array_slice($reviews, 0, intval($atts['limit']));
    }

    if (empty($reviews)) return '<p>No reviews match the selected filters.</p>';

    /* Stats */
    $total = 0; $count = 0; $sources = [];
    foreach ($reviews as $r) {
        $rating = intval($r['product_evaluation'] ?? 0);
        if ($rating > 0) { $total += $rating; $count++; }
        $src = $r['reviewSite'] ?? 'Other';
        $sources[$src] = ($sources[$src] ?? 0) + 1;
    }
    $avg            = $count ? round($total / $count, 1) : 0;
    $avg_rounded    = round($avg);
    $company_id_opt = get_option('real_reviews_company_id', 'Com0000DEMO');
    $container_id   = 'rr_grid_' . uniqid();

    wp_enqueue_style('rr-suite-style');
    wp_enqueue_script('rr-suite-scripts');

    ob_start(); ?>
    <div id="<?php echo esc_attr($container_id); ?>" class="rr-wrap" style="--rr-accent:<?php echo esc_attr($accent); ?>">

        <div class="rr-header">
            <h2 class="rr-title"><?php echo esc_html($title); ?></h2>
            <div class="rr-summary">
                <div class="rr-avg-score"><?php echo esc_html($avg); ?></div>
                <div class="rr-summary-right">
                    <div class="rr-stars-row"><?php echo str_repeat('★', $avg_rounded) . str_repeat('☆', 5 - $avg_rounded); ?></div>
                    <div class="rr-count"><?php echo count($reviews); ?> Reviews</div>
                </div>
            </div>
        </div>

        <?php if (empty($atts['source'])): ?>
        <div class="rr-tabs-wrapper">
            <button class="rr-tab active" data-src="__all">
                <div class="rr-all-circle">ALL</div>
                <span><?php echo count($reviews); ?></span>
            </button>
            <?php foreach ($sources as $s => $c): ?>
            <button class="rr-tab" data-src="<?php echo esc_attr($s); ?>">
                <div class="rr-tab-img-box">
                    <img src="https://img.realreviewsbyrp.com/files/app-img/social-media/<?php echo esc_attr($s); ?>.jpg"
                         onerror="this.src='https://img.realreviewsbyrp.com/files/app-img/social-media/default.jpg'"
                         alt="<?php echo esc_attr($s); ?>" loading="lazy">
                </div>
                <span><?php echo esc_html($c); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="rr-grid"></div>

        <div class="rr-see-all-wrap">
            <a class="rr-see-all-btn"
               href="https://realreviewsbyrp.com/<?php echo esc_attr($company_id_opt); ?>"
               target="_blank" rel="noopener noreferrer">
                See All Reviews &rarr;
            </a>
        </div>

        <div class="rr-powered">
            <a href="https://realreviewsbyrealpeople.com/" target="_blank" rel="noopener noreferrer">
                Powered by <span class="p-g">Real</span><span class="p-b">Reviews</span><span>byRealPeople</span>
            </a>
        </div>

    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initRRGrid === 'function') {
            window.initRRGrid('<?php echo esc_js($container_id); ?>', <?php echo wp_json_encode($reviews); ?>);
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

/* ==============================================================
 2.  [real_reviews_carousel]  —  Carousel
     Params: source, min_stars, limit
============================================================== */
add_shortcode('real_reviews_carousel', function ($atts) {
    $atts = shortcode_atts([
        'source'    => '',
        'min_stars' => 0,
        'limit'     => 9,
    ], $atts, 'real_reviews_carousel');

    $api_url = rr_get_api_url();
    $accent  = get_option('real_reviews_accent_color', '#f39c12');
    $data    = real_reviews_fetch_and_decode($api_url);

    if (is_wp_error($data))
        return '<p>Error: ' . esc_html($data->get_error_message()) . '</p>';
    if (!is_array($data) || empty($data))
        return '<p>No reviews found.</p>';

    $all_reviews = array_values(array_filter($data, fn($r) => isset($r['comment'])));

    /* Apply shortcode filters */
    if (!empty($atts['source'])) {
        $all_reviews = array_values(array_filter($all_reviews, fn($r) => ($r['reviewSite'] ?? '') === $atts['source']));
    }
    if (intval($atts['min_stars']) > 0) {
        $all_reviews = array_values(array_filter($all_reviews, fn($r) => intval($r['product_evaluation'] ?? 0) >= intval($atts['min_stars'])));
    }

    /* Stats */
    $total_r = 0; $count_r = 0;
    foreach ($all_reviews as $r) {
        $v = intval($r['product_evaluation'] ?? 0);
        if ($v > 0) { $total_r += $v; $count_r++; }
    }
    $avg_r       = $count_r ? round($total_r / $count_r, 1) : 0;
    $total_count = count($all_reviews);

    $biz_info       = rr_fetch_business_info();
    $company        = (!is_wp_error($biz_info) && !empty($biz_info['NickName']))
        ? $biz_info['NickName']
        : get_bloginfo('name');
    $company_id_opt = get_option('real_reviews_company_id', 'Com0000DEMO');

    $reviews      = array_slice($all_reviews, 0, max(1, intval($atts['limit'])));
    $container_id = 'rrc_' . uniqid();

    wp_enqueue_style('rr-suite-style');
    wp_enqueue_script('rr-suite-scripts');

    ob_start(); ?>
    <div id="<?php echo esc_attr($container_id); ?>"
         class="rrc-carousel"
         style="--rr-accent:<?php echo esc_attr($accent); ?>"
         data-avg="<?php echo esc_attr($avg_r); ?>"
         data-total="<?php echo esc_attr($total_count); ?>"
         data-company="<?php echo esc_attr($company); ?>"
         data-company-id="<?php echo esc_attr($company_id_opt); ?>">

        <div class="rrc-body">
            <div class="rrc-info-panel"></div>
            <div class="rrc-stage-wrap">
                <button class="rrc-arrow rrc-prev" aria-label="Previous">&#8592;</button>
                <div class="rrc-viewport">
                    <div class="rrc-track"></div>
                </div>
                <button class="rrc-arrow rrc-next" aria-label="Next">&#8594;</button>
            </div>
        </div>

        <div class="rrc-dots"></div>

    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initRRCarousel === 'function') {
            window.initRRCarousel('<?php echo esc_js($container_id); ?>', <?php echo wp_json_encode($reviews); ?>);
        }
    });
    </script>
    <?php
    return ob_get_clean();
});

/* ==============================================================
 3.  [real_reviews_form]  —  2-Step Submission Form
============================================================== */
add_shortcode('real_reviews_form', function () {
    $company_id = get_option('real_reviews_company_id', 'Com0000DEMO');

    wp_enqueue_style('rr-suite-style');
    wp_enqueue_script('rr-suite-form');

    ob_start(); ?>
    <div class="rr-form-wrap">

        <h2 class="rr-form-title">Leave Us a Review</h2>
        <p class="rr-form-subtitle">Your feedback helps us improve and lets others know about your experience.</p>

        <div class="rr-stars-section">
            <div class="rr-stars-section-label">How would you rate your experience?</div>
            <div class="rr-stars-box">
                <span class="rr-star-item" data-star="1">★</span>
                <span class="rr-star-item" data-star="2">★</span>
                <span class="rr-star-item" data-star="3">★</span>
                <span class="rr-star-item" data-star="4">★</span>
                <span class="rr-star-item" data-star="5">★</span>
            </div>
            <div id="rrRatingLabel" class="rr-rating-label"></div>
            <div id="rrErrorStar" class="rr-error">Please select a star rating before submitting.</div>
        </div>

        <div id="rrFormStep2" class="rr-form-step2">

            <div class="rr-form-group">
                <label for="rrMessage">Your Review <span class="req">*</span></label>
                <textarea id="rrMessage" class="rr-textarea"
                          placeholder="Tell us about your experience..."
                          maxlength="500"></textarea>
                <div class="rr-char-count" id="rrCharCount">0 / 500</div>
                <div id="rrErrorMessage" class="rr-error">Review must be at least 10 characters.</div>
            </div>

            <div class="rr-form-row">
                <div class="rr-form-group">
                    <label for="rrName">Full Name <span class="req">*</span></label>
                    <input id="rrName" class="rr-input" type="text" placeholder="John Smith" autocomplete="name">
                    <div id="rrErrorName" class="rr-error">Your name is required.</div>
                </div>
                <div class="rr-form-group">
                    <label for="rrEmail">Email <span class="req">*</span></label>
                    <input id="rrEmail" class="rr-input" type="email" placeholder="john@email.com" autocomplete="email">
                    <div id="rrErrorEmail" class="rr-error">A valid email is required.</div>
                </div>
            </div>

            <button id="rrSubmitBtn" class="rr-btn"
                    onclick="rrSubmit('<?php echo esc_js($company_id); ?>')">
                <span class="rr-btn-spinner"></span>
                <span class="rr-btn-label">Submit Review</span>
            </button>

            <div id="rrResp" class="rr-resp-box"></div>

        </div>

        <div class="rr-powered" style="margin-top:24px;">
            <a href="https://realreviewsbyrealpeople.com/" target="_blank" rel="noopener noreferrer">
                Powered by <span class="p-g">Real</span><span class="p-b">Reviews</span><span>byRealPeople</span>
            </a>
        </div>

    </div>
    <?php
    return ob_get_clean();
});

/* ==============================================================
 4.  [real_reviews_badge]  —  Trust Badge
     Params: position (bottom-right | bottom-left | static), source
============================================================== */
add_shortcode('real_reviews_badge', function ($atts) {
    $atts = shortcode_atts([
        'position' => 'bottom-right',
        'source'   => '',
    ], $atts, 'real_reviews_badge');

    $api_url = rr_get_api_url();
    $accent  = get_option('real_reviews_accent_color', '#f39c12');
    $data    = real_reviews_fetch_and_decode($api_url);

    if (is_wp_error($data) || !is_array($data)) return '';

    $reviews = array_values(array_filter($data, fn($r) => isset($r['comment'])));
    if (!empty($atts['source'])) {
        $reviews = array_values(array_filter($reviews, fn($r) => ($r['reviewSite'] ?? '') === $atts['source']));
    }
    if (empty($reviews)) return '';

    $total = 0; $count = 0;
    foreach ($reviews as $r) {
        $v = intval($r['product_evaluation'] ?? 0);
        if ($v > 0) { $total += $v; $count++; }
    }
    $avg            = $count ? round($total / $count, 1) : 0;
    $avg_r          = round($avg);
    $pos_cls        = 'rr-badge--' . sanitize_html_class($atts['position']);
    $company_id_opt = get_option('real_reviews_company_id', 'Com0000DEMO');

    wp_enqueue_style('rr-suite-style');

    ob_start(); ?>
    <div class="rr-badge <?php echo esc_attr($pos_cls); ?>" style="--rr-accent:<?php echo esc_attr($accent); ?>" role="complementary" aria-label="Review rating badge">
        <button class="rr-badge-close" onclick="this.closest('.rr-badge').style.display='none'" aria-label="Close">×</button>
        <div class="rr-badge-logo">
            <span class="rr-badge-logo-g">Real</span><span class="rr-badge-logo-b">Reviews</span>
        </div>
        <div class="rr-badge-score"><?php echo esc_html($avg > 0 ? $avg : '—'); ?></div>
        <div class="rr-badge-stars">
            <span style="color:#8BC53F"><?php echo str_repeat('★', $avg_r); ?></span><span style="color:rgba(0,0,0,.15)"><?php echo str_repeat('★', 5 - $avg_r); ?></span>
        </div>
        <div class="rr-badge-count"><?php echo esc_html(count($reviews)); ?> reviews</div>
        <div class="rr-badge-verified">✓ Verified</div>
        <a class="rr-badge-link"
           href="https://realreviewsbyrp.com/<?php echo esc_attr($company_id_opt); ?>"
           target="_blank" rel="noopener noreferrer">
            See all reviews &rarr;
        </a>
    </div>
    <?php
    return ob_get_clean();
});

/* ==============================================================
 5.  [real_reviews_featured]  —  Featured Review
     Params: source, min_stars (default 5), random (true|false)
============================================================== */
add_shortcode('real_reviews_featured', function ($atts) {
    $atts = shortcode_atts([
        'source'    => '',
        'min_stars' => 5,
        'random'    => 'false',
    ], $atts, 'real_reviews_featured');

    $api_url = rr_get_api_url();
    $accent  = get_option('real_reviews_accent_color', '#f39c12');
    $data    = real_reviews_fetch_and_decode($api_url);

    if (is_wp_error($data) || !is_array($data)) return '';

    $reviews = array_values(array_filter($data, fn($r) => isset($r['comment'])));

    if (!empty($atts['source'])) {
        $reviews = array_values(array_filter($reviews, fn($r) => ($r['reviewSite'] ?? '') === $atts['source']));
    }
    if (intval($atts['min_stars']) > 0) {
        $reviews = array_values(array_filter($reviews, fn($r) => intval($r['product_evaluation'] ?? 0) >= intval($atts['min_stars'])));
    }
    if (empty($reviews)) return '';

    $review = ($atts['random'] === 'true') ? $reviews[array_rand($reviews)] : $reviews[0];

    $name    = $review['guest_name']       ?? 'Anonymous';
    $comment = $review['comment']          ?? '';
    $rating  = intval($review['product_evaluation'] ?? 5);
    $date    = $review['comment_date']     ?? '';
    $src     = $review['reviewSite']       ?? '';

    wp_enqueue_style('rr-suite-style');

    ob_start(); ?>
    <div class="rr-featured" style="--rr-accent:<?php echo esc_attr($accent); ?>">
        <div class="rr-featured-quote">❝</div>
        <div class="rr-featured-text"><?php echo esc_html($comment); ?></div>
        <div class="rr-featured-stars">
            <span style="color:<?php echo esc_attr($accent); ?>"><?php echo str_repeat('★', min(5, $rating)); ?></span><span style="color:#d0d8e4"><?php echo str_repeat('★', max(0, 5 - $rating)); ?></span>
        </div>
        <div class="rr-featured-author">
            <div class="rr-featured-meta">
                <strong><?php echo esc_html($name); ?></strong>
                <?php if ($date): ?><span><?php echo esc_html($date); ?></span><?php endif; ?>
            </div>
            <?php if ($src): ?>
            <img src="https://img.realreviewsbyrp.com/files/app-img/social-media/<?php echo esc_attr($src); ?>.jpg"
                 onerror="this.style.display='none'"
                 alt="<?php echo esc_attr($src); ?>"
                 loading="lazy"
                 class="rr-featured-platform">
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

/* ==============================================================
 6.  [real_reviews_score]  —  Inline Score Widget
     Params: source, show_count (true|false), label
============================================================== */
add_shortcode('real_reviews_score', function ($atts) {
    $atts = shortcode_atts([
        'source'     => '',
        'show_count' => 'true',
        'label'      => 'Based on',
    ], $atts, 'real_reviews_score');

    $api_url = rr_get_api_url();
    $accent  = get_option('real_reviews_accent_color', '#f39c12');
    $data    = real_reviews_fetch_and_decode($api_url);

    if (is_wp_error($data) || !is_array($data)) return '';

    $reviews = array_values(array_filter($data, fn($r) => isset($r['comment'])));
    if (!empty($atts['source'])) {
        $reviews = array_values(array_filter($reviews, fn($r) => ($r['reviewSite'] ?? '') === $atts['source']));
    }

    $total = 0; $count = 0;
    foreach ($reviews as $r) {
        $v = intval($r['product_evaluation'] ?? 0);
        if ($v > 0) { $total += $v; $count++; }
    }
    $avg            = $count ? round($total / $count, 1) : 0;
    $avg_r          = round($avg);
    $company_id_opt = get_option('real_reviews_company_id', 'Com0000DEMO');

    wp_enqueue_style('rr-suite-style');

    ob_start(); ?>
    <a class="rr-score-inline" style="--rr-accent:<?php echo esc_attr($accent); ?>"
       href="https://realreviewsbyrp.com/<?php echo esc_attr($company_id_opt); ?>"
       target="_blank" rel="noopener noreferrer"
       title="See all reviews">
        <span class="rr-score-num"><?php echo esc_html($avg > 0 ? $avg : '—'); ?></span>
        <span class="rr-score-stars">
            <span style="color:<?php echo esc_attr($accent); ?>"><?php echo str_repeat('★', $avg_r); ?></span><span style="color:#d0d8e4"><?php echo str_repeat('★', 5 - $avg_r); ?></span>
        </span>
        <?php if ($atts['show_count'] !== 'false'): ?>
        <span class="rr-score-count"><?php echo esc_html($atts['label'] . ' ' . count($reviews) . ' reviews'); ?></span>
        <?php endif; ?>
    </a>
    <?php
    return ob_get_clean();
});
