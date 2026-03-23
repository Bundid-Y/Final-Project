// Koch Script JS
if (window.kochScriptInitialized) {
    console.log('Koch script already matched; skipping double init.');
} else {
window.kochScriptInitialized = true;
console.log('Koch script loaded');

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // ตัวแปรสำหรับจัดการ Slider
    // ----------------------------------------------------
    const slides = document.querySelectorAll('.slide-item');
    const sliderWrapper = document.getElementById('sliderWrapper');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    let current = 0;
    let autoSlideInterval;
    const totalSlides = slides.length;

    // ----------------------------------------------------
    // ฟังก์ชันคำนวณขนาด (Responsive)
    // ----------------------------------------------------
    function getDimensions() {
        const w = window.innerWidth;
        if (w >= 1200) {
            return { centerW: 900, centerH: 600, sideW: 500, sideH: 450, overlapPct: 0.15 };
        } else if (w >= 992) {
            return { centerW: 700, centerH: 500, sideW: 400, sideH: 380, overlapPct: 0.12 };
        } else if (w >= 600) {
            return { centerW: 450, centerH: 350, sideW: 280, sideH: 260, overlapPct: 0.10 };
        } else {
            return { centerW: 280, centerH: 220, sideW: 160, sideH: 140, overlapPct: 0.08 };
        }
    }

    // ----------------------------------------------------
    // ฟังก์ชันอัปเดตการแสดงผล Slider
    // ----------------------------------------------------
    function updateSlider() {
        const dim = getDimensions();
        
        // ปรับความสูงของ Container หลักให้พอดีกับภาพตรงกลาง
        if(sliderWrapper) {
             sliderWrapper.style.height = (dim.centerH + 40) + "px"; // +40 เผื่อเงา
        }

        const offset = (dim.centerW / 2) + (dim.sideW / 2) - (dim.sideW * dim.overlapPct);

        slides.forEach((slide, index) => {
            // Reset click event
            slide.onclick = null;

            // คำนวณ Index สำหรับ Loop
            const prevIndex = (current - 1 + totalSlides) % totalSlides;
            const nextIndex = (current + 1) % totalSlides;

            if (index === current) {
                // ภาพตรงกลาง (Active)
                setStyle(slide, {
                    width: dim.centerW,
                    height: dim.centerH,
                    zIndex: 10,
                    opacity: 1,
                    left: '50%',
                    transform: 'translate(-50%, -50%) scale(1)',
                    pointerEvents: 'none' // ไม่ให้กดตัวเองซ้ำ
                });
            } else if (index === prevIndex) {
                // ภาพซ้าย
                setStyle(slide, {
                    width: dim.sideW,
                    height: dim.sideH,
                    zIndex: 5,
                    opacity: 0.8,
                    left: `calc(50% - ${offset}px)`,
                    transform: 'translate(-50%, -50%) scale(1)',
                    pointerEvents: 'auto'
                });
                slide.onclick = () => { moveSlide(-1); };
            } else if (index === nextIndex) {
                // ภาพขวา
                setStyle(slide, {
                    width: dim.sideW,
                    height: dim.sideH,
                    zIndex: 5,
                    opacity: 0.8,
                    left: `calc(50% + ${offset}px)`,
                    transform: 'translate(-50%, -50%) scale(1)',
                    pointerEvents: 'auto'
                });
                slide.onclick = () => { moveSlide(1); };
            } else {
                // ภาพที่ซ่อนอยู่
                setStyle(slide, {
                    width: dim.sideW,
                    height: dim.sideH,
                    zIndex: 0,
                    opacity: 0,
                    left: '50%',
                    transform: 'translate(-50%, -50%) scale(0.5)',
                    pointerEvents: 'none'
                });
            }
        });
    }

    // ฟังก์ชันช่วยกำหนด Style
    function setStyle(element, styles) {
        element.style.width = styles.width + "px";
        element.style.height = styles.height + "px";
        element.style.zIndex = styles.zIndex;
        element.style.opacity = styles.opacity;
        element.style.left = styles.left;
        element.style.top = "50%"; // Center Vertically
        element.style.transform = styles.transform;
        element.style.pointerEvents = styles.pointerEvents;
    }

    // ----------------------------------------------------
    // ฟังก์ชันเลื่อนภาพ
    // ----------------------------------------------------
    function moveSlide(direction) {
        current = (current + direction + totalSlides) % totalSlides;
        updateSlider();
        resetAutoSlide();
    }

    // ----------------------------------------------------
    // ระบบเลื่อนอัตโนมัติ (Auto Slide)
    // ----------------------------------------------------
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            moveSlide(1);
        }, 8000);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    // ----------------------------------------------------
    // Event Listeners
    // ----------------------------------------------------
    if(prevBtn) prevBtn.addEventListener('click', () => moveSlide(-1));
    if(nextBtn) nextBtn.addEventListener('click', () => moveSlide(1));

    if(sliderWrapper) {
        // Pause-on-hover behavior disabled
        // sliderWrapper.addEventListener('mouseenter', stopAutoSlide);
        // sliderWrapper.addEventListener('mouseleave', startAutoSlide);
    }
    
    // จัดการเมื่อเปลี่ยนขนาดหน้าจอ (Resize)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateSlider, 100);
    });

    // เริ่มต้นทำงาน
    if(slides.length > 0) {
        updateSlider();
        startAutoSlide();
    }

    // ----------------------------------------------------
    // ระบบ Infinite Loop สำหรับ Logo Carousel
    // — Duplicate items เพื่อให้ loop ไม่มีช่องว่าง
    // ----------------------------------------------------
    const carouselTrack = document.querySelector('.carousel-track');
    if (carouselTrack) {
        const items = carouselTrack.querySelectorAll('.carousel-item');
        // Clone ทุก item แล้วเพิ่มต่อท้าย เพื่อให้ CSS translateX(-50%) ทำงานต่อเนื่อง
        items.forEach(item => {
            const clone = item.cloneNode(true);
            carouselTrack.appendChild(clone);
        });
    }

    // ----------------------------------------------------
    // GSAP & Lenis Animations for Quotation Section การ์ดสไลด์ logo ลูกค้า
    // ----------------------------------------------------
    if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        // Note: SplitText is a GSAP Club plugin, ensure it's loaded if used.
        
        const lenis = new Lenis();
        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => {
            lenis.raf(time * 1000);
        });
        gsap.ticker.lagSmoothing(0);

        if (document.querySelector('.image-motion')) {
            gsap.set('.image-motion', {
                transform: 'rotatex(90deg)',
            });

            gsap.to('.image-motion', {
                transform: 'rotatex(0deg)',
                scrollTrigger: {
                    trigger: '.section2',
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: true,
                    markers: false,
                },
            });
        }

        if (document.querySelector('.section3')) {
            gsap.fromTo('.title', {
                opacity: 0,
                y: 50,
            }, {
                opacity: 1,
                y: 0,
                duration: 1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.section3',
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                },
            });

            gsap.fromTo('.subtitle', {
                opacity: 0,
                y: 30,
            }, {
                opacity: 1,
                y: 0,
                duration: 0.8,
                delay: 0.3,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.section3',
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                },
            });
        }

        if (document.querySelector('.text-content') && typeof SplitText !== 'undefined') {
            const text = new SplitText('.text', {
                types: 'lines',
                mask: 'lines',
            });

            gsap.fromTo(text.lines, {
                opacity: 0,
                y: 30,
            }, {
                opacity: 1,
                y: 0,
                stagger: 0.2,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.text-content',
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                },
            });
        }

        if (document.querySelector('.features')) {
            gsap.fromTo('.feature', {
                opacity: 0,
                y: 50,
                scale: 0.9,
            }, {
                opacity: 1,
                y: 0,
                scale: 1,
                stagger: 0.2,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: '.features',
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse',
                },
            });
        }

        // ----------------------------------------------------
        // GSAP Animations — สี่เหลี่ยมแดงตกแต่ง (Decorative Red Shapes)
        // แต่ละ Section มีอนิเมชั่นไม่ซ้ำกัน
        // ----------------------------------------------------

        // === 8.1 PARTNERS SECTION: สี่เหลี่ยมเลื่อนลงจากบน ===
        const partnersSec = document.querySelector('.section-partners');
        if (partnersSec) {
            gsap.fromTo(partnersSec,
                { '--deco-y': '-100%' },
                {
                    '--deco-y': '0%',
                    duration: 0.8,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: '.section-partners',
                        start: 'top 85%',
                        end: 'bottom 20%',
                        toggleActions: 'play none none reverse',
                    },
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
                    duration: 1.2,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: '.section-products',
                        start: 'top 85%',
                        end: 'bottom 20%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );
        }

        // === 8.3 SLIDER SECTION: สี่เหลี่ยมเลื่อนลงจากบนแบบ Parallax ===
        const sliderSec = document.querySelector('.section-slider');
        if (sliderSec) {
            // Layer สีแดง (มาเร็วหน่อย)
            gsap.fromTo(sliderSec,
                { '--deco-y': '-100%' },
                {
                    '--deco-y': '0%',
                    duration: 1.2,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: '.section-slider',
                        start: 'top 85%',
                        end: 'bottom 20%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );

            // Layer สีน้ำเงิน (มาช้ากว่าเล็กน้อย สร้างมิติ)
            gsap.fromTo(sliderSec,
                { '--deco-y2': '-100%' },
                {
                    '--deco-y2': '0%',
                    duration: 1.6, /* ช้ากว่าสีแดง */
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: '.section-slider',
                        start: 'top 85%',
                        end: 'bottom 20%',
                        toggleActions: 'play none none reverse',
                    },
                }
            );
        }
    }
});

