<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$pdo = Database::connection();
$companyInfo = get_company_info($pdo, 'KOCH');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koch Packaging</title>

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
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Company Page (about/company.php)" -->
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<!-- page-company: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-company">
    <?php include '../component/menubar.php'; ?>

    <!-- Main Content -->

    <div class="content-section layout_padding" style="margin-top: 0; padding-top: 100px; flex: 1;">
        <div class="flex-row" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px;">
            <!-- Left: Image with custom red shape -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column;">
                <div class="company-image-wrap">
                    <div class="company-image-inner">
                        <img src="../img/company_logo/logo.png" alt="Company Profile">
                    </div>
                </div>
            </div>
            <!-- Right: Details — text-align left กำหนดผ่าน CSS .details-desc -->
            <div style="flex: 1; min-width: 300px; padding-bottom: 20px;">
                <h1 class="details-title" data-i18n="company.title">ข้อมูลบริษัท</h1>

                <div class="company-text-container" style="margin-top: 20px;">
                    <!-- Paragraph 1: ชื่อบริษัท + คำอธิบาย -->
                    <p class="details-desc" style="text-align: left !important; text-indent: 0 !important; line-height: 1.8; margin-bottom: 20px;">
                        <span data-i18n-html="company.p1">ดำเนินธุรกิจด้านการผลิตและให้บริการ<strong class="company-dark-highlight">โซลูชันบรรจุภัณฑ์และซัพพลายเชนครบวงจร</strong> ตั้งแต่การออกแบบ การจัดหาวัตถุดิบ การผลิต ไปจนถึงการบริหารคลังสินค้าและโลจิสติกส์</span>
                    </p>
                    <!-- Paragraph 2: ขอบเขตบริการ -->
                    <p class="details-desc" style="text-align: left !important; text-indent: 0 !important; line-height: 1.8; margin-bottom: 20px;">
                        <span data-i18n-html="company.p2">เริ่มต้นจากการผลิตและจำหน่าย<strong class="company-dark-highlight">บรรจุภัณฑ์กระดาษลูกฟูกและกระดาษแข็ง</strong> ซึ่งเป็นหัวใจของการปกป้องสินค้าในการขนส่งและจัดเก็บ บริษัทได้พัฒนาขอบเขตบริการครอบคลุมทั้ง <strong class="company-dark-highlight">ระบบบริหารบรรจุภัณฑ์ (VMI), การจัดการคลังสินค้า และการขนส่ง</strong> รวมถึงบริการโลจิสติกส์อื่นๆ ที่เหมาะกับ<strong class="company-dark-highlight">อุตสาหกรรมยานยนต์และอิเล็กทรอนิกส์</strong></span>
                    </p>
                    <!-- Paragraph 3: เทคโนโลยีและอนาคต -->
                    <p class="details-desc" style="text-align: left !important; text-indent: 0 !important; line-height: 1.8; margin-bottom: 20px;">
                        <span data-i18n-html="company.p3">ด้วยแนวคิดการพัฒนาอย่างต่อเนื่อง บริษัทมุ่งนำ<strong class="company-dark-highlight">เทคโนโลยีอัตโนมัติและการบริหารจัดการข้อมูล</strong>มาใช้ เพื่อให้บริการลูกค้าได้อย่าง<strong class="company-dark-highlight">แม่นยำ รวดเร็ว และมีมาตรฐานสูง</strong> พร้อมรองรับการเติบโตของธุรกิจทั้งในและต่างประเทศ</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>