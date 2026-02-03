document.addEventListener('DOMContentLoaded', () => {

    // Initializing AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true,
            duration: 1000,
            easing: 'ease-out-cubic'
        });
    }

    // Sidebar Toggle Logic
    function initSidebarToggle() {
        const sidebar = document.querySelector('.sidebar-elite');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.querySelector('.sidebar-toggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                if (overlay) overlay.classList.toggle('show');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }

    // Initialize
    initSidebarToggle();

    // --- Global Countdown System ---
    const timers = document.querySelectorAll('.timer-val');

    function updateCountdowns() {
        timers.forEach(timer => {
            let d = parseInt(timer.getAttribute('data-days')) || 0;
            let h = parseInt(timer.getAttribute('data-hours')) || 0;
            let m = parseInt(timer.getAttribute('data-min')) || 0;
            let s = parseInt(timer.getAttribute('data-sec')) || 0;

            if (s > 0) {
                s--;
            } else {
                if (m > 0) {
                    m--; s = 59;
                } else {
                    if (h > 0) {
                        h--; m = 59; s = 59;
                    } else {
                        if (d > 0) {
                            d--; h = 23; m = 59; s = 59;
                        }
                    }
                }
            }

            timer.setAttribute('data-days', d);
            timer.setAttribute('data-hours', h);
            timer.setAttribute('data-min', m);
            timer.setAttribute('data-sec', s);

            const dayEl = timer.querySelector('[data-days]') || (timer.hasAttribute('data-days') ? timer : null);
            const hourEl = timer.querySelector('[data-hours]') || (timer.hasAttribute('data-hours') ? timer : null);
            const minEl = timer.querySelector('[data-min]') || (timer.hasAttribute('data-min') ? timer : null);
            const secEl = timer.querySelector('[data-sec]') || (timer.hasAttribute('data-sec') ? timer : null);

            if (dayEl && dayEl !== timer) dayEl.innerText = d.toString().padStart(2, '0');
            if (hourEl && hourEl !== timer) hourEl.innerText = h.toString().padStart(2, '0');
            if (minEl && minEl !== timer) minEl.innerText = m.toString().padStart(2, '0');
            if (secEl && secEl !== timer) secEl.innerText = s.toString().padStart(2, '0');

            if (timer.classList.contains('combined-timer')) {
                timer.innerText = `${d.toString().padStart(2, '0')}:${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }
        });
    }

    if (timers.length > 0) {
        setInterval(updateCountdowns, 1000);
    }

    // --- Watchlist Toggle System ---
    const watchlistForms = document.querySelectorAll('.watchlist-toggle-form');

    watchlistForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const button = form.querySelector('button');
            const icon = button.querySelector('i');
            const url = form.getAttribute('action');
            const csrf = form.querySelector('input[name="_token"]').value;

            // Optional: Add loading state
            button.style.opacity = '0.5';
            button.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 401 || response.redirected) {
                    window.location.href = '/login';
                    return;
                }

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    // Fallback to normal submission if not JSON
                    form.submit();
                    return;
                }

                const data = await response.json();

                if (data.status === 'added') {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    // If it's a card icon, it already has text-danger usually, 
                    // but for the detail page button we might want to handle text
                } else if (data.status === 'removed') {
                    icon.classList.remove('fas');
                    icon.classList.add('far');

                    // If we are on the watchlist page, remove the row
                    if (window.location.pathname.includes('/user/watchlist')) {
                        const row = form.closest('tr');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                // Check if table is empty
                                const tbody = document.querySelector('tbody');
                                if (tbody && tbody.children.length === 0) {
                                    window.location.reload(); // Simple way to show empty state
                                }
                            }, 300);
                        }
                    }
                }
            } catch (error) {
                console.error('Watchlist toggle failed:', error);
            } finally {
                button.style.opacity = '1';
                button.disabled = false;
            }
        });
    });

    console.log('LaraBids Elite System Initialized.');
});
