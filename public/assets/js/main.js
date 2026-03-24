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
    function updateCountdowns() {
        const timers = document.querySelectorAll('.timer-val');
        timers.forEach(timer => {
            let d = parseInt(timer.getAttribute('data-days')) || 0;
            let h = parseInt(timer.getAttribute('data-hours')) || 0;
            let m = parseInt(timer.getAttribute('data-min')) || 0;
            let s = parseInt(timer.getAttribute('data-sec')) || 0;

            if (d === 0 && h === 0 && m === 0 && s === 0) return;

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

            const dayEl = timer.querySelector('[data-days]');
            const hourEl = timer.querySelector('[data-hours]');
            const minEl = timer.querySelector('[data-min]');
            const secEl = timer.querySelector('[data-sec]');

            if (dayEl) dayEl.innerText = d.toString().padStart(2, '0');
            if (hourEl) hourEl.innerText = h.toString().padStart(2, '0');
            if (minEl) minEl.innerText = m.toString().padStart(2, '0');
            if (secEl) secEl.innerText = s.toString().padStart(2, '0');

            if (timer.classList.contains('combined-timer')) {
                timer.innerText = `${d.toString().padStart(2, '0')}:${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }

            // Urgency logic: < 1 hour (0 days, 0 hours) -> Red Box
            if (d === 0 && h === 0 && !isUpcoming(timer)) {
                timer.classList.add('urgent-timer');
            } else {
                timer.classList.remove('urgent-timer');
            }
        });
    }

    function isUpcoming(timer) {
        // Simple check if it's an upcoming timer badge helper
        return timer.closest('.alert-info') !== null;
    }

    setInterval(updateCountdowns, 1000);

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
                    icon.classList.add('fas', 'text-danger');
                } else if (data.status === 'removed') {
                    icon.classList.remove('fas', 'text-danger');
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

    // --- Search Reset System ---
    const searchInput = document.querySelector('.nav-search input[name="q"]');
    if (searchInput) {
        const handleSearchReset = () => {
            if (searchInput.value === '') {
                // If input is cleared and we are on a search results page, reset
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('q')) {
                    searchInput.closest('form').submit();
                }
            }
        };

        searchInput.addEventListener('search', handleSearchReset);
        searchInput.addEventListener('input', handleSearchReset);
    }

    // --- Auto-dismiss Alerts System ---
    function setupAutoDismiss() {
        const path = window.location.pathname;
        const isContactPage = path === '/contact' || path === '/contact/';
        const isAuctionShowPage = /^\/auctions\/\d+$/.test(path);
        
        // Sirf in 2 pages par auto dismiss chalega
        if (!isContactPage && !isAuctionShowPage) {
            return;
        }

        // Function to dismiss a single alert
        const dismissAlert = (alert) => {
            if (alert.classList.contains('alert-permanent')) return;

            setTimeout(() => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                } else {
                    alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 600);
                }
            }, 5000);
        };

        // 1. Process existing alerts
        document.querySelectorAll('.alert').forEach(dismissAlert);

        // 2. Observe for dynamically added alerts (AJAX responses)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList.contains('alert')) {
                            dismissAlert(node);
                        } else {
                            // Check children for alerts
                            node.querySelectorAll('.alert').forEach(dismissAlert);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }
    setupAutoDismiss();

    console.log('LaraBids Elite System Initialized.');
});
