const storeNavLinks = [...document.querySelectorAll('[data-store-nav]')];
const routeNavKey = window.location.pathname.startsWith('/products') ? 'products' : 'home';
const navSections = ['home', 'about', 'contact']
    .map((key) => ({ key, element: document.getElementById(key) }))
    .filter(({ element }) => element);

const setActiveNav = (activeKey) => {
    storeNavLinks.forEach((link) => {
        const isActive = link.dataset.storeNav === activeKey;
        link.classList.toggle('is-active', isActive);

        if (isActive) {
            link.setAttribute('aria-current', activeKey === 'about' || activeKey === 'contact' ? 'location' : 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });
};

const hashNavKey = window.location.hash.slice(1);
setActiveNav(navSections.some(({ key }) => key === hashNavKey) ? hashNavKey : routeNavKey);

const updateActiveNav = () => {
    const headerHeight = document.querySelector('.store-header')?.getBoundingClientRect().height ?? 0;
    const contactSection = navSections.find(({ key }) => key === 'contact');
    const contactBounds = contactSection?.element.getBoundingClientRect();
    const isAtPageBottom = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
    const contactIsVisible = contactBounds
        && contactBounds.top < window.innerHeight
        && contactBounds.bottom > headerHeight;

    if (contactIsVisible && (isAtPageBottom || window.location.hash === '#contact')) {
        setActiveNav('contact');
        return;
    }

    const activationLine = Math.min(
        Math.max(headerHeight + 12, window.innerHeight * 0.12),
        window.innerHeight * 0.45,
    );
    const activeSection = navSections
        .map(({ key, element }) => {
            const bounds = element.getBoundingClientRect();

            return { key, top: bounds.top, bottom: bounds.bottom };
        })
        .filter(({ top, bottom }) => top <= activationLine && bottom > activationLine)
        .sort((first, second) => second.top - first.top)[0];

    setActiveNav(activeSection?.key ?? routeNavKey);
};

let navUpdateScheduled = false;
const scheduleActiveNavUpdate = () => {
    if (navUpdateScheduled) {
        return;
    }

    navUpdateScheduled = true;
    window.requestAnimationFrame(() => {
        navUpdateScheduled = false;
        updateActiveNav();
    });
};

window.addEventListener('scroll', scheduleActiveNavUpdate, { passive: true });
window.addEventListener('resize', scheduleActiveNavUpdate);
window.addEventListener('hashchange', scheduleActiveNavUpdate);
updateActiveNav();

const storeMenuButton = document.querySelector('[data-store-menu-button]');
const storeMobileMenu = document.querySelector('[data-store-menu]');

storeMenuButton?.addEventListener('click', () => {
    const isOpen = storeMenuButton.getAttribute('aria-expanded') === 'true';
    storeMenuButton.setAttribute('aria-expanded', String(!isOpen));
    storeMobileMenu?.classList.toggle('hidden', isOpen);
});

storeMobileMenu?.querySelectorAll('[data-store-nav]').forEach((link) => {
    link.addEventListener('click', () => {
        storeMenuButton?.setAttribute('aria-expanded', 'false');
        storeMobileMenu.classList.add('hidden');
    });
});

document.querySelectorAll('[data-store-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-store-carousel-track]');
    const slides = [...carousel.querySelectorAll('[data-store-carousel-slide]')];
    const dots = [...carousel.querySelectorAll('[data-store-carousel-dot]')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!track || slides.length < 2) {
        return;
    }

    let activeIndex = 0;
    let autoplayTimer;
    let touchStartX = null;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${activeIndex * 100}%)`;

        slides.forEach((slide, slideIndex) => {
            slide.setAttribute('aria-hidden', String(slideIndex !== activeIndex));
        });

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;
            dot.setAttribute('aria-current', String(isActive));
            dot.classList.toggle('bg-amber-400', isActive);
            dot.classList.toggle('bg-white/40', !isActive);
        });
    };

    const stopAutoplay = () => window.clearInterval(autoplayTimer);
    const startAutoplay = () => {
        stopAutoplay();

        if (!reducedMotion && !document.hidden) {
            autoplayTimer = window.setInterval(() => showSlide(activeIndex + 1), 2500);
        }
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.storeCarouselDot));
            startAutoplay();
        });
    });

    carousel.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX ?? null;
    }, { passive: true });

    carousel.addEventListener('touchend', (event) => {
        if (touchStartX === null) {
            return;
        }

        const touchEndX = event.changedTouches[0]?.clientX ?? touchStartX;
        const distance = touchEndX - touchStartX;

        if (Math.abs(distance) > 45) {
            showSlide(activeIndex + (distance < 0 ? 1 : -1));
            startAutoplay();
        }

        touchStartX = null;
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoplay();
        } else {
            startAutoplay();
        }
    });

    showSlide(0);
    startAutoplay();
});

document.querySelectorAll('[data-testimonial-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-testimonial-track]');
    const slides = [...carousel.querySelectorAll('.store-testimonial-slide')];
    const dotsContainer = carousel.querySelector('[data-testimonial-dots]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!track || slides.length < 2 || !dotsContainer) {
        return;
    }

    let activeIndex = 0;
    let autoplayTimer;
    let touchStartX = null;

    const getVisibleSlides = () => {
        const slideWidth = slides[0].getBoundingClientRect().width;
        const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0;
        return Math.max(1, Math.floor((carousel.clientWidth + gap) / (slideWidth + gap)));
    };

    const getLastPage = () => Math.max(0, Math.ceil(slides.length / getVisibleSlides()) - 1);

    const renderDots = () => {
        const dotCount = getLastPage() + 1;
        dotsContainer.replaceChildren(...Array.from({ length: dotCount }, (_, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.dataset.testimonialDot = String(index);
            dot.setAttribute('aria-label', `Tampilkan posisi testimoni ${index + 1}`);
            dot.className = 'h-2.5 w-2.5 rounded-full bg-slate-300 transition hover:bg-amber-400';

            return dot;
        }));

        dotsContainer.querySelectorAll('[data-testimonial-dot]').forEach((dot) => {
            dot.addEventListener('click', () => {
                showSlide(Number(dot.dataset.testimonialDot));
                startAutoplay();
            });
        });
    };

    const showSlide = (index) => {
        activeIndex = Math.min(Math.max(index, 0), getLastPage());
        const slideWidth = slides[0].getBoundingClientRect().width;
        const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0;
        const firstVisibleSlide = activeIndex * getVisibleSlides();
        track.style.transform = `translateX(-${firstVisibleSlide * (slideWidth + gap)}px)`;

        slides.forEach((slide, slideIndex) => {
            const isVisible = slideIndex >= firstVisibleSlide && slideIndex < firstVisibleSlide + getVisibleSlides();
            slide.setAttribute('aria-hidden', String(!isVisible));
        });

        dotsContainer.querySelectorAll('[data-testimonial-dot]').forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;
            dot.setAttribute('aria-current', String(isActive));
            dot.classList.toggle('bg-amber-500', isActive);
            dot.classList.toggle('bg-slate-300', !isActive);
        });
    };

    const stopAutoplay = () => window.clearInterval(autoplayTimer);
    const startAutoplay = () => {
        stopAutoplay();

        if (!reducedMotion && !document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
            autoplayTimer = window.setInterval(() => {
                showSlide(activeIndex >= getLastPage() ? 0 : activeIndex + 1);
            }, 4000);
        }
    };

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            startAutoplay();
        }
    });

    carousel.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX ?? null;
    }, { passive: true });

    carousel.addEventListener('touchend', (event) => {
        if (touchStartX === null) {
            return;
        }

        const touchEndX = event.changedTouches[0]?.clientX ?? touchStartX;
        const distance = touchEndX - touchStartX;

        if (Math.abs(distance) > 45) {
            const nextIndex = activeIndex + (distance < 0 ? 1 : -1);
            showSlide(nextIndex > getLastPage() ? 0 : nextIndex);
            startAutoplay();
        }

        touchStartX = null;
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoplay();
        } else {
            startAutoplay();
        }
    });

    window.addEventListener('resize', () => {
        renderDots();
        showSlide(activeIndex);
        startAutoplay();
    });

    renderDots();
    showSlide(0);
    startAutoplay();
});