// ----------------------------------------------------
// ระบบฟีเจอร์เลื่อนหน้าจอ (Scrolling Feature Logic for development.php)
// ----------------------------------------------------
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        // ทำงานเมื่อมีคลาส .development-scroll-container อยู่ในหน้าเท่านั้น
        if ($('.development-scroll-container').length === 0) return;

    // กำหนด URL ของรูปภาพให้ตรงกับ ID ของแต่ละส่วนข้อความ
    const images = {
        'dev-section1': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/0st9yhngses-benjamin-child.jpg',
        'dev-section2': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/2fgvaqx-fxs-oskar-krawczyk.jpg',
        'dev-section3': 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/495197/Great_Wave_off_Kanagawa2_cr.jpg'
    };

    const $sections = $('.scroll-text-container section');
    const $stickyImg = $('#sticky-dev-img');
    let currentSection = '';

    // ฟังก์ชันทำงานเวลาเลื่อนหน้าจอ
    function onScroll() {
        // หาจุดกึ่งกลางของหน้าจอเพื่อให้เปลี่ยนรูปเมื่ออ่านถึงตรงกลาง
        var scrollPosition = $(window).scrollTop() + $(window).height() / 2;

        $sections.each(function() {
            var $this = $(this);
            var sectionTop = $this.offset().top;
            var sectionBottom = sectionTop + $this.outerHeight();

            // ตรวจสอบว่าเลื่อนมาจุดที่แสดงส่วนข้อความนี้หรือยัง
            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                var id = $this.attr('id');
                
                // เพิ่มคลาส active ให้แสดงข้อความให้ชัดเจนขึ้น
                $sections.removeClass('active-text');
                $this.addClass('active-text');

                // ถ้าเลื่อนเปลี่ยนส่วนข้อความ ให้แสดงอนิเมชั่นเปลี่ยนรูป
                if (currentSection !== id) {
                    currentSection = id;
                    
                    // เฟดภาพออก (เพิ่ม class fade-img)
                    $stickyImg.addClass('fade-img');
                    
                    // หลังจาก 400ms ให้เปลี่ยนรูปและเอาคลาส fade-img ออกเพื่อแสดงรูปใหม่
                    setTimeout(function() {
                        // ดึงรูปภาพจากแท็ก img ที่ซ่อนอยู่ใน HTML ก่อน (ถ้ามี)
                        var dynamicImgUrl = $('#' + id).find('.section-dynamic-image').attr('src');
                        var finalImgUrl = dynamicImgUrl ? dynamicImgUrl : images[id];
                        
                        if (finalImgUrl) {
                            $stickyImg.attr('src', finalImgUrl);
                        }
                        $stickyImg.removeClass('fade-img');
                    }, 400); // 400ms ให้ตรงกับ transition ใน CSS
                }
            }
        });
    }

    // เรียกทำงานตอน scroll และ resize
    $(window).on('scroll resize', onScroll);

    // ทำงานครั้งแรกเพื่อตั้งค่าเริ่มต้น
    setTimeout(onScroll, 100);
});
}

