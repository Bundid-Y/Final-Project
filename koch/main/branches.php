<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

try {
    $pdo = Database::connection();
    $companyId = get_company_id_by_code($pdo, 'KOCH');
    $companyInfo = get_company_info($pdo, 'KOCH');
} catch (Throwable $e) {
    $companyInfo = null;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koch Packaging - Branches</title>

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

    <!-- Hero & Branches Section -->
    <div class="branches_section layout_padding">
        <div class="container">
            <h1 class="branches_title" data-i18n="branches.title">สาขาโรงงานของเรา</h1>
            <p class="branches_desc" data-i18n="branches.desc">
                ยกระดับมาตรฐานการผลิตและระบบบริหารคลังสินค้าในระดับสากลเพื่อตอบสนองความต้องการของลูกค้า</p>

            <!-- Branch 1: Ban Bueng (Image Left, Text Right) -->
            <div class="branch_block_1">
                <div class="row">
                    <div class="col-md-6">
                        <div class="branch_img_container">
                            <div class="branch_image"><img src="../img/other/branches/banbueng.png" alt="สาขาบ้านบึง">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="branch_subtitle" data-i18n="branches.banbuengTitle">สาขาบ้านบึง (Ban Bueng)</h2>
                        <p class="branch_text" data-i18n="branches.banbuengDesc">
                            ฐานการผลิตหลักสำหรับบรรจุภัณฑ์กระดาษลูกฟูก (Carton Box) ด้วยพนักงานที่เชี่ยวชาญกว่า 120 ท่าน
                            พร้อมรองรับปริมาณการผลิตขนาดใหญ่</p>
                        <ul class="branch_list">
                            <li><strong data-i18n="branches.banbueng1Topic">พื้นที่บริการ:</strong> <span
                                    data-i18n="branches.banbueng1Text">1,600 ตารางเมตร</span></li>
                            <li><strong data-i18n="branches.banbueng2Topic">จุดเด่น:</strong> <span
                                    data-i18n="branches.banbueng2Text">เน้นการให้บริการด้านบรรจุภัณฑ์ (Packaging
                                    Services) ครบวงจร</span></li>
                            <li><strong data-i18n="branches.banbueng3Topic">กระบวนการ:</strong> <span
                                    data-i18n="branches.banbueng3Text">ควบคุมคุณภาพการผลิตตามมาตรฐานอุตสาหกรรมในทุกขั้นตอน</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Branch 2: Bowin (Text Left, Image Right) -->
            <div class="branch_block_2">
                <div class="row mobile-reverse-row">
                    <div class="col-md-6">
                        <h2 class="branch_subtitle" data-i18n="branches.bowinTitle">สาขาบ่อวิน (Bowin)</h2>
                        <p class="branch_text" data-i18n="branches.bowinDesc">
                            สาขาใหม่ที่ถูกออกแบบมาเพื่อรองรับการขยายตัวในนิคมอุตสาหกรรมบ่อวิน
                            เน้นบริการคลังสินค้าและการจัดการระบบ VMI ให้ใกล้ชิดกับโรงงานลูกค้ามากขึ้น</p>
                        <ul class="branch_list">
                            <li><strong data-i18n="branches.bowin1Topic">พื้นที่บริการ:</strong> <span
                                    data-i18n="branches.bowin1Text">5,000 ตารางเมตร</span></li>
                            <li><strong data-i18n="branches.bowin2Topic">จุดเด่น:</strong> <span
                                    data-i18n="branches.bowin2Text">เน้นการดำเนินงานด้านระบบคลังสินค้า (Warehouse
                                    Operations) อย่างเต็มรูปแบบ</span></li>
                            <li><strong data-i18n="branches.bowin3Topic">ระบบที่ใช้:</strong> <span
                                    data-i18n="branches.bowin3Text">นำระบบบริหารคลังสินค้า (WMS) และ VMI
                                    มาประยุกต์ใช้เพื่อความรวดเร็วและแม่นยำ</span></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="branch_img_container_right">
                            <div class="branch_image"><img src="../img/other/branches/bowin.png" alt="สาขาบ่อวิน"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>