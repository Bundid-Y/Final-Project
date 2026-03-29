<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$pdo = Database::connection();
$companyId = get_company_id_by_code($pdo, 'TNB');
$dbSliders = get_active_sliders($pdo, $companyId);
$dbPartners = get_active_partners($pdo, $companyId);
$dbTruckTypes = get_active_truck_types($pdo);
$dbBranches = get_active_branches($pdo, $companyId);
$companyInfo = get_company_info($pdo, 'TNB');
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TNB Logistics | บริการขนส่งและโลจิสติกส์ครบวงจร
    </title>
    <meta name="description" content="บริการขนส่งและโลจิสติกส์ครบวงจร" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/branches.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/branches.html" />
    <meta property="og:title" content="สาขาและเครือข่ายจุดบริการ | TNB Logistics" />
    <meta property="og:description" content="สาขาและเครือข่ายจุดบริการยุทธศาสตร์ของ TNB Logistics ครอบคลุมบางแสน แหลมฉบัง บางกะดี และลาดกระบัง" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lato:wght@400;700&family=Playfair+Display:wght@400;700&family=Sarabun:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Libraries: GSAP & Lenis -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/gh/studio-freight/lenis@1.0.19/bundled/lenis.min.js" defer></script>
    <!-- Note: SplitText is a club plugin, typically requires local file or private CDN. 
         Assuming the provided scripts will handle its absence gracefully or it will be added if available. -->

    <!-- Custom CSS & JS -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<body>

    <?php include '../component/menubar.php'; ?>

    <!-- SLIDER SECTION (Tap House Tilt Panel) — 6 Slides with Prev/Next -->
    <section class="tnb-home-slider" id="tnbHomeSlider">

        <!-- Prev / Next Navigation Buttons -->
        <button class="tnb-slider-nav tnb-slider-nav--prev" id="tnbSliderPrev"
            aria-label="Previous slide">&#8249;</button>
        <button class="tnb-slider-nav tnb-slider-nav--next" id="tnbSliderNext" aria-label="Next slide">&#8250;</button>

        <!-- Slides Track -->
        <div class="tnb-panel-container">
            <?php if (!empty($dbSliders)): foreach ($dbSliders as $si => $sl): ?>
            <div class="tnb-panel tnb-slide<?php echo $si === 0 ? ' tnb-slide--active' : ''; ?>" data-index="<?php echo $si; ?>">
                <div class="tnb-panel__content-col">
                    <div class="tnb-panel__content">
                        <div class="tnb-panel__text">
                            <h1 class="tnb-panel__title"><?php echo htmlspecialchars((string)$sl['title']); ?></h1>
                            <?php if (!empty($sl['subtitle'])): ?><p class="tnb-panel__addr"><span></span><span><?php echo htmlspecialchars((string)$sl['subtitle']); ?></span></p><?php endif; ?>
                            <?php if (!empty($sl['button_text']) && !empty($sl['button_url'])): ?><a href="<?php echo htmlspecialchars((string)$sl['button_url']); ?>" style="display:inline-block;margin-top:12px;padding:8px 20px;background:#0d2d6b;color:#fff;border-radius:6px;text-decoration:none;font-size:.85rem"><?php echo htmlspecialchars((string)$sl['button_text']); ?></a><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="tnb-panel__img-col">
                    <img src="<?php echo htmlspecialchars(resolve_image_url((string)$sl['image_url'])); ?>" alt="<?php echo htmlspecialchars((string)$sl['title']); ?>" class="tnb-panel__img" />
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="tnb-panel tnb-slide tnb-slide--active" data-index="0">
                <div class="tnb-panel__content-col"><div class="tnb-panel__content"><div class="tnb-panel__text"><h1 class="tnb-panel__title">TNB Logistics</h1><p class="tnb-panel__addr"><span></span><span data-i18n="slide.s0">บริการขนส่งและโลจิสติกส์ครบวงจร</span></p></div></div></div>
                <div class="tnb-panel__img-col"><div style="height:400px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:16px">No Sliders Available</div></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Dots Indicator -->
        <div class="tnb-slider-dots" id="tnbSliderDots" aria-label="Slide indicators">
            <?php
            $slideCount = !empty($dbSliders) ? count($dbSliders) : 3;
            for ($di = 0; $di < $slideCount; $di++): ?>
            <button class="tnb-dot<?php echo $di === 0 ? ' tnb-dot--active' : ''; ?>" data-index="<?php echo $di; ?>" aria-label="Slide <?php echo $di + 1; ?>"></button>
            <?php endfor; ?>
        </div>
    </section>


    <!-- CONTENT / DETAILS SECTION — เกี่ยวกับเรา -->
    <div class="content-section layout_padding">
        <!-- ใช้ Flexbox แทน Bootstrap row/col -->
        <div class="flex-row" style="display: flex; flex-wrap: wrap; align-items: center; gap: 30px;">

            <!-- คอลัมน์ซ้าย: รูปภาพ -->
            <div style="flex: 1; min-width: 300px;">
                <div class="details-box">
                    <div class="details-image">
                        <img src="../img/other/index/about/index_about.png" alt="Detail Image">
                    </div>
                </div>
            </div>

            <!-- คอลัมน์ขวา: เนื้อหา -->
            <div style="flex: 1; min-width: 300px;">
                <h1 class="details-title" style="margin-bottom: 10px;" data-i18n="index.about_title">
                    เกี่ยวกับเรา</h1>

                <p class="details-desc" style="margin-top: 0; font-size: 18px; line-height: 1.8;">
                    <span data-i18n="index.about_p1"><strong>TNB Logistics</strong> เป็นผู้ให้บริการด้าน<strong>โลจิสติกส์และการขนส่งครบวงจร</strong>
                        ให้บริการ<strong>ขนส่งสินค้าภายในประเทศ</strong> <strong>การขนส่งตู้คอนเทนเนอร์</strong> และ<strong>งานชัตเทิลระหว่างคลังสินค้า</strong> (WH to WH)
                        บริษัทมีสำนักงานและสาขาใน<strong>พื้นที่ยุทธศาสตร์สำคัญ</strong> เช่น <strong>บางแสน</strong> <strong>แหลมฉบัง</strong> <strong>บางกะดี</strong> และ<strong>ลาดกระบัง</strong>
                        เพื่อรองรับความต้องการของลูกค้าใน<strong>ภาคอุตสาหกรรม</strong>อย่างมีประสิทธิภาพ</span>
                </p>

                <!-- ย่อหน้าที่สอง -->
                <p class="details-desc" style="margin-top: 16px; font-size: 18px; line-height: 1.8;">
                    <span data-i18n="index.about_p2">TNB มุ่งเน้น<strong>มาตรฐานการบริการ</strong> <strong>ความปลอดภัย</strong>
                        และ<strong>ความตรงต่อเวลา</strong>ในทุกกระบวนการขนส่ง
                        พร้อมนำ<strong>เทคโนโลยีระบบบริหารจัดการการขนส่ง</strong>มาใช้เพื่อเพิ่ม<strong>ความโปร่งใส</strong>และ<strong>ประสิทธิภาพในการทำงาน</strong>
                        บริษัทพร้อมเป็น<strong>พันธมิตรด้านโลจิสติกส์</strong>ที่ลูกค้าไว้วางใจและเติบโตไปพร้อมกันอย่างยั่งยืน</span>
                </p>

                <div class="details-action-group">
                </div>
            </div>

        </div>
    </div>



    <!-- CONTENT / DETAILS SECTION — บริการของเรา (News Card Style) -->
    <div id="services" class="tnb-services-section content-section layout_padding">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div class="tnb-services-section__heading">
            <h1 class="details-title" style="margin-top: 0; margin-bottom: 10px; border: none; text-decoration: none;"
                data-i18n="index.services_title">
                บริการของเรา
            </h1>
            <p class="details-desc" style="margin-top: 0; margin-bottom: 40px; line-height: 1.6;"
                data-i18n="index.services_sub">
                บริการบรรจุภัณฑ์และการจัดการลอจิสติกส์แบบครบวงจร ที่ตอบสนองทุกความต้องการของธุรกิจด้วยระบบที่ทันสมัย
            </p>
        </div>

        <!-- ส่วน News Card Grid — 6 บริการหลัก (3×2 Grid) -->
        <div class="tnb-news-content-wrapper">

            <!-- การ์ด 1: Packaging Development -->
            <div class="tnb-news-card">
                <a href="../service/domestic.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/domestic/Inboundlogistics.png" alt="Domestic Transportation"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Domestic Transportation</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc1_sub">บริการขนส่งสินค้าในประเทศ</div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc1_excerpt">
                            บริการขนส่งสินค้าในประเทศที่รวดเร็วและปลอดภัย ตอบโจทย์ทุกการจัดส่งทั่วไทย</p>
                        <a href="../service/domestic.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc1_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>

            <!-- การ์ด 2: Supply Management -->
            <div class="tnb-news-card">
                <a href="../service/shuttle.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/shuttle/shuttletruck.png" alt="Supply Management"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Shuttle Truck Service</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc2_sub">บริการรถรับ–ส่งระหว่างคลังสินค้า
                    </div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc2_excerpt">
                            บริการรถรับ-ส่งสินค้าระหว่างคลัง (WH to WH) เพื่อการบริหารจัดการที่ไร้รอยต่อ</p>
                        <a href="../service/shuttle.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc2_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>

            <!-- การ์ด 3: Warehouse & Operation -->
            <div class="tnb-news-card">
                <a href="../service/import-export.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/import-export/import-export.png" alt="Warehouse Management"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Import & Export Container Transportation</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc3_sub">
                        บริการขนส่งตู้คอนเทนเนอร์นำเข้า–ส่งออก</div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc3_excerpt">
                            บริการรถหัวลากและขนส่งตู้คอนเทนเนอร์นำเข้า-ส่งออกอย่างมืออาชีพ</p>
                        <a href="../service/import-export.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc3_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>

            <!-- การ์ด 4: Transportation -->
            <div class="tnb-news-card">
                <a href="../service/container.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/container/containerreceiving.png" alt="Transportation"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Container Arrangement & Container Yard</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc4_sub">
                        บริการจัดการตู้คอนเทนเนอร์และลานตู้สินค้า</div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc4_excerpt">
                            บริหารจัดการตู้คอนเทนเนอร์และพื้นที่ลานตู้สินค้าอย่างเป็นระบบ</p>
                        <a href="../service/container.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc4_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>
            <!-- การ์ด 5: Domestic Logistics -->
            <div class="tnb-news-card">
                <a href="../service/nationwide.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/nationwide/lastmiledelivery.png" alt="Domestic Logistics"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Nationwide Distribution</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc5_sub">บริการกระจายสินค้าทั่วประเทศ</div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc5_excerpt">
                            บริการกระจายสินค้าครอบคลุมทุกพื้นที่ทั่วประเทศ ส่งตรงถึงปลายทางอย่างแม่นยำ</p>
                        <a href="../service/nationwide.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc5_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>

            <!-- การ์ด 6: Container Service -->
            <div class="tnb-news-card">
                <a href="../service/container.php" class="tnb-news-card__card-link"></a>
                <img src="../img/other/service/parking/fleetmanagement.png" alt="Container Service"
                    class="tnb-news-card__image">
                <div class="tnb-news-card__text-wrapper">
                    <h2 class="tnb-news-card__title">Truck Parking & Fleet Support</h2>
                    <div class="tnb-news-card__post-date" data-i18n="index.svc6_sub">
                        บริการที่จอดรถบรรทุกและบริหารพื้นที่จอด</div>
                    <div class="tnb-news-card__details-wrapper">
                        <p class="tnb-news-card__excerpt" data-i18n="index.svc6_excerpt">
                            ให้บริการพื้นที่จุดจอดรถบรรทุกที่ปลอดภัย พร้อมสิ่งอำนวยความสะดวกครบครัน</p>
                        <a href="../service/parking.php" class="tnb-news-card__read-more"
                            data-i18n="index.svc6_more">อ่านเพิ่มเติม ›</a>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <!-- CONTENT / DETAILS SECTION — พันธมิตรที่ไว้วางใจเรา -->
    <style>
        .partners-overlay-text {
            position: absolute;
            width: 100%;
            right: 0;
            top: 40px;
            left: 0;
            height: 420px;
            z-index: 2;
            pointer-events: none;
            clip-path: polygon(100% 0, 39% 9.4%, 37.9% 9.6%, 37% 10%, 36.3% 10.5%, 35.7% 11.2%, 35.3% 12%, 35.1% 12.9%, 35% 14%, 35% 86%, 35.1% 87.1%, 35.3% 88%, 35.7% 88.8%, 36.3% 89.5%, 37% 90%, 37.9% 90.4%, 39% 90.6%, 100% 100%);
            transform: translateY(var(--deco-y, 0));
            will-change: transform;
            padding-left: 30px;
            padding-right: 30px;
            box-sizing: border-box;
        }

        @media (max-width: 991px) {
            .partners-overlay-text {
                display: none !important;
            }
        }
    </style>
    <div class="content-section section-partners" style="padding-top: 80px; padding-bottom: 80px;">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div style="margin-bottom: 24px;">
            <div style="text-align: right;">
                <h1 class="details-title" style="margin-top: 0; margin-bottom: 10px; border: none; text-decoration: none; color: #ffffff; text-align: right;"
                    data-i18n="index.partners_title">
                    พันธมิตรที่ไว้วางใจเรา
                </h1>
                <p class="details-desc" style="margin-top: 0; margin-bottom: 20px; line-height: 1.6; color: #ffffff; text-align: right;"
                    data-i18n="index.partners_sub">
                    ความไว้วางใจจากบริษัทชั้นนำ เป็นเครื่องยืนยันถึง คุณภาพและมาตรฐานการบริการระดับมืออาชีพของเรา
                </p>
            </div>
        </div>

        <!-- logo ลูกค้าเลื่อน loop slides -->
        <section class="loop-images-quotation" style="--bg: white; height: auto; min-height: 160px; padding: 15px 0;">
            <?php if (!empty($dbPartners)): $pTotal = count($dbPartners); ?>
            <div class="login-track" style="--time: <?php echo max(30, $pTotal * 5); ?>s; --total: <?php echo $pTotal; ?>;">
                <?php foreach ($dbPartners as $pi => $partner): ?>
                <div class="login-item" style="--i: <?php echo $pi + 1; ?>;">
                    <?php if (!empty($partner['website_url'])): ?><a href="<?php echo htmlspecialchars((string)$partner['website_url']); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                    <img src="<?php echo htmlspecialchars(resolve_image_url((string)$partner['logo_url'])); ?>" alt="<?php echo htmlspecialchars((string)$partner['name']); ?>" loading="lazy">
                    <?php if (!empty($partner['website_url'])): ?></a><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="login-track" style="--time: 60s; --total: 3;">
                <div class="login-item" style="--i: 1;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                <div class="login-item" style="--i: 2;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                <div class="login-item" style="--i: 3;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
            </div>
            <?php endif; ?>
        </section>
    </div>


    <!-- CONTENT / DETAILS SECTION — ประเภทรถ -->
    <div class="content-section layout_padding section-products">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div style="margin-bottom: 24px;">
            <div style="text-align: left;">
                <h1 class="details-title" style="margin-top: 0; margin-bottom: 10px; border: none; text-decoration: none; color: #ffffff;"
                    data-i18n="index.products_title">
                    ประเภทรถ
                </h1>
                <p class="details-desc" style="margin-top: 0; margin-bottom: 12px; color: #ffffff; line-height: 1.6;"
                    data-i18n="index.products_sub">
                    บริการรถบรรทุกหลากหลายประเภท เพื่อรองรับทุกความต้องการด้านการขนส่งและโลจิสติกส์ของคุณ
                </p>
            </div>
        </div>

        <!-- Truck Type Carousel: dynamic from DB -->
        <div class="dev-carousel-root">
            <div class="dev-carousel-viewport">
                <div class="dev-carousel-track">
                    <?php if (!empty($dbTruckTypes)):
                        $truckPages = array_chunk($dbTruckTypes, 5);
                        foreach ($truckPages as $tPage): ?>
                    <div class="dev-carousel-page">
                        <div class="dev-cards">
                            <?php foreach ($tPage as $truck): ?>
                            <div class="dev-card">
                                <a href="../main/trucktypes.php" rel="noopener noreferrer">
                                    <?php if (!empty($truck['image_url'])): ?><img src="<?php echo htmlspecialchars(resolve_image_url((string)$truck['image_url'])); ?>" alt="<?php echo htmlspecialchars((string)$truck['name']); ?>" loading="lazy"><?php else: ?><div style="height:140px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px">No Image</div><?php endif; ?>
                                    <div class="dev-card-title"><?php echo htmlspecialchars((string)$truck['name']); ?></div>
                                    <p class="dev-card-desc"><?php echo htmlspecialchars((string)($truck['capacity'] ?? $truck['name'])); ?></p>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="dev-carousel-page">
                        <div class="dev-cards">
                            <div class="dev-card"><a href="../main/trucktypes.php" rel="noopener noreferrer"><img src="../img/alltruck/picipjumbo/picup1.png" alt="Pickup Jumbo" loading="lazy"><div class="dev-card-title">Pickup Jumbo</div><p class="dev-card-desc">รถกระบะจัมโบ้</p></a></div>
                            <div class="dev-card"><a href="../main/trucktypes.php" rel="noopener noreferrer"><img src="../img/alltruck/6 wheel/6wheel1.png" alt="6 Wheel" loading="lazy"><div class="dev-card-title">6 Wheel</div><p class="dev-card-desc">รถ 6 ล้อ</p></a></div>
                            <div class="dev-card"><a href="../main/trucktypes.php" rel="noopener noreferrer"><img src="../img/alltruck/6 wheel_trailer/6wheeltrailer_1.png" alt="6 Wheel Trailer" loading="lazy"><div class="dev-card-title">6 Wheel Trailer</div><p class="dev-card-desc">รถ 6 ล้อพ่วง</p></a></div>
                            <div class="dev-card"><a href="../main/trucktypes.php" rel="noopener noreferrer"><img src="../img/alltruck/10 wheel_trailer/10wheeltrailer_1.png" alt="10 Wheel Trailer" loading="lazy"><div class="dev-card-title">10 Wheel Trailer</div><p class="dev-card-desc">รถ 10 ล้อพ่วง</p></a></div>
                            <div class="dev-card"><a href="../main/trucktypes.php" rel="noopener noreferrer"><img src="../img/alltruck/trailer/trailer3.png" alt="Trailer Head" loading="lazy"><div class="dev-card-title">Trailer Head</div><p class="dev-card-desc">หัวลาก</p></a></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div><!-- end .dev-carousel-track -->
            </div><!-- end .dev-carousel-viewport -->
            <div class="dev-carousel-dots" aria-label="Truck carousel navigation"></div>
        </div><!-- end .dev-carousel-root -->
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>