// =========================================
// Login Page Specific Styles & Script
// =========================================

    document.addEventListener('DOMContentLoaded', () => {
      const sign_in_btn = document.querySelector("#sign-in-btn");
      const sign_up_btn = document.querySelector("#sign-up-btn");
      const login_container = document.querySelector(".login-container");

      if (sign_up_btn && sign_in_btn && login_container) {
        sign_up_btn.addEventListener("click", () => {
          login_container.classList.add("sign-up-mode");
        });

        sign_in_btn.addEventListener("click", () => {
          login_container.classList.remove("sign-up-mode");
        });
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

    items.forEach(item => {
        if (!isLoadMore) {
            item.style.animation = 'none'; // reset animation
            item.offsetHeight; // trigger reflow
        }

        const isMatch = (currentCategory === 'all' || item.getAttribute('data-category') === currentCategory);

        if (isMatch) {
            if (visibleCount < currentLimit) {
                if (!isLoadMore || item.style.display !== 'flex') {
                    item.style.display = 'flex';
                    item.style.animation = `productFadeUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) ${delay}s forwards`;
                    delay += 0.08; // stagger effect increased slightly
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
}

// Initialize default view
document.addEventListener('DOMContentLoaded', () => {
    // Check if we log in product page first to prevent errors
    const productGrid = document.querySelector('.product-grid');
    if (!productGrid) return;

    filterCategory('all', null);

    let isUpdating = false;

    window.addEventListener('scroll', () => {
        if (isUpdating) return;
        
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        // Adjust the trigger point to be when the user is near the bottom
        if (scrollTop + clientHeight >= scrollHeight - 350) {
            
            const items = document.querySelectorAll('.product-grid-item');
            let totalMatch = 0;
            items.forEach(item => {
                const isMatch = (currentCategory === 'all' || item.getAttribute('data-category') === currentCategory);
                if (isMatch) totalMatch++;
            });

            if (currentLimit < totalMatch) {
                isUpdating = true;
                currentLimit += 4; // Add 4 items at a time for smooth infinite scrolling
                updateGrid(true);
                
                setTimeout(() => {
                    isUpdating = false;
                }, 400); // 400ms buffer timeout to prevent runaway limit increases
            }
        }
    });
});


// =========================================
// Company Page Overflow Fix (JS fallback)
// Scoped: body.page-company + tablet/mobile only
// =========================================
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.body.classList.contains('page-company')) return;
        function lockHorizontalScroll() {
            if (window.innerWidth <= 1024) {
                document.documentElement.style.overflowX = 'hidden';
                document.body.style.overflowX = 'hidden';
            } else {
                document.documentElement.style.overflowX = '';
                document.body.style.overflowX = '';
            }
        }
        lockHorizontalScroll();
        window.addEventListener('resize', lockHorizontalScroll);
    });
})();

// =========================================
// Menubar Component (component/menubar.php)
// สรุปขอบเขต (Scope) ของ JS เพื่อป้องกันการโหลดซ้อนและแก้ปัญหา Click Events
// =========================================
(function initMenubarScoped() {
    document.addEventListener('DOMContentLoaded', () => {
        // Guard: ทำงานเฉพาะหน้าที่มี menubar เท่านั้น
        const header = document.getElementById('header');
        if (!header) return;
        if (window.menubarInitialized) return;
        window.menubarInitialized = true;

        const navMenu = document.getElementById('navMenu');
        const navToggle = document.getElementById('navToggle');
        const navClose = document.getElementById('navClose');
        // Scope strictly inside #header
        const dropdownItems = header.querySelectorAll('.dropdown-item');
        const navLinks = header.querySelectorAll('.nav-list .nav-item > a.nav-link');

        /* ===== Scroll Effect: Header compact เมื่อ scroll ลง ===== */
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > 40) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            lastScroll = currentScroll;
        }, { passive: true });

        /* ===== ฟังก์ชันเปิดเมนู ===== */
        function openMenu() {
            if(navMenu) navMenu.classList.add('show-menu');
            if(navToggle) navToggle.classList.add('active');
            document.body.style.overflow = 'hidden'; /* ล็อค scroll เวลาเปิดเมนู */
        }

        /* ===== ฟังก์ชันปิดเมนู ===== */
        function closeMenu() {
            if(navMenu) navMenu.classList.remove('show-menu');
            if(navToggle) navToggle.classList.remove('active');
            document.body.style.overflow = ''; /* คืน scroll */
        }

        /* ===== Hamburger Toggle — เปิด/ปิดเมนู ===== */
        if (navToggle) {
            navToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                if (navMenu.classList.contains('show-menu')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        /* ===== ปุ่ม ✕ ปิดเมนู ===== */
        if (navClose) {
            navClose.addEventListener('click', (e) => {
                e.stopPropagation();
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

        /* ===== Mobile Dropdown Toggle (iPad Pro + Touch Fix) ===== */
        // ใช้ matchMedia แทน innerWidth เดิม เพราะ iPad Pro 12.9" อาจ report width ผิดเพี้ยน
        function isMobileView() {
            return window.matchMedia('(max-width: 1024px)').matches;
        }

        dropdownItems.forEach(item => {
            const toggleElement = item.querySelector('.dropdown-toggle');
            if (!toggleElement) return;

            // ฟังก์ชันเปิด/ปิด dropdown — ใช้ร่วมกันระหว่าง click และ touchend
            function handleDropdownToggle(e) {
                if (!isMobileView()) return;
                e.preventDefault();
                e.stopPropagation(); // จำกัดขอบเขต (Scope) ไม่ให้ event ไหลออก

                const isActive = item.classList.contains('active');

                // Accordion: ปิด dropdown อื่นๆ ทั้งหมดก่อน (ป้องกันซ้อนกัน)
                dropdownItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                        const otherToggle = otherItem.querySelector('.dropdown-toggle');
                        if (otherToggle) otherToggle.style.color = '';
                    }
                });

                // Toggle current item
                item.classList.toggle('active', !isActive);
                toggleElement.style.color = !isActive ? 'var(--primary-color)' : '';
            }

            // Bind click (desktop/mouse) และ touchend (iPad Safari) แยกกัน
            toggleElement.addEventListener('click', handleDropdownToggle);

            // iPad Safari Fix: touchend เพิ่มเพื่อป้องกัน click ไม่ทำงานบน iOS
            toggleElement.addEventListener('touchend', function(e) {
                // ป้องกัน click ยิงซ้ำหลัง touchend
                e.preventDefault();
                handleDropdownToggle(e);
            }, { passive: false });
        });

        // ===== IntersectionObserver for Service Animations =====
        const animationObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('anim-visible');
                    // Stop observing once animated so it only happens once
                    observer.unobserve(entry.target); 
                }
            });
        }, {
            threshold: 0.1, // Trigger when 10% of element is visible
            rootMargin: "0px 0px -50px 0px"
        });

        const animatedElements = document.querySelectorAll('.anim-fade-up, .anim-fade-left, .anim-fade-right, .anim-zoom-in, .anim-red-block');
        animatedElements.forEach(el => animationObserver.observe(el));

    });
})();
// =========================================
// Product Carousel — index.php (dev-carousel-root)
// auto-slide ทุก 5 วิ, pause on hover
// =========================================
(function initProductCarousel() {
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.querySelector('.dev-carousel-root');
        if (!root) return;
        const track = root.querySelector('.dev-carousel-track');
        const dotsContainer = root.querySelector('.dev-carousel-dots');
        if (!track || !dotsContainer) return;

        const pages = track.querySelectorAll('.dev-carousel-page');
        const total = pages.length;
        if (total <= 1) return;

        let currentIdx = 0;
        let isHovered = false;
        let timer = null;

        // Build dots
        pages.forEach(function (_, i) {
            const dot = document.createElement('button');
            dot.className = 'dev-carousel-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'สินค้าหน้า ' + (i + 1));
            dot.addEventListener('click', function () { goTo(i); });
            dotsContainer.appendChild(dot);
        });

        function goTo(index) {
            currentIdx = ((index % total) + total) % total;
            track.style.transform = 'translateX(-' + (currentIdx * 100) + '%)';
            root.querySelectorAll('.dev-carousel-dot').forEach(function (d, i) {
                d.classList.toggle('active', i === currentIdx);
            });
        }

        function tick() {
            if (!isHovered) goTo(currentIdx + 1);
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(tick, 5000);
        }

        root.addEventListener('mouseenter', function () { isHovered = true; });
        root.addEventListener('mouseleave', function () { isHovered = false; });

        // Touch swipe support
        var touchStartX = 0;
        root.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });
        root.addEventListener('touchend', function (e) {
            var dx = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(dx) > 40) {
                goTo(dx < 0 ? currentIdx + 1 : currentIdx - 1);
                startTimer();
            }
        }, { passive: true });

        startTimer();
    });
})();

