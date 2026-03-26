(function () {
    function initAOS() {
        if (window.AOS) {
            window.AOS.init({
                duration: 1000,
                once: true,
            });
        }
    }

    function bindNavbarScrollEffect() {
        var ticking = false;
        var applyNavbarState = function () {
            var navbar = document.querySelector('.navbar-custom');
            if (!navbar) return;

            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };

        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                applyNavbarState();
                ticking = false;
            });
        }, { passive: true });

        applyNavbarState();
    }

    function bindSmoothScroll() {
        document.addEventListener('click', function (e) {
            var anchor = e.target && e.target.closest ? e.target.closest('a[href^="#"]') : null;
            if (!anchor) return;

            var href = anchor.getAttribute('href');
            var target = href ? document.querySelector(href) : null;

            if (!target) return;

            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        });
    }

    function optimizeNavigationTransitions() {
        var warmedUrls = new Set();
        var warmingUrls = new Set();
        var progressBar = document.getElementById('visitorNavProgressBar');
        var resetTimer = null;

        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'visitorNavProgressBar';
            progressBar.style.position = 'fixed';
            progressBar.style.top = '0';
            progressBar.style.left = '0';
            progressBar.style.height = '3px';
            progressBar.style.width = '100%';
            progressBar.style.opacity = '0';
            progressBar.style.zIndex = '3000';
            progressBar.style.pointerEvents = 'none';
            progressBar.style.background = 'linear-gradient(90deg, #6ec4eb 0%, #2a93cc 100%)';
            progressBar.style.boxShadow = '0 0 10px rgba(42, 147, 204, 0.55)';
            progressBar.style.transform = 'scaleX(0)';
            progressBar.style.transformOrigin = 'left center';
            progressBar.style.transition = 'transform 0.28s ease, opacity 0.18s ease';
            document.body.appendChild(progressBar);
        }

        var isInternalLink = function (href) {
            if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
                return false;
            }

            try {
                var url = new URL(href, window.location.origin);
                return url.origin === window.location.origin;
            } catch (error) {
                return false;
            }
        };

        var canPrefetch = function (link) {
            if (!link || link.classList.contains('no-prefetch')) return false;
            if (link.target === '_blank' || link.hasAttribute('download')) return false;
            return isInternalLink(link.getAttribute('href'));
        };

        var prefetchUrl = function (href) {
            var url = new URL(href, window.location.origin).toString();
            if (warmedUrls.has(url) || warmingUrls.has(url)) return;

            warmingUrls.add(url);
            var hint = document.createElement('link');
            hint.rel = 'prefetch';
            hint.href = url;
            hint.as = 'document';
            document.head.appendChild(hint);

            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Nav-Warm': '1' },
            })
            .catch(function () {})
            .finally(function () {
                warmingUrls.delete(url);
                warmedUrls.add(url);
            });
        };

        var startProgress = function () {
            clearTimeout(resetTimer);
            progressBar.style.transform = 'scaleX(0.42)';
            progressBar.style.opacity = '1';

            window.requestAnimationFrame(function () {
                progressBar.style.transform = 'scaleX(0.82)';
            });
        };

        var finishProgress = function () {
            progressBar.style.transform = 'scaleX(1)';
            progressBar.style.opacity = '0';
            resetTimer = setTimeout(function () {
                progressBar.style.transform = 'scaleX(0)';
            }, 220);
        };

        var getLink = function (event) {
            if (!event.target || !event.target.closest) return null;
            return event.target.closest('a[href]');
        };

        var prefetchOnIntent = function (event) {
            var link = getLink(event);
            if (!canPrefetch(link)) return;
            prefetchUrl(link.getAttribute('href'));
        };

        document.addEventListener('pointerdown', prefetchOnIntent, { passive: true });
        document.addEventListener('focusin', prefetchOnIntent, { passive: true });
        document.addEventListener('touchstart', prefetchOnIntent, { passive: true });
        document.addEventListener('click', function (event) {
            var link = getLink(event);
            if (!canPrefetch(link)) return;
            if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            window.requestAnimationFrame(startProgress);
        });

        var warmImportantLinks = function () {
            var links = Array.from(document.querySelectorAll('.navbar a[href], .mobile-bottom-nav a[href], .mobile-quick-links a[href]'))
                .filter(function (link) { return canPrefetch(link); });

            links.forEach(function (link, index) {
                setTimeout(function () {
                    prefetchUrl(link.getAttribute('href'));
                }, 120 * index);
            });
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(warmImportantLinks, { timeout: 1200 });
        } else {
            setTimeout(warmImportantLinks, 500);
        }

        window.addEventListener('pageshow', finishProgress);
    }

    function setupToastAndAlerts() {
        if (!window.Swal) return;

        var toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: function (toastEl) {
                toastEl.addEventListener('mouseenter', Swal.stopTimer);
                toastEl.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        var cfg = window.landingLayoutConfig || {};

        if (cfg.success) {
            toast.fire({
                icon: 'success',
                title: cfg.success,
            });
        }

        if (cfg.error) {
            toast.fire({
                icon: 'error',
                title: cfg.error,
            });
        }

        window.confirmLogout = function () {
            Swal.fire({
                title: cfg.logoutTitle || 'Log out?',
                text: cfg.logoutText || 'You will exit the Kampung Telaga Air visit session.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: cfg.logoutConfirm || 'Yes, Exit',
                cancelButtonText: cfg.logoutCancel || 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) {
                    var form = document.getElementById('logout-form-action');
                    if (form) form.submit();
                }
            });
        };
    }

    function optimizeHeroForMobileAndLazyBackground() {
        var isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        if (isMobile) {
            document.querySelectorAll('.hero-section .carousel').forEach(function (carouselEl) {
                carouselEl.removeAttribute('data-bs-ride');
                carouselEl.setAttribute('data-bs-interval', 'false');

                var items = carouselEl.querySelectorAll('.carousel-item');
                items.forEach(function (item, index) {
                    if (index === 0) {
                        item.classList.add('active');
                        item.style.display = 'block';
                    } else {
                        item.classList.remove('active');
                        item.style.display = 'none';
                    }
                });

                if (window.bootstrap && window.bootstrap.Carousel) {
                    var instance = window.bootstrap.Carousel.getInstance(carouselEl);
                    if (instance) instance.dispose();
                }
            });
        }

        document.querySelectorAll('.hero-lazy').forEach(function (el) {
            var bg = el.getAttribute('data-bg');
            if (!bg) return;

            var img = new Image();
            img.onload = function () {
                el.style.backgroundImage = "url('" + bg + "')";
            };
            img.src = bg;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAOS();
        bindNavbarScrollEffect();
        bindSmoothScroll();
        setupToastAndAlerts();
        optimizeHeroForMobileAndLazyBackground();
        optimizeNavigationTransitions();
    });
})();
