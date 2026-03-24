/* ==============================================================
   REAL REVIEWS SUITE — GRID & CAROUSEL  v4.1
============================================================== */

(function() {
    "use strict";

    /* ── Helpers ── */
    function esc(t) {
        return t ? String(t).replace(/[&<>"']/g, function(m) {
            return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m];
        }) : '';
    }

    var COLORS = [
        '#6366f1','#3b82f6','#0ea5e9','#10b981',
        '#f59e0b','#ef4444','#8b5cf6','#ec4899',
        '#14b8a6','#f97316','#06b6d4','#84cc16'
    ];
    function avatarColor(name) {
        var h = 0, s = String(name || '');
        for (var i = 0; i < s.length; i++) h = s.charCodeAt(i) + ((h << 5) - h);
        return COLORS[Math.abs(h) % COLORS.length];
    }
    function initials(name) {
        var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '?';
        if (parts.length === 1) return parts[0][0].toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    function starHTML(n) {
        return '<span class="rr-stars-filled">' + '★'.repeat(Math.max(0, n)) + '</span>'
             + '<span class="rr-stars-empty">'  + '☆'.repeat(Math.max(0, 5 - n)) + '</span>';
    }

    /* ================================================================
       GRID  —  window.initRRGrid
    ================================================================ */
    window.initRRGrid = function(containerId, reviews) {
        var el   = document.getElementById(containerId);
        var grid = el && el.querySelector('.rr-grid');
        var tabs = el ? el.querySelectorAll('.rr-tab') : [];
        if (!grid) return;

        function buildCard(r, idx) {
            var name    = r.guest_name || 'Anonymous';
            var nameE   = esc(name);
            var comment = esc(r.comment || '');
            var src     = r.reviewSite || 'Other';
            var srcE    = esc(src);
            var date    = esc(r.comment_date || '');
            var rating  = Math.max(0, Math.min(5, parseInt(r.product_evaluation || 0)));
            var reply   = esc(r.comment_reply || '');
            var color   = avatarColor(name);
            var inits   = initials(name);
            var isLong  = (r.comment || '').length > 220;
            var delay   = (idx % 12) * 35;
            var cardId  = 'rrcmt-' + containerId + '-' + idx;

            var commentBlock = isLong
                ? '<div class="rr-comment rr-comment-truncated" id="' + cardId + '">' + comment + '</div>'
                  + '<button class="rr-read-more" onclick="rrToggle(this,\'' + cardId + '\')">Read more ▾</button>'
                : '<div class="rr-comment">' + comment + '</div>';

            return '<div class="rr-card" data-src="' + srcE + '" style="animation-delay:' + delay + 'ms">'
                + '<div class="rr-card-top">'
                +   '<div class="rr-avatar" style="background:' + color + '">' + inits + '</div>'
                +   '<div class="rr-card-info">'
                +     '<div class="rr-name">' + nameE + '</div>'
                +     '<div class="rr-meta">' + date + (src !== 'Other' ? ' &middot; ' + srcE : '') + '</div>'
                +   '</div>'
                +   '<div class="rr-platform-badge">'
                +     '<img src="https://img.realreviewsbyrp.com/files/app-img/social-media/' + srcE + '.jpg"'
                +          ' onerror="this.parentElement.style.display=\'none\'" loading="lazy" alt="' + srcE + '">'
                +   '</div>'
                + '</div>'
                + '<div class="rr-rating">' + starHTML(rating) + '<span class="rr-score">' + rating + '.0</span></div>'
                + commentBlock
                + (reply ? '<div class="rr-reply">' + reply + '</div>' : '')
                + '</div>';
        }

        function render(src) {
            grid.innerHTML = '';
            var show = reviews.filter(function(r) {
                return src === '__all' || (r.reviewSite || 'Other') === src;
            });
            if (!show.length) {
                grid.innerHTML = '<p style="text-align:center;color:#888;padding:2rem 0;grid-column:1/-1;">No reviews found for this platform.</p>';
                return;
            }
            grid.innerHTML = show.map(buildCard).join('');
        }

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                tabs.forEach(function(b) { b.classList.remove('active'); });
                tab.classList.add('active');
                render(tab.dataset.src);
            });
        });

        render('__all');
    };

    window.rrToggle = function(btn, id) {
        var el = document.getElementById(id);
        if (!el) return;
        var collapsed = el.classList.contains('rr-comment-truncated');
        el.classList.toggle('rr-comment-truncated', !collapsed);
        btn.textContent = collapsed ? 'Read less ▴' : 'Read more ▾';
    };

    /* ================================================================
       CAROUSEL  —  window.initRRCarousel
       3-up layout with info panel, fixed card height, paginated dots
    ================================================================ */
    window.initRRCarousel = function(containerId, reviews) {
        var el        = document.getElementById(containerId);
        var track     = el && el.querySelector('.rrc-track');
        var viewport  = el && el.querySelector('.rrc-viewport');
        var prevBtn   = el && el.querySelector('.rrc-prev');
        var nextBtn   = el && el.querySelector('.rrc-next');
        var dotsEl    = el && el.querySelector('.rrc-dots');
        var infoPanel = el && el.querySelector('.rrc-info-panel');

        if (!track || !reviews.length) return;

        var total   = reviews.length;
        var current = 0; // current page index
        var GAP     = 16;

        /* ── Info panel ── */
        if (infoPanel) {
            var avg     = parseFloat(el.dataset.avg)     || 0;
            var cnt     = parseInt(el.dataset.total)     || total;
            var company = el.dataset.company             || '';
            var avgR    = Math.round(avg);

            infoPanel.innerHTML =
                '<div class="rrc-info-header">'
                +   '<div class="rrc-info-logo">'
                +     '<span style="color:#8BC53F;font-weight:900;">Real</span>'
                +     '<span style="color:#00AEEF;font-weight:900;">Reviews</span>'
                +   '</div>'
                +   '<div class="rrc-info-verified">'
                +     '<span class="rrc-info-check">✓</span>Verified'
                +   '</div>'
                + '</div>'
                + '<div class="rrc-info-body">'
                +   '<div class="rrc-info-name">' + esc(company) + '</div>'
                +   '<div class="rrc-info-score-row">'
                +     '<span class="rrc-info-score">' + (avg > 0 ? avg.toFixed(1) : '—') + '</span>'
                +     '<div class="rrc-info-stars">'
                +       '<span style="color:#8BC53F">' + '★'.repeat(Math.max(0, avgR)) + '</span>'
                +       '<span style="color:rgba(255,255,255,.18)">' + '★'.repeat(Math.max(0, 5 - avgR)) + '</span>'
                +     '</div>'
                +   '</div>'
                +   '<div class="rrc-info-based">Based on <strong>' + cnt + '</strong> verified reviews</div>'
                + '</div>'
                + '<a class="rrc-info-brand-link" href="https://realreviewsbyrealpeople.com/" target="_blank" rel="noopener noreferrer">'
                +   '<span style="font-size:10px;color:rgba(255,255,255,.35);letter-spacing:.5px;text-transform:uppercase;">powered by</span>'
                +   '<span>'
                +     '<span style="color:#8BC53F;font-weight:800;font-size:13px;">Real</span>'
                +     '<span style="color:#00AEEF;font-weight:800;font-size:13px;">Reviews</span>'
                +     '<span style="color:rgba(255,255,255,.4);font-weight:400;font-size:11px;">byRealPeople</span>'
                +   '</span>'
                + '</a>';
        }

        /* ── Build cards ── */
        track.innerHTML = reviews.map(function(r) {
            var name    = r.guest_name || 'Anonymous';
            var nameE   = esc(name);
            var comment = esc(r.comment || '');
            var date    = esc(r.comment_date || '');
            var rating  = Math.max(0, Math.min(5, parseInt(r.product_evaluation || 0)));
            var site    = esc((r.reviewSite || 'other').toLowerCase());
            var color   = avatarColor(name);
            var inits   = initials(name);

            return '<div class="rrc-card">'
                + '<div class="rrc-card-header">'
                +   '<div class="rrc-user">'
                +     '<div class="rrc-avatar" style="background:' + color + '">' + inits + '</div>'
                +     '<div class="rrc-user-info">'
                +       '<div class="rrc-name">' + nameE + '</div>'
                +       '<div class="rrc-date">' + date + '</div>'
                +     '</div>'
                +   '</div>'
                +   '<div class="rrc-site-icon">'
                +     '<img src="https://img.realreviewsbyrp.com/files/app-img/social-media/' + site + '.jpg"'
                +          ' onerror="this.parentElement.style.display=\'none\'" loading="lazy" alt="' + site + '">'
                +   '</div>'
                + '</div>'
                + '<div class="rrc-stars-row">'
                +   '<span style="color:var(--rr-accent)">' + '★'.repeat(rating) + '</span>'
                +   '<span style="color:#d0d8e4">'          + '★'.repeat(5 - rating) + '</span>'
                + '</div>'
                + '<div class="rrc-comment">' + comment + '</div>'
                + '</div>';
        }).join('');

        /* ── Helpers ── */
        function ipv() {
            return window.innerWidth < 640 ? 1 : (window.innerWidth < 900 ? 2 : 3);
        }
        function cardW() {
            var vw = viewport ? viewport.offsetWidth : 0;
            var n  = ipv();
            return Math.floor((vw - GAP * (n - 1)) / n);
        }
        function totalPages() { return Math.ceil(total / ipv()); }

        function setWidths() {
            var cw = cardW();
            track.querySelectorAll('.rrc-card').forEach(function(c) {
                c.style.width    = cw + 'px';
                c.style.minWidth = cw + 'px';
            });
        }

        /* ── Dots ── */
        function buildDots() {
            if (!dotsEl) return;
            dotsEl.innerHTML = '';
            var pages = totalPages();
            for (var i = 0; i < pages; i++) {
                (function(idx) {
                    var dot = document.createElement('button');
                    dot.className = 'rrc-dot' + (idx === 0 ? ' active' : '');
                    dot.setAttribute('aria-label', 'Page ' + (idx + 1));
                    dot.addEventListener('click', function() { goTo(idx); });
                    dotsEl.appendChild(dot);
                })(i);
            }
        }
        function updateDots(page) {
            if (!dotsEl) return;
            dotsEl.querySelectorAll('.rrc-dot').forEach(function(d, i) {
                d.classList.toggle('active', i === page);
            });
        }

        /* ── Navigation ── */
        function goTo(page) {
            var pages = totalPages();
            current   = ((page % pages) + pages) % pages; // wraps around
            var cw    = cardW();
            var offset = current * ipv() * (cw + GAP);
            track.style.transform = 'translateX(-' + offset + 'px)';
            updateDots(current);
        }

        if (prevBtn) prevBtn.addEventListener('click', function() { goTo(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function() { goTo(current + 1); });

        /* ── Touch swipe ── */
        var touchX = 0;
        if (viewport) {
            viewport.addEventListener('touchstart', function(e) { touchX = e.touches[0].clientX; }, { passive: true });
            viewport.addEventListener('touchend',   function(e) {
                var diff = touchX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) goTo(diff > 0 ? current + 1 : current - 1);
            }, { passive: true });
        }

        /* ── Resize ── */
        var rTimer;
        window.addEventListener('resize', function() {
            clearTimeout(rTimer);
            rTimer = setTimeout(function() {
                setWidths();
                buildDots();
                goTo(current);
            }, 150);
        });

        /* ── Auto-slide ── */
        var aTimer;
        function startAuto() { aTimer = setInterval(function() { goTo(current + 1); }, 5000); }
        function stopAuto()  { clearInterval(aTimer); }
        el.addEventListener('mouseenter', stopAuto);
        el.addEventListener('mouseleave', startAuto);

        /* ── Init ── */
        setWidths();
        buildDots();
        goTo(0);
        startAuto();
    };

})();