// =========================================
// Technology Pagination — technology.php
// scroll-jack: wheel/keyboard → change panel
// =========================================
(function initTechPager() {
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('page-technology')) return;

        const outer = document.querySelector('.tech-pager-outer');
        const track = document.querySelector('.tech-pager-track');
        const dotsEl = document.querySelector('.tech-pager-dots');
        if (!outer || !track || !dotsEl) return;

        // Mobile: skip (CSS already disables pager layout)
        if (window.innerWidth <= 767) return;

        const panels = track.querySelectorAll('.tech-panel');
        const total = panels.length;
        if (total === 0) return;

        let current = 0;
        let isAnimating = false;
        const DURATION = 650;

        // Set track total height = panels * panel-height
        track.style.height = (total * 100) + '%';
        panels.forEach(function (panel) {
            panel.style.height = (100 / total) + '%';
        });

        // Build dots
        panels.forEach(function (_, i) {
            const dot = document.createElement('button');
            dot.className = 'tech-pager-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Section ' + (i + 1));
            dot.addEventListener('click', function () { goToPanel(i); });
            dotsEl.appendChild(dot);
        });

        // Activate first panel
        panels[0].classList.add('panel-active');

        function goToPanel(index) {
            if (isAnimating) return;
            var next = Math.max(0, Math.min(index, total - 1));
            if (next === current) return;
            isAnimating = true;

            panels[current].classList.remove('panel-active');
            current = next;
            panels[current].classList.add('panel-active');

            // translateY by -(current/total)*100% of track height
            track.style.transform = 'translateY(-' + (current * (100 / total)) + '%)';

            dotsEl.querySelectorAll('.tech-pager-dot').forEach(function (d, i) {
                d.classList.toggle('active', i === current);
            });

            setTimeout(function () { isAnimating = false; }, DURATION);
        }

        // Mouse wheel
        var wheelDebounce = null;
        window.addEventListener('wheel', function (e) {
            if (!document.body.classList.contains('page-technology')) return;
            if (window.innerWidth <= 767) return;
            e.preventDefault();
            if (isAnimating) return;
            clearTimeout(wheelDebounce);
            wheelDebounce = setTimeout(function () {
                goToPanel(e.deltaY > 0 ? current + 1 : current - 1);
            }, 40);
        }, { passive: false });

        // Keyboard
        window.addEventListener('keydown', function (e) {
            if (!document.body.classList.contains('page-technology')) return;
            if (window.innerWidth <= 767) return;
            if (e.key === 'ArrowDown' || e.key === 'PageDown') {
                e.preventDefault();
                goToPanel(current + 1);
            } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
                e.preventDefault();
                goToPanel(current - 1);
            }
        });

        // Touch swipe
        var tStartY = 0;
        outer.addEventListener('touchstart', function (e) {
            tStartY = e.changedTouches[0].clientY;
        }, { passive: true });
        outer.addEventListener('touchend', function (e) {
            var dy = e.changedTouches[0].clientY - tStartY;
            if (Math.abs(dy) > 40) goToPanel(dy < 0 ? current + 1 : current - 1);
        }, { passive: true });

        // Disable Lenis on this page so it doesn't conflict
        if (window._lenis) {
            window._lenis.destroy();
        }
    });
})();

