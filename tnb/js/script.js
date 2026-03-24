// TNB Script JS — Performance Optimized
console.log('TNB script loaded');

// Utility: throttle function to limit event firing
function _throttle(fn, wait) {
    let last = 0;
    return function(...args) {
        const now = Date.now();
        if (now - last >= wait) {
            last = now;
            fn.apply(this, args);
        }
    };
}

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // Initialize i18n Language System
    // ----------------------------------------------------
    if (window.tnbLang && window.tnbLang._initialized) {
        console.log('TNB i18n system ready');
    } else {
        console.warn('TNB i18n system not initialized');
    }

    // ----------------------------------------------------
    // TNB Home Slider — 7-Slide Carousel (Prev/Next/Dots/Autoplay/Keyboard)
    // Guard: ทำงานเฉพาะหน้าที่มี #tnbHomeSlider เท่านั้น
    // ----------------------------------------------------
    const tnbSliderSection = document.getElementById('tnbHomeSlider');
    if (tnbSliderSection) {
        const slides    = Array.from(tnbSliderSection.querySelectorAll('.tnb-slide'));
        const dots      = Array.from(tnbSliderSection.querySelectorAll('.tnb-dot'));
        const btnPrev   = document.getElementById('tnbSliderPrev');
        const btnNext   = document.getElementById('tnbSliderNext');
        const TOTAL     = slides.length;
        let current     = 0;
        let autoTimer   = null;
        const AUTO_MS   = 8000;

        function goTo(index) {
            index = ((index % TOTAL) + TOTAL) % TOTAL;
            slides[current].classList.remove('tnb-slide--active');
            dots[current].classList.remove('tnb-dot--active');
            current = index;
            slides[current].classList.add('tnb-slide--active');
            dots[current].classList.add('tnb-dot--active');
        }

        function startAuto() {
            stopAuto();
            autoTimer = setInterval(() => goTo(current + 1), AUTO_MS);
        }

        function stopAuto() {
            if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
        }

        if (btnPrev) btnPrev.addEventListener('click', () => { goTo(current - 1); startAuto(); });
        if (btnNext) btnNext.addEventListener('click', () => { goTo(current + 1); startAuto(); });

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                goTo(parseInt(dot.getAttribute('data-index'), 10));
                startAuto();
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft')  { goTo(current - 1); startAuto(); }
            if (e.key === 'ArrowRight') { goTo(current + 1); startAuto(); }
        });

        tnbSliderSection.addEventListener('mouseenter', stopAuto);
        tnbSliderSection.addEventListener('mouseleave', startAuto);
        startAuto();
    }

    // ----------------------------------------------------
    // ระบบ Infinite Loop สำหรับ Logo Carousel
    // ----------------------------------------------------
    const carouselTrack = document.querySelector('.carousel-track');
    if (carouselTrack) {
        const items = carouselTrack.querySelectorAll('.carousel-item');
        items.forEach(item => {
            const clone = item.cloneNode(true);
            carouselTrack.appendChild(clone);
        });
    }

    // ----------------------------------------------------
    // GSAP & Lenis — Optimized with shorter durations & ease-out
    // ----------------------------------------------------
    if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        
        const lenis = new Lenis();
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => { lenis.raf(time * 1000); });
        gsap.ticker.lagSmoothing(0);

        if (document.querySelector('.image-motion')) {
            gsap.set('.image-motion', { transform: 'rotatex(90deg)' });
            gsap.to('.image-motion', {
                transform: 'rotatex(0deg)',
                scrollTrigger: { trigger: '.section2', start: 'top bottom', end: 'bottom top', scrub: true },
            });
        }

        if (document.querySelector('.section3')) {
            gsap.fromTo('.title', { opacity: 0, y: 30 }, {
                opacity: 1, y: 0, duration: 0.5, ease: 'power2.out',
                scrollTrigger: { trigger: '.section3', start: 'top 80%', toggleActions: 'play none none reverse' },
            });
            gsap.fromTo('.subtitle', { opacity: 0, y: 20 }, {
                opacity: 1, y: 0, duration: 0.5, delay: 0.15, ease: 'power2.out',
                scrollTrigger: { trigger: '.section3', start: 'top 80%', toggleActions: 'play none none reverse' },
            });
        }

        if (document.querySelector('.text-content') && typeof SplitText !== 'undefined') {
            const text = new SplitText('.text', { types: 'lines', mask: 'lines' });
            gsap.fromTo(text.lines, { opacity: 0, y: 20 }, {
                opacity: 1, y: 0, stagger: 0.1, duration: 0.5, ease: 'power2.out',
                scrollTrigger: { trigger: '.text-content', start: 'top 80%', toggleActions: 'play none none reverse' },
            });
        }

        if (document.querySelector('.features')) {
            gsap.fromTo('.feature', { opacity: 0, y: 30 }, {
                opacity: 1, y: 0, stagger: 0.1, duration: 0.5, ease: 'power2.out',
                scrollTrigger: { trigger: '.features', start: 'top 80%', toggleActions: 'play none none reverse' },
            });
        }

        // === 8.1 PARTNERS SECTION: สี่เหลี่ยมเลื่อนลงจากบน ===
        const partnersSec = document.querySelector('.section-partners');
        if (partnersSec) {
            gsap.fromTo(partnersSec,
                { '--deco-y': '-100%' },
                {
                    '--deco-y': '0%',
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: '.section-partners', start: 'top 85%', toggleActions: 'play none none reverse' },
                }
            );
        }

        // === 8.2 PRODUCTS SECTION: สี่เหลี่ยมเลื่อนลงจากบน ===
        const productsSec = document.querySelector('.section-products');
        if (productsSec) {
            gsap.fromTo(productsSec,
                { '--deco-y': '-100%' },
                {
                    '--deco-y': '0%',
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: '.section-products', start: 'top 85%', toggleActions: 'play none none reverse' },
                }
            );
        }
    }
});

