<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

try {
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'TNB');
    $dbPartners = get_active_partners($pdo, $companyId);
} catch (Throwable $e) {
    $dbPartners = [];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พันธมิตรและลูกค้า | TNB Logistics</title>
    <meta name="description" content="พันธมิตรและลูกค้าที่ไว้วางใจ TNB Logistics ในอุตสาหกรรมยานยนต์และอุตสาหกรรมที่เกี่ยวข้อง" />

    <!-- Google SEO -->
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://tnb-logistics.com/partners.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/partners.html" />
    <meta property="og:title" content="พันธมิตรและลูกค้า | TNB Logistics" />
    <meta property="og:description" content="พันธมิตรและลูกค้าที่ไว้วางใจ TNB Logistics ในอุตสาหกรรมยานยนต์และอุตสาหกรรมที่เกี่ยวข้อง" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />

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
    <div class="partners-page-wrapper">
        <div class="cycle_section_3 layout_padding">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="cycles_text" data-i18n="partners.title">พันธมิตร</h1>

                        <!-- Paragraph 1 -->
                        <p class="partners-lead anim-fade-up" data-i18n-html="partners.desc_p1">
                            ตลอดระยะเวลาที่ผ่านมา บริษัท ทีเอ็นบี โลจิสติกส์ จำกัด ได้รับความไว้วางใจจากพันธมิตรและลูกค้าในหลากหลายอุตสาหกรรม
                        </p>

                        <!-- Paragraph 2 -->
                        <p class="partners-lead anim-fade-up" data-i18n-html="partners.desc_p2">
                            โดยเฉพาะกลุ่ม<strong class="partners-highlight">อุตสาหกรรมยานยนต์</strong> <strong class="partners-highlight">อิเล็กทรอนิกส์</strong> <strong class="partners-highlight">เคมีภัณฑ์</strong> และ<strong class="partners-highlight">สินค้าอุตสาหกรรม</strong> ทั้งผู้ผลิต (OEM) และ<strong class="partners-highlight">ซัพพลายเออร์ระดับ Tier1 และ Tier2</strong>
                        </p>

                        <!-- Paragraph 3 -->
                        <p class="partners-lead anim-fade-up" data-i18n-html="partners.desc_p3">
                            TNB ให้บริการขนส่งด้วยมาตรฐานที่เน้น<strong class="partners-highlight">คุณภาพ</strong> <strong class="partners-highlight">ความปลอดภัย</strong> และ<strong class="partners-highlight">ความตรงต่อเวลา</strong> พร้อมพัฒนาการทำงานร่วมกับลูกค้าอย่างใกล้ชิด
                        </p>

                        <!-- Paragraph 4 -->
                        <p class="partners-cta-text anim-fade-up" data-i18n-html="partners.desc_p4">
                            เพื่อสนับสนุนการเติบโตทางธุรกิจอย่างยั่งยืน และสร้างความสัมพันธ์ระยะยาวในฐานะ<strong class="partners-highlight">พันธมิตรด้านโลจิสติกส์ที่เชื่อถือได้</strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="right-graphic-container">
                            <div class="red-block">
                                <!-- Logo Loop Inside Red Block -->
                                <section class="loop-images-quotation vertical" style="background-color: transparent !important; --bg: transparent; height: 100%; min-height: 500px; padding: 20px 0; width: 100%;">
                                    <?php if (!empty($dbPartners)): $pCount = count($dbPartners); $totalItems = $pCount * 2; $topRem = max(40, $totalItems * 16 - 56); $animTime = max(9, $pCount * 3); ?>
                                    <div class="login-track vertical" style="--time: <?php echo $animTime; ?>s; --total: <?php echo $totalItems; ?>; --top: -<?php echo $topRem; ?>rem;">
                                        <?php $idx = 1; foreach ($dbPartners as $p): ?>
                                        <div class="login-item" style="--i: <?php echo $idx++; ?>;"><img src="<?php echo htmlspecialchars(resolve_image_url((string)$p['logo_url'])); ?>" alt="<?php echo htmlspecialchars((string)$p['name']); ?>"></div>
                                        <?php endforeach; ?>
                                        <?php foreach ($dbPartners as $p): ?>
                                        <div class="login-item" style="--i: <?php echo $idx++; ?>;"><img src="<?php echo htmlspecialchars(resolve_image_url((string)$p['logo_url'])); ?>" alt="<?php echo htmlspecialchars((string)$p['name']); ?>"></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="login-track vertical" style="--time: 40s; --total: 6; --top: -300rem;">
                                        <div class="login-item" style="--i: 1;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                        <div class="login-item" style="--i: 2;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                        <div class="login-item" style="--i: 3;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                        <div class="login-item" style="--i: 4;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                        <div class="login-item" style="--i: 5;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                        <div class="login-item" style="--i: 6;"><div style="width:120px;height:60px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:8px">No Partners</div></div>
                                    </div>
                                    <?php endif; ?>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>