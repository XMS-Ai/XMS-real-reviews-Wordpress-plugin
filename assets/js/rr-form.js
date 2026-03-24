/* ==============================================================
   REAL REVIEWS SUITE — FORM LOGIC  v4.1
============================================================== */

(function() {
    "use strict";

    var rrStar   = 0;
    var step2Open = false;
    var LABELS   = { 1:'Very Poor', 2:'Poor', 3:'Average', 4:'Good', 5:'Excellent' };
    var EMOJIS   = { 1:'😞', 2:'😕', 3:'😐', 4:'😊', 5:'🌟' };

    function isValidEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).toLowerCase());
    }
    function paintStars(n, isHover) {
        document.querySelectorAll('.rr-star-item').forEach(function(s) {
            var v = parseInt(s.dataset.star);
            s.classList.remove('active', 'hover');
            if (v <= n) s.classList.add(isHover ? 'hover' : 'active');
        });
    }
    function showErr(id) { var el = document.getElementById(id); if (el) el.style.display = 'flex'; }
    function hideErr(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; }
    function showResp(type, msg) {
        var box = document.getElementById('rrResp');
        if (!box) return;
        box.className = 'rr-resp-box ' + type;
        box.innerHTML = msg;
        box.style.display = 'block';
        setTimeout(function() { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 50);
    }

    /* ── Reveal step 2 ── */
    function revealStep2() {
        if (step2Open) return;
        step2Open = true;

        var step2 = document.getElementById('rrFormStep2');
        if (!step2) return;

        step2.classList.add('rr-visible');
        step2.style.display = 'block'; /* fallback */

        /* Smooth scroll into view */
        setTimeout(function() {
            step2.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 80);
    }

    /* ── Star interactions ── */
    document.querySelectorAll('.rr-star-item').forEach(function(star) {
        var val = parseInt(star.dataset.star);

        star.addEventListener('mouseenter', function() { paintStars(val, true); });
        star.addEventListener('mouseleave', function() { paintStars(rrStar, false); });
        star.addEventListener('click', function() {
            rrStar = val;
            paintStars(rrStar, false);

            /* Rating label */
            var lbl = document.getElementById('rrRatingLabel');
            if (lbl) {
                lbl.textContent    = EMOJIS[rrStar] + ' ' + LABELS[rrStar];
                lbl.style.display  = 'inline-flex';
            }
            /* Update stars-box border */
            var box = document.querySelector('.rr-stars-box');
            if (box) box.style.borderColor = '#f4c02f';

            hideErr('rrErrorStar');
            revealStep2();
        });
    });

    /* ── Character counter ── */
    var textarea = document.getElementById('rrMessage');
    var counter  = document.getElementById('rrCharCount');
    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            var len = textarea.value.length;
            counter.textContent = len + ' / 500';
            counter.classList.toggle('warn', len > 450);
            if (len > 500) textarea.value = textarea.value.slice(0, 500);
        });
    }

    /* ── Submit ── */
    window.rrSubmit = function(companyId) {
        var nameEl  = document.getElementById('rrName');
        var emailEl = document.getElementById('rrEmail');
        var msgEl   = document.getElementById('rrMessage');
        var btn     = document.getElementById('rrSubmitBtn');
        var resp    = document.getElementById('rrResp');

        var name    = nameEl  ? nameEl.value.trim()  : '';
        var email   = emailEl ? emailEl.value.trim() : '';
        var message = msgEl   ? msgEl.value.trim()   : '';

        ['rrErrorStar','rrErrorMessage','rrErrorName','rrErrorEmail'].forEach(hideErr);
        if (resp) resp.style.display = 'none';

        var ok = true;
        if (rrStar === 0)         { showErr('rrErrorStar');    ok = false; }
        if (message.length < 10)  { showErr('rrErrorMessage'); ok = false; }
        if (!name)                { showErr('rrErrorName');    ok = false; }
        if (!isValidEmail(email)) { showErr('rrErrorEmail');   ok = false; }
        if (!ok) return;

        if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }

        var payload = JSON.stringify({
            star: rrStar, nameUser: name, emailUser: email, MessageUser: message,
            ReviewSite: 'RealReviewsbyRealPeople', ReviewUrl: window.location.href, CompanyId: companyId
        });

        fetch('https://api.realreviewsbyrp.com/24867dekf/', {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body: btoa(unescape(encodeURIComponent(payload)))
        })
        .then(function(res) { return res.text(); })
        .then(function(txt) {
            var data;
            try { data = JSON.parse(txt); } catch(e) {
                showResp('error', '⚠ Unexpected server response. Please try again.');
                return;
            }
            if (data && data.key) {
                showResp('success', '🎉 Thank you! Your review has been submitted successfully.');
                /* Reset */
                rrStar = 0; step2Open = false;
                paintStars(0, false);
                if (msgEl)   msgEl.value   = '';
                if (nameEl)  nameEl.value  = '';
                if (emailEl) emailEl.value = '';
                var lbl = document.getElementById('rrRatingLabel');
                if (lbl) lbl.style.display = 'none';
                var box = document.querySelector('.rr-stars-box');
                if (box) box.style.borderColor = '';
                if (counter) counter.textContent = '0 / 500';
                /* Collapse step 2 after 3s */
                setTimeout(function() {
                    var s2 = document.getElementById('rrFormStep2');
                    if (s2) { s2.classList.remove('rr-visible'); s2.style.display = 'none'; }
                    if (resp) resp.style.display = 'none';
                }, 3500);
            } else {
                showResp('error', '⚠ Submission failed. Please try again.');
            }
        })
        .catch(function(e) {
            showResp('error', '⚠ Network error: ' + e.message);
        })
        .finally(function() {
            if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }
        });
    };

})();
