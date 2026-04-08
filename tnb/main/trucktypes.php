<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

try {
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'TNB');
    $dbTruckTypes = get_active_truck_types($pdo);
} catch (Throwable $e) {
    $dbTruckTypes = [];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประเภทรถบรรทุก | TNB Logistics</title>
    <meta name="description" content="ประเภทรถบรรทุกและยานพาหนะที่ TNB Logistics ให้บริการ รองรับการขนส่งสินค้าทุกประเภท" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/trucktypes.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/trucktypes.html" />
    <meta property="og:title" content="ประเภทรถบรรทุก | TNB Logistics" />
    <meta property="og:description" content="ประเภทรถบรรทุกและยานพาหนะที่ TNB Logistics ให้บริการ รองรับการขนส่งสินค้าทุกประเภท" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />
    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Truck Types Page" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-trucktypes: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-trucktypes">
    <?php include '../component/menubar.php'; ?>

    <!-- หัวข้อหลัก — Blue gradient header เหมือน technology.php -->
    <div class="card-ui-header layout_padding">
        <div class="container">
            <h1 class="card-ui-main-title" data-i18n="trucktypes.title">ประเภทรถบรรทุก</h1>
            <p class="card-ui-main-desc" data-i18n="trucktypes.subtitle">รถบรรทุกหลากหลายประเภทของ TNB Logistics พร้อมรองรับทุกความต้องการด้านการขนส่ง</p>
        </div>
    </div>

    <!-- Truck Cards Grid Section -->
    <section class="trucktypes-cards-section">
        <div class="trucktypes-interactive__grid">
            <?php if (!empty($dbTruckTypes)): foreach ($dbTruckTypes as $truck): ?>
            <div class="truck-card">
                <div class="truck-card__img-container">
                    <?php if (!empty($truck['image_url'])): ?>
                    <img class="truck-card__img" src="<?php echo htmlspecialchars(resolve_image_url((string)$truck['image_url'])); ?>" alt="<?php echo htmlspecialchars((string)$truck['name']); ?>">
                    <?php else: ?>
                    <div style="height:200px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="truck-card__body">
                    <h3 class="truck-card__name"><?php echo htmlspecialchars((string)$truck['name']); ?></h3>
                    <?php if (!empty($truck['description'])): ?><p class="truck-card__desc"><?php echo htmlspecialchars((string)$truck['description']); ?></p><?php endif; ?>
                    <ul class="truck-card__specs">
                        <?php if (!empty($truck['capacity'])): ?><li><strong>น้ำหนักบรรทุก:</strong> <?php echo htmlspecialchars((string)$truck['capacity']); ?></li><?php endif; ?>
                        <?php if (!empty($truck['dimensions'])): ?><li><strong>ขนาดตู้:</strong> <?php echo htmlspecialchars((string)$truck['dimensions']); ?></li><?php endif; ?>
                        <?php if (!empty($truck['price_range'])): ?><li><strong>ราคา:</strong> <?php echo htmlspecialchars((string)$truck['price_range']); ?></li><?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="truck-card">
                <div class="truck-card__img-container">
                    <div style="height:200px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px">No Truck Types Available</div>
                </div>
                <div class="truck-card__body">
                    <h3 class="truck-card__name">No Data</h3>
                    <p class="truck-card__desc">Please add truck types from admin dashboard</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../component/footer.php'; ?>
</body>

</html>