<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';
require_once __DIR__ . '/../../admin/includes/crud.php';

try {
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'KOCH');
    $dbSliders = get_active_sliders($pdo, $companyId);
    $dbPartners = get_active_partners($pdo, $companyId);
    $dbProducts = get_active_featured_products($pdo);
    $companyInfo = get_company_info($pdo, 'KOCH');
} catch (Throwable $e) {
    $dbSliders = [];
    $dbPartners = [];
    $dbProducts = [];
    $companyInfo = null;
}

?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Koch Packaging and Packing Services Co.,Ltd</title>
    <meta name="description"
        content="Smart, Fast, and Sustainable Solutions สำหรับอุตสาหกรรมยานยนต์ในประเทศไทย - บริการบรรจุภัณฑ์และคลังสินค้าครบวงจร" />
       
    <!-- Google SEO -->
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://koch-packaging-services.com/" />

    <!-- Facebook Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="th_TH" />
    <meta property="og:site_name" content="KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:title" content="KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:description" content="Smart, Fast, and Sustainable Solutions สำหรับอุตสาหกรรมยานยนต์ในประเทศไทย - บริการบรรจุภัณฑ์และคลังสินค้าครบวงจร" />
    <meta property="og:url" content="https://koch-packaging-services.com/" />
    <meta property="og:image" content="https://koch-packaging-services.com/scr/assets/carousel/company/Gemini_Generated_Image_o9ab0wo9.png" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/logo 2.png" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Libraries: GSAP & Lenis -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/gh/studio-freight/lenis@1.0.19/bundled/lenis.min.js" defer></script>
    <!-- Note: SplitText is a club plugin, typically requires local file or private CDN. 
         Assuming the provided scripts will handle its absence gracefully or it will be added if available. -->

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<body>

    <?php include '../component/menubar.php'; ?>

    <!-- SLIDER SECTION -->
    <div class="main-container section-slider">
        <div class="slider-wrapper" id="sliderWrapper">

            <div class="slide-track" id="slideTrack">
                <?php if (!empty($dbSliders)): foreach ($dbSliders as $sl): ?>
                <div class="slide-item">
                    <img src="<?php echo htmlspecialchars(resolve_image_url((string)$sl['image_url'])); ?>" alt="<?php echo htmlspecialchars((string)$sl['title']); ?>">
                    <?php if (!empty($sl['title']) || !empty($sl['subtitle'])): ?>
                    <div class="slide-caption" style="position:absolute;bottom:20px;left:20px;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,.6)">
                        <?php if ($sl['title']): ?><h3 style="margin:0;font-size:1.4rem"><?php echo htmlspecialchars((string)$sl['title']); ?></h3><?php endif; ?>
                        <?php if ($sl['subtitle']): ?><p style="margin:4px 0 0;font-size:.9rem;opacity:.9"><?php echo htmlspecialchars((string)$sl['subtitle']); ?></p><?php endif; ?>
                        <?php if (!empty($sl['button_text']) && !empty($sl['button_url'])): ?><a href="<?php echo htmlspecialchars((string)$sl['button_url']); ?>" style="display:inline-block;margin-top:8px;padding:6px 16px;background:#ED2A2A;color:#fff;border-radius:6px;text-decoration:none;font-size:.8rem"><?php echo htmlspecialchars((string)$sl['button_text']); ?></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; else: ?>
                <div class="slide-item"><img src="../img/other/index/cardslides/cardslides-1.jpeg" alt="Slide 1"></div>
                <div class="slide-item"><img src="../img/other/index/cardslides/cardslides-2.jpeg" alt="Slide 2"></div>
                <div class="slide-item"><img src="../img/other/index/cardslides/cardslides-3.jpeg" alt="Slide 3"></div>
                <div class="slide-item"><img src="../img/other/index/cardslides/cardslides-4.jpeg" alt="Slide 4"></div>
                <div class="slide-item"><img src="../img/other/index/cardslides/cardslides-5.jpeg" alt="Slide 5"></div>
                <?php endif; ?>
            </div>

            <button class="nav-btn prev" id="prevBtn">‹</button>
            <button class="nav-btn next" id="nextBtn">›</button>
        </div>
    </div>


    <!-- CONTENT / DETAILS SECTION — เกี่ยวกับเรา -->
    <div class="content-section layout_padding koch-about-section">
        <!-- ใช้ Flexbox แทน Bootstrap row/col -->
        <div class="koch-about-row">

            <!-- คอลัมน์ซ้าย: รูปภาพ -->
            <div class="koch-about-col-img">
                <div class="details-box">
                    <div class="details-image">
                        <img src="../img/other/index/about/box_about.png" alt="Detail Image">
                    </div>
                </div>
            </div>

            <!-- คอลัมน์ขวา: เนื้อหา -->
            <div class="koch-about-col-text">

                <!-- หัวข้อหลัก -->
                <h1 class="details-title koch-about-title" data-i18n="index.about_title">เกี่ยวกับเรา</h1>
                <div class="koch-about-accent"></div>

                <!-- ย่อหน้าแรก -->
                <p class="koch-about-desc">
                    <span data-i18n="index.about_p1">KOCH คือผู้เชี่ยวชาญด้านบริการ Supply Chain ครบวงจร
                        สำหรับอุตสาหกรรมยานยนต์ในประเทศไทย มุ่งเน้นการสร้างระบบที่</span><strong class="koch-about-highlight" data-i18n="index.about_p1_strong">"Smart, Fast, and Sustainable"</strong>
                </p>

                <!-- ย่อหน้าที่สอง -->
                <p class="koch-about-desc">
                    <span data-i18n="index.about_p2">เราไม่ได้เป็นเพียงผู้ผลิตบรรจุภัณฑ์ แต่คือ</span>
                    <strong class="koch-about-highlight" data-i18n="index.about_p2_strong">"พันธมิตรเชิงกลยุทธ์"</strong>
                    <span data-i18n="index.about_p2_cont">ที่ช่วยยกระดับการบริหารจัดการโลจิสติกส์ให้มีประสิทธิภาพสูงสุด
                        ด้วยความเชี่ยวชาญเฉพาะด้าน:</span>
                </p>

                <!-- รายการความเชี่ยวชาญ -->
                <ul class="koch-about-list">
                    <li data-i18n="index.about_li1">ระบบ VMI (Vendor Managed Inventory)</li>
                    <li data-i18n="index.about_li2">การออกแบบวิศวกรรมภายในองค์กร (In-house Engineering)</li>
                    <li data-i18n="index.about_li3">การประยุกต์ใช้ระบบอัตโนมัติที่ล้ำสมัย</li>
                </ul>

                <!-- ปุ่ม CTA -->
                <div class="details-action-group">
                    <a href="../about/company.php" class="koch-about-cta" data-i18n="index.about_cta">ดูรายละเอียดเพิ่มเติม</a>
                </div>
            </div>

        </div>
    </div>


    <!-- CONTENT / DETAILS SECTION — บริการของเรา -->
    <div class="content-section layout_padding">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div style="margin-bottom: 8px;">
            <div style="text-align: left;">
                <h1 class="details-title" style="margin-top: 0; border: none; text-decoration: none;"
                    data-i18n="index.services_title">
                    บริการของเรา
                </h1>
                <p class="details-desc" style="margin-top: 5px; margin-bottom: 10px; line-height: 1.8;"
                    data-i18n="index.services_sub">
                    บริการบรรจุภัณฑ์และการจัดการลอจิสติกส์แบบครบวงจร ที่ตอบสนองทุกความต้องการของธุรกิจด้วยระบบที่ทันสมัย
                </p>
                <div class="details-action-group"></div>
            </div>
        </div>

        <!-- ส่วน Card Grid -->
        <div>
            <div class="details-box details-box-services">
                <div class="card-grid">
                    <a class="card" href="../service/development.php">
                        <div class="card__background"
                            style="background-image: url(../img/other/service/development/development.jpeg)">
                        </div>
                        <div class="card__content">
                            <div class="card__text-box">
                                <p class="card__category">Packaging Development</p>
                                <h3 class="card__heading" data-i18n="index.svc1_heading">นวัตกรรมการออกแบบบรรจุภัณฑ์ครบวงจร</h3>
                            </div>
                            <span class="card__button" data-i18n="index.svc_btn_readmore">อ่านเพิ่มเติม</span>
                        </div>
                    </a>
                    <a class="card" href="../service/supply_management.php">
                        <div class="card__background"
                            style="background-image: url(../img/other/service/supply/supply_management.jpeg)">
                        </div>
                        <div class="card__content">
                            <div class="card__text-box">
                                <p class="card__category">Packaging SupplyManagement System</p>
                                <h3 class="card__heading" data-i18n="index.svc2_heading">ระบบบริหารจัดการบรรจุภัณฑ์อัจฉริยะ</h3>
                            </div>
                            <span class="card__button" data-i18n="index.svc_btn_readmore">อ่านเพิ่มเติม</span>
                        </div>
                    </a>
                    <a class="card" href="../service/warehouse.php">
                        <div class="card__background"
                            style="background-image: url(../img/other/service/warehouse/warehouse.jpeg)">
                        </div>
                        <div class="card__content">
                            <div class="card__text-box">
                                <p class="card__category">Warehouse & Operation Management</p>
                                <h3 class="card__heading" data-i18n="index.svc3_heading">บริหารจัดการคลังสินค้าและงานปฏิบัติการมืออาชีพ</h3>
                            </div>
                            <span class="card__button" data-i18n="index.svc_btn_readmore">อ่านเพิ่มเติม</span>
                        </div>
                    </a>
                    <a class="card" href="../service/transportation.php">
                        <div class="card__background"
                            style="background-image: url(../img/other/service/transportation/Transportation.png)">
                        </div>
                        <div class="card__content">
                            <div class="card__text-box">
                                <p class="card__category">Transportation Inhouse Fleet & System</p>
                                <h3 class="card__heading" data-i18n="index.svc4_heading">ระบบขนส่งอัจฉริยะ</h3>
                            </div>
                            <span class="card__button" data-i18n="index.svc_btn_readmore">อ่านเพิ่มเติม</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>



    <!-- CONTENT / DETAILS SECTION — พันธมิตรที่ไว้วางใจเรา -->
    <div class="content-section section-partners" style="padding-top: 80px; padding-bottom: 80px;">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div style="margin-bottom: 24px;">
            <div style="text-align: right;">
                <h1 class="details-title" style="margin-top: 0; border: none; text-decoration: none; color: #ffffff; text-align: right;"
                    data-i18n="index.partners_title">
                    พันธมิตรที่ไว้วางใจเรา
                </h1>
                <p class="details-desc" style="margin-top: 5px; margin-bottom: 20px; line-height: 1.8; color: #ffffff; text-align: right;"
                    data-i18n="index.partners_sub">
                    ความไว้วางใจจากบริษัทชั้นนำ เป็นเครื่องยืนยันถึง คุณภาพและมาตรฐานการบริการระดับมืออาชีพของเรา
                </p>
            </div>
        </div>

        <!-- logo ลูกค้าเลื่อน loop slides -->
        <section class="loop-images-quotation" style="--bg: white; height: auto; min-height: 220px; padding: 20px 0;">
            <?php if (!empty($dbPartners)):
                $pReal = count($dbPartners);
                $minItems = 12;
                $pTotal = $pReal >= $minItems ? $pReal : (int)(ceil($minItems / $pReal) * $pReal);
            ?>
            <div class="login-track" style="--time: <?php echo max(30, $pTotal * 5); ?>s; --total: <?php echo $pTotal; ?>;">
                <?php for ($pi = 0; $pi < $pTotal; $pi++):
                    $partner = $dbPartners[$pi % $pReal];
                ?>
                <div class="login-item" style="--i: <?php echo $pi + 1; ?>;">
                    <?php if (!empty($partner['website_url'])): ?><a href="<?php echo htmlspecialchars((string)$partner['website_url']); ?>" target="_blank" rel="noopener noreferrer"><?php endif; ?>
                    <?php 
                    $logoUrl = resolve_image_url((string)$partner['logo_url']);
                    $imageExists = !empty($logoUrl) && !empty($partner['logo_url']) && file_exists(__DIR__ . '/../../' . ltrim($partner['logo_url'], '/'));
                    ?>
                    <?php if ($imageExists): ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="<?php echo htmlspecialchars((string)$partner['name']); ?>" loading="lazy">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px; text-align: center; padding: 8px;">No Logo</div>
                    <?php endif; ?>
                    <?php if (!empty($partner['website_url'])): ?></a><?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <!-- Empty state: show placeholder boxes to indicate CRUD capability -->
            <div class="login-track" style="--time: 60s; --total: 12;">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <div class="login-item" style="--i: <?php echo $i; ?>;">
                    <div style="width: 100%; height: 100%; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px; text-align: center; padding: 8px;">
                        Partner Logo<br><span style="font-size: 9px; opacity: 0.7;">Add from Admin</span>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- CONTENT / DETAILS SECTION — สินค้าแนะนำ -->
    <div class="content-section layout_padding section-products">
        <!-- ส่วนหัวข้อ (Heading) -->
        <div style="margin-bottom: 24px;">
            <div style="text-align: left;">
                <h1 class="details-title" style="margin-top: 0; border: none; text-decoration: none; color: #ffffff;"
                    data-i18n="index.products_title">
                    สินค้าแนะนำ
                </h1>
                <p class="details-desc" style="margin-top: 5px; margin-bottom: 20px; color: #ffffff; line-height: 1.8;"
                    data-i18n="index.products_sub">
                    ผลิตภัณฑ์บรรจุภัณฑ์คุณภาพสูง ออกแบบและพัฒนา เพื่อรองรับทุกความต้องการของอุตสาหกรรม
                </p>
            </div>
        </div>

        <!-- CARDS COMPONENT — Expandable Image Strip (แทนที่ Block_Expanding_Cards)
             Semantic HTML with accessibility features:
             - Security: rel="noopener noreferrer" prevents vulnerabilities
             - Performance: loading="lazy" defers off-screen images
             - SEO: Descriptive alt text for all images
        -->
        <!-- Product Carousel: dynamic pages from DB — auto-slide ทุก 5 วิ -->
        <div class="dev-carousel-root">
            <div class="dev-carousel-viewport">
                <div class="dev-carousel-track">
                    <?php if (!empty($dbProducts)):
                        $productPages = array_chunk($dbProducts, 6);
                        foreach ($productPages as $page): ?>
                    <div class="dev-carousel-page">
                        <div class="dev-cards">
                            <?php foreach ($page as $product): ?>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer">
                                <?php if (!empty($product['image_url'])): ?><img src="<?php echo htmlspecialchars(resolve_image_url((string)$product['image_url'])); ?>" alt="<?php echo htmlspecialchars((string)$product['name']); ?>" loading="lazy"><?php else: ?><div style="height:140px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px">No Image</div><?php endif; ?>
                                <div class="dev-card-title"><?php echo htmlspecialchars((string)$product['name']); ?></div>
                                <p class="dev-card-desc"><?php echo htmlspecialchars((string)($product['category'] ?? $product['name'])); ?></p>
                            </a></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="dev-carousel-page">
                        <div class="dev-cards">
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/box/rsc.png" alt="RSC Box" loading="lazy"><div class="dev-card-title">RSC Box</div><p class="dev-card-desc">กล่องกระดาษ</p></a></div>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/box/ftd.png" alt="FTD Box" loading="lazy"><div class="dev-card-title">FTD Box</div><p class="dev-card-desc">กล่องกระดาษ</p></a></div>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/box/osc.png" alt="OSC Box" loading="lazy"><div class="dev-card-title">OSC Box</div><p class="dev-card-desc">กล่องกระดาษ</p></a></div>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/Wooden/wooden_crates.png" alt="Wooden Crates" loading="lazy"><div class="dev-card-title">Wooden Crates</div><p class="dev-card-desc">บรรจุภัณฑ์ไม้</p></a></div>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/Plastic/plastic_container.png" alt="Plastic Container" loading="lazy"><div class="dev-card-title">Plastic Container</div><p class="dev-card-desc">บรรจุภัณฑ์พลาสติก</p></a></div>
                            <div class="dev-card"><a href="../main/product.php" rel="noopener noreferrer"><img src="../img/products/Steel/steel_rack.png" alt="Steel Rack" loading="lazy"><div class="dev-card-title">Steel Rack</div><p class="dev-card-desc">บรรจุภัณฑ์เหล็ก</p></a></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div><!-- end .dev-carousel-track -->
            </div><!-- end .dev-carousel-viewport -->
            <div class="dev-carousel-dots" aria-label="Product carousel navigation"></div>
        </div><!-- end .dev-carousel-root -->
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>