// =========================================
// Product Scroll Reveal — product.php
// Reset animation after filterCategory, then
// re-fire via IntersectionObserver as items enter viewport
// =========================================
(function initProductScrollReveal() {
    document.addEventListener('DOMContentLoaded', function () {
        const productGrid = document.querySelector('.product-grid');
        if (!productGrid) return;

        // Triggered when item enters viewport: replay productFadeUp
        const revealObserver = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    el.style.animation = 'none';
                    // Force reflow before re-applying
                    void el.offsetHeight;
                    el.style.animation = 'productFadeUp 0.55s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards';
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

        // After updateGrid shows items: only reset items NOT yet in viewport
        // Items already visible keep their existing animation (no flash)
        function resetAndObserve() {
            productGrid.querySelectorAll('.product-grid-item').forEach(function (item) {
                if (item.style.display === 'flex') {
                    var rect = item.getBoundingClientRect();
                    var inViewport = rect.top < window.innerHeight && rect.bottom > 0;
                    if (!inViewport) {
                        item.style.animation = 'none';
                        item.style.opacity = '0';
                        revealObserver.observe(item);
                    }
                }
            });
        }

        // Patch filterCategory to intercept after updateGrid
        var _orig = window.filterCategory;
        if (typeof _orig === 'function') {
            window.filterCategory = function (category, event) {
                _orig(category, event);
                // Short delay lets updateGrid finish setting display:flex
                setTimeout(resetAndObserve, 30);
            };
        }

        // Initial page load
        setTimeout(resetAndObserve, 120);
    });
})();