// ----------------------------------------------------
// ระบบฟีเจอร์เลื่อนหน้าจอ (Scrolling Feature Logic for development.php)
// Uses IntersectionObserver instead of continuous scroll for performance
// ----------------------------------------------------
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        if ($('.development-scroll-container').length === 0) return;

    const images = {
        'dev-section1': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/0st9yhngses-benjamin-child.jpg',
        'dev-section2': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/2fgvaqx-fxs-oskar-krawczyk.jpg',
        'dev-section3': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/Great_Wave_off_Kanagawa2_cr.jpg'
    };

    const $sections = $('.scroll-text-container section');
    const $stickyImg = $('#sticky-dev-img');
    let currentSection = '';

    // Use IntersectionObserver for better performance
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && entry.intersectionRatio > 0.3) {
                    const el = $(entry.target);
                    const id = el.attr('id');
                    $sections.removeClass('active-text');
                    el.addClass('active-text');
                    if (currentSection !== id) {
                        currentSection = id;
                        $stickyImg.addClass('fade-img');
                        setTimeout(function() {
                            var dynamicImgUrl = $('#' + id).find('.section-dynamic-image').attr('src');
                            var finalImgUrl = dynamicImgUrl ? dynamicImgUrl : images[id];
                            if (finalImgUrl) $stickyImg.attr('src', finalImgUrl);
                            $stickyImg.removeClass('fade-img');
                        }, 300);
                    }
                }
            });
        }, { threshold: [0.3, 0.5], rootMargin: '-20% 0px -20% 0px' });

        $sections.each(function() { observer.observe(this); });
    } else {
        // Fallback with throttled scroll
        const onScroll = _throttle(function() {
            var scrollPosition = $(window).scrollTop() + $(window).height() / 2;
            $sections.each(function() {
                var $this = $(this);
                var sectionTop = $this.offset().top;
                var sectionBottom = sectionTop + $this.outerHeight();
                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    var id = $this.attr('id');
                    $sections.removeClass('active-text');
                    $this.addClass('active-text');
                    if (currentSection !== id) {
                        currentSection = id;
                        $stickyImg.addClass('fade-img');
                        setTimeout(function() {
                            var dynamicImgUrl = $('#' + id).find('.section-dynamic-image').attr('src');
                            var finalImgUrl = dynamicImgUrl ? dynamicImgUrl : images[id];
                            if (finalImgUrl) $stickyImg.attr('src', finalImgUrl);
                            $stickyImg.removeClass('fade-img');
                        }, 300);
                    }
                }
            });
        }, 100);
        $(window).on('scroll resize', onScroll);
        setTimeout(onScroll, 100);
    }
});
}

