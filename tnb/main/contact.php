<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$successMessage = flash('success_message');
$errorMessage = flash('error_message');
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
    <title>ติดต่อเรา | TNB Logistics</title>
    <meta name="description" content="ติดต่อ TNB Logistics สำหรับบริการขนส่งและโลจิสติกส์ครบวงจร ที่อยู่ เบอร์โทรศัพท์ และอีเมล" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/contact.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/contact.html" />
    <meta property="og:title" content="ติดต่อเรา | TNB Logistics" />
    <meta property="og:description" content="ติดต่อ TNB Logistics สำหรับบริการขนส่งและโลจิสติกส์ครบวงจร ที่อยู่ เบอร์โทรศัพท์ และอีเมล" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />

    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Contact Page" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-contact: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-contact">
    <?php include '../component/menubar.php'; ?>

    <!-- หัวข้อหลัก — Blue gradient header เหมือน technology.php -->
    <div class="card-ui-header layout_padding">
        <div class="container">
            <h1 class="card-ui-main-title" data-i18n="contact.title">ติดต่อเรา</h1>
            <p class="card-ui-main-desc" data-i18n="contact.intro">เราพร้อมให้คำปรึกษาและบริการด้านโลจิสติกส์แบบครบวงจร ทันสมัย และตอบโจทย์ทุกความต้องการทางธุรกิจ
                ติดต่อสอบถามข้อมูลเพิ่มเติมได้ผ่านช่องทางด้านล่าง</p>
        </div>
    </div>

    <!-- เนื้อหาหลัก -->
    <section class="contact-section">
        <div class="container">

            <!-- รายละเอียดการติดต่อ -->
            <div class="contact-info-row">

                <!-- ที่อยู่สำนักงาน -->
                <div class="contact-item">
                    <div class="contact-icon">
                        <!-- SVG แผนที่ -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="contact-text-group">
                        <h4 data-i18n="contact.address_label">ที่อยู่สำนักงาน</h4>
                        <p data-i18n="contact.address">18/2 หมู่ที่ 5 ตำบลเหมือง อำเภอเมืองชลบุรี จังหวัดชลบุรี 20130</p>
                    </div>
                </div>

                <!-- ฝ่ายขาย -->
                <div class="contact-item">
                    <div class="contact-icon">
                        <!-- SVG โทรศัพท์ -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="contact-text-group">
                        <h4 data-i18n="contact.phone_label">ติดต่อฝ่ายขาย</h4>
                        <p>081-5758823<br>062-6392499</p>
                    </div>
                </div>

                <!-- อีเมล -->
                <div class="contact-item">
                    <div class="contact-icon">
                        <!-- SVG อีเมล -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <div class="contact-text-group">
                        <h4 data-i18n="contact.email_label">อีเมล</h4>
                        <p>wachira.o@tnb-logistics.com</p>
                    </div>
                </div>

            </div>

            <!-- แผนที่ Google Maps -->
            <div class="contact-map-wrapper">
                <!-- พื้นหลังรูปทรงสีน้ำเงินด้านหลังแผนที่ -->
                <div class="map-bg-shape"></div>
                <div class="contact-map-box">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1941.5823673197542!2d100.972431!3d13.277644!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3102b5007148f2cf%3A0xfe03520168a8b47c!2sTNB%20Logistics!5e0!3m2!1sth!2sth!4v1772427453783!5m2!1sth!2sth"
                        frameborder="0" style="border:0; width: 100%; height: 100%; display: block;"
                        allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                </div>
            </div>

        </div>
    </section>

    <?php include '../component/footer.php'; ?>
</body>

</html>