// =========================================
// Card UI Modal — technology.php
// Open modal on card click, close on X / overlay / ESC
// =========================================
(function initCardUiModal() {
    document.addEventListener('DOMContentLoaded', function () {
        var cards = document.querySelectorAll('.card-ui-item[data-modal]');
        if (!cards.length) return;

        function openModal(id) {
            var overlay = document.getElementById(id);
            if (!overlay) return;
            overlay.classList.add('modal-ui-active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(overlay) {
            overlay.classList.remove('modal-ui-active');
            document.body.style.overflow = '';
        }

        function closeAllModals() {
            document.querySelectorAll('.modal-ui-overlay.modal-ui-active').forEach(function (o) {
                closeModal(o);
            });
        }

        /* Card click → open */
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var modalId = card.getAttribute('data-modal');
                openModal(modalId);
            });
        });

        /* Close button click */
        document.querySelectorAll('.modal-ui-close').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var overlay = btn.closest('.modal-ui-overlay');
                if (overlay) closeModal(overlay);
            });
        });

        /* Overlay click (outside container) */
        document.querySelectorAll('.modal-ui-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal(overlay);
            });
        });

        /* ESC key */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllModals();
        });
    });
})();

// =========================================
// Card UI Scroll Reveal — fade-up cards on scroll
// =========================================
(function initCardUiReveal() {
    document.addEventListener('DOMContentLoaded', function () {
        var items = document.querySelectorAll('.card-ui-item');
        if (!items.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    /* Stagger delay based on data index */
                    var idx = Array.prototype.indexOf.call(items, entry.target);
                    var delay = idx * 120;
                    setTimeout(function () {
                        entry.target.classList.add('card-ui-visible');
                    }, delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        items.forEach(function (item) { observer.observe(item); });
    });
})();

} // end of window.kochScriptInitialized check