// ----------------------------------------------------
// Expanding Cards Logic (from index.php)
// ----------------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    // Only run if the relevant element exists on the page
    if (document.querySelector('.Block_Expanding_Cards') === null) return;

    var	doc = document ,
    elemList = Construct_SubElement_List( doc.body ) ,
    len = elemList.length ,
    win = window ,
    i ,
    _Blocks = [] ,
    Card_Expander ;

    for (  i = 0 ; i < len ; i++  ) {
        var	currElem	= elemList[i] ;
        if (  currElem.className && /^Block_Expanding_Cards$/.test( currElem.className )  ) {
            Card_Expander	= new Card_Expander_Obj( currElem ) ;
        }
        if (  currElem.parentNode && /body/i.test( currElem.parentNode.tagName ) && ( /^Block/.test( currElem.className ) || /^Block/.test( currElem.id ) )  )
            _Blocks.push( currElem ) ;
    }

    function	Card_Expander_Obj   (  ElemObjRef  )  {
        var	ELEM		= ElemObjRef ,
            ElemList	= Construct_SubElement_List( ELEM ) , num = ElemList.length , j , k ;

        this.ElemObj		= ELEM ;
        this._Cards		= [] ;
        this.current_index	= 0 ;

        for (  j = 0 ; j < num ; j++  ) {
            var	CE = ElemList[j] ;
            if ( /^US_City_sB/.test( CE.className ) ) {
                var	subElemList	= Construct_SubElement_List( CE ), n2 = subElemList.length ,
                    h1_EO , shade_EO , content_EO , BG_EO ;

                for ( k = 0 ; k < n2 ; k++ ) {
                    if ( /^card_h1/.test( subElemList[k].className ) )			h1_EO		= subElemList[k] ;
                    else if ( /^card_shade/.test( subElemList[k].className ) )	shade_EO		= subElemList[k] ;
                    else if ( /^card_content/.test( subElemList[k].className ) )	content_EO	= subElemList[k] ;
                    else if ( /^card_BG/.test( subElemList[k].className ) )		BG_EO		= subElemList[k] ;
                }

                this._Cards.push( {"EO": CE, "h1_EO": h1_EO, "shade_EO": shade_EO, "content_EO": content_EO, "BG_EO": BG_EO} ) ;

                CE.onclick = function() {
                    if ( this.className.indexOf("_active") > -1 )	return ;
                    Card_Expander._Toggle( parseInt( this.getAttribute("data-card-index") ) ) ;
                } ;
            }
        }

        this._Toggle = function ( new_card_index ) {
            this._Cards[ this.current_index ].EO.className			= "US_City_sB" ;
            this._Cards[ this.current_index ].h1_EO.className		= "card_h1" ;
            this._Cards[ this.current_index ].shade_EO.className		= "card_shade" ;
            this._Cards[ this.current_index ].content_EO.className	= "card_content" ;
            this._Cards[ this.current_index ].BG_EO.className		= "card_BG" ;

            this._Cards[ new_card_index ].EO.className			+= " _active" ;
            this._Cards[ new_card_index ].h1_EO.className		+= " _active" ;
            this._Cards[ new_card_index ].shade_EO.className	+= " _off" ;
            this._Cards[ new_card_index ].content_EO.className	+= " _on" ;
            this._Cards[ new_card_index ].BG_EO.className		+= " _active" ;

            this.current_index	= new_card_index ;
        } ;
    }

    function	Construct_SubElement_List ( ElemObjRef ) {
        var	_ELEM		= ElemObjRef ,
            tmpList		= _ELEM.getElementsByTagName("*") , tmpLen = tmpList.length , k ,
            ELEM_LIST 	= [] ;
        for (  k = 0 ; k < tmpLen ; k++  )		ELEM_LIST.push( tmpList[k] ) ;
        return		ELEM_LIST ;
    }
});





// =========================================
// Login Page Specific Script
// =========================================
document.addEventListener('DOMContentLoaded', () => {
    const sign_in_btn = document.querySelector("#sign-in-btn");
    const sign_up_btn = document.querySelector("#sign-up-btn");
    const login_container = document.querySelector(".login-container");

    if (sign_up_btn && sign_in_btn && login_container) {
        sign_up_btn.addEventListener("click", () => login_container.classList.add("sign-up-mode"));
        sign_in_btn.addEventListener("click", () => login_container.classList.remove("sign-up-mode"));
    }
});

// =========================================
// Product Page Specific Script (Moved from product.php)
// =========================================

let currentLimit = 8;
let currentCategory = 'all';

