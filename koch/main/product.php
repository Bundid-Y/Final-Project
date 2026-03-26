<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$pdo = Database::connection();
$companyId = get_company_id_by_code($pdo, 'KOCH');
$dbProducts = get_active_products($pdo);

$categoryMap = [
    'mail' => ['กล่องกระดาษ', 'paper', 'box', 'cardboard', 'mail'],
    'corrugated' => ['ไม้', 'wooden', 'wood', 'corrugated'],
    'diecut' => ['พลาสติก', 'plastic', 'pp', 'diecut'],
    'accessory' => ['เหล็ก', 'steel', 'metal', 'rack', 'accessory'],
];
function resolve_category(string $cat): string {
    global $categoryMap;
    $catLower = strtolower($cat);
    foreach ($categoryMap as $key => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($catLower, $kw)) return $key;
        }
    }
    return 'mail';
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koch Packaging - Products</title>

    <!-- Google SEO -->
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://koch-packaging-services.com/expertise.html" />

    <!-- Facebook Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="th_TH" />
    <meta property="og:site_name" content="KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:title" content="ความเชี่ยวชาญของเรา - KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:description" content="ความเชี่ยวชาญของ KOCH - ยกระดับการจัดการซัพพลายเชนสู่อนาคต ด้วยความเชี่ยวชาญระดับมืออาชีพ" />
    <meta property="og:url" content="https://koch-packaging-services.com/expertise.html" />
    <meta property="og:image" content="https://koch-packaging-services.com/scr/assets/carousel/company/Gemini_Generated_Image_o9ab0wo9.png" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/logo 2.png" />
    
    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<body>
    <?php include '../component/menubar.php'; ?>

    <main>
        <!-- PRODUCT CATEGORY MENU SECTION -->
        <section class="product-category-section layout_padding" style="padding-top: 100px; padding-bottom: 90px;">
            <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                <!-- Heading -->
                <h2 style="width: 100%; float: left; font-size: 40px; color: #111111; text-align: center; font-weight: bold; font-family: 'Sarabun', sans-serif; margin-top: 0px; margin-bottom: 15px;"
                    data-i18n="product.title">
                    ประเภทสินค้า
                </h2>
                <p style="width: 100%; float: left; font-size: 16px; color: #242424; text-align: center; font-family: 'Sarabun', sans-serif; margin-top: 0px; margin-bottom: 40px;"
                    data-i18n="product.desc">
                    ครอบคลุมวัสดุหลากหลายประเภท เช่น กระดาษ ไม้ พลาสติก และเหล็ก
                </p>

                <!-- Menu Bar -->
                <div class="category-menu-container" style="width: 100%; float: left; margin-bottom: 40px;">
                    <ul class="category-menu">
                        <li><a href="#all" class="active" onclick="filterCategory('all', event)"
                                data-i18n="product.catAll">ทั้งหมด</a></li>
                        <li><a href="#mail" onclick="filterCategory('mail', event)"
                                data-i18n="product.catPaper">กล่องกระดาษ</a></li>
                        <li><a href="#corrugated" onclick="filterCategory('corrugated', event)"
                                data-i18n="product.catWooden">บรรจุภัณฑ์ไม้</a></li>
                        <li><a href="#diecut" onclick="filterCategory('diecut', event)"
                                data-i18n="product.catPlastic">บรรจุภัณฑ์พลาสติก</a></li>
                        <li><a href="#accessory" onclick="filterCategory('accessory', event)"
                                data-i18n="product.catSteel">บรรจุภัณฑ์เหล็ก</a></li>
                    </ul>
                </div>

                <!-- Product Grid Contents -->
                <div class="product-grid">
                    <?php if (!empty($dbProducts)): foreach ($dbProducts as $prod):
                        $cat = resolve_category((string)($prod['category'] ?? ''));
                    ?>
                    <div class="product-grid-item" data-category="<?php echo $cat; ?>">
                        <?php if (!empty($prod['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars('../../' . (string)$prod['image_url']); ?>" alt="<?php echo htmlspecialchars((string)$prod['name']); ?>">
                        <?php else: ?>
                        <div style="height:200px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px">No Image</div>
                        <?php endif; ?>
                        <div class="product-overlay"><span class="product-title"><?php echo htmlspecialchars((string)$prod['name']); ?></span></div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="product-grid-item" data-category="mail"><img src="../img/products/box/rsc.png" alt="RSC Box"><div class="product-overlay"><span class="product-title">RSC Box</span></div></div>
                    <div class="product-grid-item" data-category="corrugated"><img src="../img/products/Wooden/wooden_crates.png" alt="Wooden Crates"><div class="product-overlay"><span class="product-title">Wooden Crates</span></div></div>
                    <div class="product-grid-item" data-category="diecut"><img src="../img/products/Plastic/plastic_container.png" alt="Plastic Container"><div class="product-overlay"><span class="product-title">Plastic Container</span></div></div>
                    <div class="product-grid-item" data-category="accessory"><img src="../img/products/Steel/steel_rack.png" alt="Steel Rack"><div class="product-overlay"><span class="product-title">Steel Rack</span></div></div>
                    <div class="product-grid-item" data-category="mail"><img src="../img/products/box/die-cut.png" alt="Die-Cut Box"><div class="product-overlay"><span class="product-title">Die-Cut Box</span></div></div>
                    <div class="product-grid-item" data-category="corrugated"><img src="../img/products/Wooden/wooden_pallet.png" alt="Wooden Pallet"><div class="product-overlay"><span class="product-title">Wooden Pallet</span></div></div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- CSS and JS for Product Page has been moved to ../css/style.css and ../js/script.js respectively -->
        </section>
    </main>

    <?php include '../component/footer.php'; ?>
</body>

</html>