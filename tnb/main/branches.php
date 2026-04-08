<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

try {
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'TNB');
    $companyInfo = get_company_info($pdo, 'TNB');
} catch (Throwable $e) {
    $companyInfo = null;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สาขาและเครือข่ายจุดบริการ | TNB Logistics</title>
    <meta name="description" content="สาขาและเครือข่ายจุดบริการยุทธศาสตร์ของ TNB Logistics ครอบคลุมบางแสน แหลมฉบัง บางกะดี และลาดกระบัง" />

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

    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Branches Page" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-branches: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-branches">
    <?php include '../component/menubar.php'; ?>

    <!-- หัวข้อหลัก — Blue gradient header เหมือน technology.php -->
    <div class="card-ui-header layout_padding">
        <div class="container">
            <h1 class="card-ui-main-title" data-i18n="branches.title">สาขาของเรา</h1>
            <p class="card-ui-main-desc" data-i18n="branches.subtitle">เครือข่ายจุดบริการยุทธศาสตร์ครอบคลุมพื้นที่สำคัญทางอุตสาหกรรมเพื่อรองรับความต้องการของลูกค้าอย่างมีประสิทธิภาพ</p>
        </div>
    </div>

    <!-- เนื้อหาหลัก -->
    <div class="branches-page">
        <div class="branches-layout">

            <!-- ส่วนรูปภาพ (Sticky) -->
            <div class="branches-image-section">
                <div class="branches-image-wrapper">
                    <img src="../img/other/service/nationwide/middlemiledistribution.png" alt="TNB Logistics Branches Network">
                    <div class="branches-image-overlay">
                        <h3 data-i18n="branches.overlay_title">เครือข่ายสาขาทั่วประเทศ</h3>
                        <p data-i18n="branches.overlay_desc">ครอบคลุมพื้นที่สำคัญทางอุตสาหกรรมและโลจิสติกส์</p>
                    </div>
                </div>
            </div>

            <!-- ส่วนการ์ดสาขา -->
            <div class="branches-cards-section">

                <div class="branch-card">
                    <h3 class="branch-card__name" data-i18n="branches.bangsaen_name">สาขาบางแสน (สำนักงานใหญ่)</h3>
                    <p class="branch-card__desc" data-i18n="branches.bangsaen_desc">ศูนย์กลางการบริหารจัดการการขนส่งภายในประเทศ เป็นสำนักงานใหญ่ที่รวมศูนย์บัญชาการและประสานงานทุกสาขา</p>
                    <ul class="branch-card__services">
                        <li>Domestic Transport</li>
                        <li>Fleet Management</li>
                        <li>HQ Operations</li>
                    </ul>
                </div>

                <div class="branch-card">
                    <h3 class="branch-card__name" data-i18n="branches.laemchabang_name">สาขาแหลมฉบัง</h3>
                    <p class="branch-card__desc" data-i18n="branches.laemchabang_desc">ให้บริการจัดจองตู้คอนเทนเนอร์และพื้นที่ฝากวางตู้ (Container Drop Yard) เชื่อมต่อท่าเรือแหลมฉบังโดยตรง</p>
                    <ul class="branch-card__services">
                        <li>Container Yard</li>
                        <li>Import/Export</li>
                        <li>Port Linkage</li>
                    </ul>
                </div>

                <div class="branch-card">
                    <h3 class="branch-card__name" data-i18n="branches.bangkadi_name">สาขาบางกะดี</h3>
                    <p class="branch-card__desc" data-i18n="branches.bangkadi_desc">เชี่ยวชาญการให้บริการรถ Shuttle รับ-ส่งสินค้าระหว่างคลังสินค้าและการจัดการตู้คอนเทนเนอร์</p>
                    <ul class="branch-card__services">
                        <li>Shuttle Service</li>
                        <li>WH to WH</li>
                        <li>Container Mgmt</li>
                    </ul>
                </div>

                <div class="branch-card">
                    <h3 class="branch-card__name" data-i18n="branches.latkrabang_name">สาขาลาดกระบัง</h3>
                    <p class="branch-card__desc" data-i18n="branches.latkrabang_desc">ศูนย์กระจายสินค้าและลานจอดรถขนาด 9,000 ตร.ม. ตั้งอยู่ใกล้กับ ICD เพื่อความรวดเร็วในการขนส่ง</p>
                    <ul class="branch-card__services">
                        <li>Distribution Center</li>
                        <li>9,000 sqm Yard</li>
                        <li>Near ICD</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>