function filterCategory(category, event) {
    if (event) {
        event.preventDefault();
        // Update active class on menu
        document.querySelectorAll('.category-menu a').forEach(link => {
            link.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    currentCategory = category;
    currentLimit = 8; // Reset limit when filter changes
    updateGrid(false);
}

function updateGrid(isLoadMore = false) {
    const items = document.querySelectorAll('.product-grid-item');
    let delay = 0;
    let visibleCount = 0;
    let totalMatch = 0;

    items.forEach(item => {
        if (!isLoadMore) {
            item.style.animation = 'none'; // reset animation
            item.offsetHeight; // trigger reflow
        }

        const isMatch = (currentCategory === 'all' || item.getAttribute('data-category') === currentCategory);

        if (isMatch) {
            totalMatch++;
            if (visibleCount < currentLimit) {
                if (!isLoadMore || item.style.display !== 'flex') {
                    item.style.display = 'flex';
                    item.style.animation = `fadeIn 0.4s ease ${delay}s forwards`;
                    delay += 0.05; // stagger effect
                }
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.style.animation = 'none';
            }
        } else {
            item.style.display = 'none';
            item.style.animation = 'none';
        }
    });

    // Update Load More button visibility
    const loadMoreContainer = document.querySelector('.load-more-container');
    if (loadMoreContainer) {
        if (totalMatch > currentLimit) {
            loadMoreContainer.style.display = 'block'; // Or 'text-align: center' parent handles it
        } else {
            loadMoreContainer.style.display = 'none';
        }
    }
}

// Initialize default view
document.addEventListener('DOMContentLoaded', () => {
    filterCategory('all', null);

    const loadMoreBtn = document.querySelector('.load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentLimit += 8;
            updateGrid(true);
        });
    }
});


// =========================================
// Menubar Component (component/menubar.php)
// =========================================
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('header')) return;

    const header = document.getElementById('header');
    const navMenu = document.getElementById('navMenu');
    const navToggle = document.getElementById('navToggle');
    const navClose = document.getElementById('navClose');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    const navLinks = document.querySelectorAll('.header .nav-list .nav-item > a.nav-link');

    /* ===== Scroll Effect: Header compact — throttled for performance ===== */
    let lastScroll = 0;
    window.addEventListener('scroll', _throttle(() => {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 40) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    }, 50), { passive: true });

    /* ===== ฟังก์ชันเปิดเมนู ===== */
    function openMenu() {
        navMenu.classList.add('show-menu');
        navToggle.classList.add('active');
        document.body.style.overflow = 'hidden'; /* ล็อค scroll เวลาเปิดเมนู */
    }

    /* ===== ฟังก์ชันปิดเมนู ===== */
    function closeMenu() {
        navMenu.classList.remove('show-menu');
        navToggle.classList.remove('active');
        document.body.style.overflow = ''; /* คืน scroll */
    }

    /* ===== Hamburger Toggle — เปิด/ปิดเมนู ===== */
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            if (navMenu.classList.contains('show-menu')) {
                closeMenu();
            } else {
                openMenu();
            }
        });
    }

    /* ===== ปุ่ม ✕ ปิดเมนู ===== */
    if (navClose) {
        navClose.addEventListener('click', () => {
            closeMenu();
        });
    }

    /* ===== คลิกลิงก์เมนูแล้วปิดเมนูอัตโนมัติ (มือถือ) ===== */
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                closeMenu();
            }
        });
    });

    /* ===== Resize: ปิดเมนูอัตโนมัติเมื่อกลับเป็น Desktop ===== */
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            closeMenu();
        }
    });

    /* ===== Mobile Dropdown Toggle ===== */
    dropdownItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        if (link) {
            link.addEventListener('click', (e) => {
                if (window.innerWidth > 1024) return;
                e.preventDefault();
                item.classList.toggle('active');
                if (item.classList.contains('active')) {
                    link.style.color = 'var(--primary-color)';
                } else {
                    link.style.color = 'var(--text-color)';
                }
            });
        }
    });
});

// =========================================
// Scroll-triggered animations for feature cards
// =========================================

document.addEventListener('DOMContentLoaded', function() {
    // Feature Cards Animation
    const featureCards = document.querySelectorAll('.feat-cards__item.fc-hidden');
    
    if (featureCards.length > 0) {
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('fc-hidden');
                    entry.target.classList.add('fc-visible');
                }
            });
        }, observerOptions);
        
        featureCards.forEach(card => {
            cardObserver.observe(card);
        });
    }

    // Modal System for Technology Page
    const modalItems = document.querySelectorAll('.card-ui-item');
    const modalOverlays = document.querySelectorAll('.modal-ui-overlay');
    const modalCloseButtons = document.querySelectorAll('.modal-ui-close');

    // Open modal
    modalItems.forEach(item => {
        item.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close modal
    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close on X button
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-ui-overlay');
            closeModal(modal);
        });
    });

    // Close on overlay click
    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-ui-overlay.active');
            if (activeModal) {
                closeModal(activeModal);
            }
        }
    });

    // Branch Cards Animation
    const branchCards = document.querySelectorAll('.branch-card');
    
    if (branchCards.length > 0) {
        const branchObserverOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const branchObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, branchObserverOptions);
        
        branchCards.forEach(card => {
            branchObserver.observe(card);
        });
    }
});
