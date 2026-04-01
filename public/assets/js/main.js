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

    // --- Watchlist Toggle System (Using Event Delegation for AJAX support) ---
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.watchlist-toggle-form');
        if (!form) return;

        e.preventDefault();

        const button = form.querySelector('button');
        const icon = button.querySelector('i');
        const url = form.getAttribute('action');
        const csrfInput = form.querySelector('input[name="_token"]');
        const csrf = csrfInput ? csrfInput.value : document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Loading state
        const originalIconClass = icon.className;
        icon.className = 'fas fa-spinner fa-spin';
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

            const data = await response.json();

            if (data.status === 'added') {
                icon.className = 'fas fa-heart text-danger';
                showToast('success', 'Added to watchlist');
            } else if (data.status === 'removed') {
                icon.className = 'far fa-heart';
                showToast('info', 'Removed from watchlist');

                if (window.location.pathname.includes('/user/watchlist')) {
                    const row = form.closest('tr');
                    if (row) {
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            if (document.querySelector('tbody').children.length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                }
            }
        } catch (error) {
            console.error('Watchlist toggle failed:', error);
            icon.className = originalIconClass;
            showToast('error', 'Something went wrong');
        } finally {
            button.disabled = false;
        }
    });

    // --- Auction Registration System (AJAX) ---
    const registrationForms = document.querySelectorAll('.registration-form');
    registrationForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = form.querySelector('button');
            const originalHtml = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Registering...';
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: data.message,
                        confirmButtonText: 'Great!',
                        confirmButtonColor: '#4e73df',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.reload(); // Reload to show bid form
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Registration failed.',
                        confirmButtonColor: '#e74a3b'
                    });
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Registration failed:', error);
                button.innerHTML = originalHtml;
                button.disabled = false;
            }
        });
    });

    // Helper: Show Toast
    function showToast(icon, title) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        Toast.fire({ icon, title });
    }

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
