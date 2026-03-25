<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$pdo = Database::connection();
$companyInfo = get_company_info($pdo, 'TNB');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เกี่ยวกับเรา | TNB Logistics</title>
    <meta name="description" content="เกี่ยวกับ TNB Logistics ประวัติความเป็นมา วิสัยทัศน์ และพันธกิจในการให้บริการโลจิสติกส์ครบวงจร" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/about/company.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/about/company.html" />
    <meta property="og:title" content="เกี่ยวกับเรา | TNB Logistics" />
    <meta property="og:description" content="เกี่ยวกับ TNB Logistics ประวัติความเป็นมา วิสัยทัศน์ และพันธกิจในการให้บริการโลจิสติกส์ครบวงจร" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />
    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Company Page (about/company.php)" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-company: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-company">
    <?php include '../component/menubar.php'; ?>

    <!-- Main Content -->

    <div class="content-section layout_padding" style="flex: 1; padding-top: 120px;">
        <div class="flex-row" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px;">
            <!-- Left: Image with custom red shape -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column;">
                <div class="company-image-wrap">
                    <div class="company-image-inner">
                        <img src="../img/company_logo/tnb_logo.webp" alt="Company Profile">
                    </div>
                </div>
            </div>
            <!-- Right: Details -->
            <div style="flex: 1; min-width: 300px; padding-bottom: 20px;">
                <h1 class="details-title" data-i18n="company.title">ข้อมูลบริษัท</h1>
                
                <div class="company-text-container" style="margin-top: 20px;">
                    <p class="details-desc">ก่อตั้งขึ้นเพื่อดำเนินธุรกิจด้าน<strong style="color: #0D2D6B;">โลจิสติกส์และการขนส่งแบบครบวงจร</strong> ครอบคลุมทั้งการขนส่งภายในประเทศและระหว่างประเทศ พร้อมให้บริการ<strong style="color: #0D2D6B;">โซลูชันด้านซัพพลายเชน</strong>ที่ตอบโจทย์ความต้องการของลูกค้าในภาคอุตสาหกรรมและองค์กรธุรกิจ</p>
                    <p class="details-desc">บริษัทได้พัฒนาขอบเขตการให้บริการอย่างต่อเนื่อง จากการให้บริการขนส่งสินค้า ไปสู่<strong style="color: #0D2D6B;">การบริหารจัดการโลจิสติกส์แบบครบวงจร</strong> ตั้งแต่การวางแผนขนส่ง การจัดการคลังสินค้า ไปจนถึงการติดตามและควบคุมกระบวนการขนส่งอย่างมีประสิทธิภาพ เพื่อรองรับความต้องการของอุตสาหกรรมที่หลากหลาย</p>
                    <p class="details-desc">ปัจจุบัน <strong style="color: #0D2D6B;">บริษัท ทีเอ็นบี โลจิสติกส์ จำกัด</strong> ดำเนินธุรกิจด้วยทิศทางที่ชัดเจน มุ่งเน้นการพัฒนาศักยภาพด้านการให้บริการและการขยายขีดความสามารถในการรองรับความต้องการของลูกค้าที่เติบโตอย่างต่อเนื่อง</p>
                    <p class="details-desc">ด้วยแนวคิดการพัฒนาอย่างต่อเนื่อง <strong style="color: #0D2D6B;">TNB</strong> มุ่งเน้นการนำ<strong style="color: #0D2D6B;">เทคโนโลยีที่ทันสมัย</strong>มาใช้ในกระบวนการให้บริการ ไม่ว่าจะเป็น<strong style="color: #0D2D6B;">ระบบติดตามงานแบบเรียลไทม์</strong> และการบริหารจัดการข้อมูล เพื่อเพิ่มความแม่นยำ ความรวดเร็ว และความปลอดภัยในการขนส่ง พร้อมส่งมอบบริการที่มีคุณภาพ ตรงต่อเวลา และสร้างความเชื่อมั่นให้กับลูกค้าอย่างยั่งยืน</p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>