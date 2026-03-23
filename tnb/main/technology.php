<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เทคโนโลยีและนวัตกรรมการขนส่ง | TNB Logistics</title>
    <meta name="description" content="เทคโนโลยีและนวัตกรรมของ TNB Logistics ระบบ TMS, Geofencing, Camera Control เพื่อยกระดับการขนส่ง" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/technology.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/technology.html" />
    <meta property="og:title" content="เทคโนโลยีและนวัตกรรมการขนส่ง | TNB Logistics" />
    <meta property="og:description" content="เทคโนโลยีและนวัตกรรมของ TNB Logistics ระบบ TMS, Geofencing, Camera Control เพื่อยกระดับการขนส่ง" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />
    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Technology Page (technology.php)" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-technology: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-technology">
    <?php include '../component/menubar.php'; ?>

    <!-- หัวข้อหลัก -->
    <div class="card-ui-header layout_padding">
        <div class="container">
            <h1 class="card-ui-main-title" data-i18n="technology.title">เทคโนโลยีของเรา</h1>
            <p class="card-ui-main-desc" data-i18n="technology.subtitle">นวัตกรรมและระบบอัจฉริยะที่ช่วยยกระดับประสิทธิภาพการจัดการซัพพลายเชนของคุณ</p>
        </div>
    </div>

    <!-- Card Grid Section -->
    <section class="card-ui-section">
        <div class="container">
            <div class="card-ui-grid">

                <!-- Card 1: TMS System -->
                <div class="card-ui-item" data-modal="modal-tms">
                    <div class="card-ui-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <h3 class="card-ui-title" data-i18n="technology.tab_tms">TMS</h3>
                    <p class="card-ui-desc" data-i18n="technology.tms_card_desc">ระบบบริหารจัดการงานขนส่งแบบครบวงจร ติดตามสถานะแบบเรียลไทม์ผ่าน GPS</p>
                    <span class="card-ui-more" data-i18n="technology.cardMore">คลิกเพื่อดูรายละเอียด →</span>
                </div>

                <!-- Card 2: Truck Monitoring -->
                <div class="card-ui-item" data-modal="modal-wms">
                    <div class="card-ui-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <h3 class="card-ui-title" data-i18n="technology.tab_wms">Truck Monitoring</h3>
                    <p class="card-ui-desc" data-i18n="technology.wms_card_desc">ระบบติดตามและแสดงผลการทำงานของรถขนส่งผ่าน Dashboard กลาง</p>
                    <span class="card-ui-more" data-i18n="technology.cardMore">คลิกเพื่อดูรายละเอียด →</span>
                </div>

                <!-- Card 3: Geofencing -->
                <div class="card-ui-item" data-modal="modal-geo">
                    <div class="card-ui-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    </div>
                    <h3 class="card-ui-title" data-i18n="technology.tab_geo">Geofencing</h3>
                    <p class="card-ui-desc" data-i18n="technology.geo_card_desc">ระบบกำหนดพื้นที่ปลอดภัย ติดตามการเคลื่อนที่ของรถในเขตที่กำหนด</p>
                    <span class="card-ui-more" data-i18n="technology.cardMore">คลิกเพื่อดูรายละเอียด →</span>
                </div>

                <!-- Card 4: Camera Control -->
                <div class="card-ui-item" data-modal="modal-cam">
                    <div class="card-ui-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <h3 class="card-ui-title" data-i18n="technology.tab_cam">Camera Control</h3>
                    <p class="card-ui-desc" data-i18n="technology.cam_card_desc">ระบบควบคุมกล้องวงจรปิดรถบรรทุก เพิ่มความปลอดภัยในการขนส่ง</p>
                    <span class="card-ui-more" data-i18n="technology.cardMore">คลิกเพื่อดูรายละเอียด →</span>
                </div>

            </div>
        </div>
    </section>

    <!-- ==================== MODALS ==================== -->

    <!-- Modal 1: TMS System -->
    <div class="modal-ui-overlay" id="modal-tms">
        <div class="modal-ui-container">
            <button class="modal-ui-close" aria-label="ปิด">&times;</button>
            <div class="modal-ui-body">
                <div class="modal-ui-two-col">
                    <div class="modal-ui-col-img">
                        <img src="../img/other/technology/tms1.png" alt="TMS" loading="lazy">
                    </div>
                    <div class="modal-ui-col-text">
                        <h2 class="modal-ui-title" data-i18n="technology.tab_tms">TMS</h2>
                        <p class="modal-ui-subtitle" data-i18n="technology.tms_subtitle">Transportation Management System</p>
                        <p class="modal-ui-desc" data-i18n="technology.tms_desc">ระบบบริหารจัดการงานขนส่งแบบครบวงจร ติดตามสถานะแบบเรียลไทม์ผ่าน GPS</p>
                        <ul class="modal-ui-list">
                            <li data-i18n="technology.tms_li1"><strong>Route Optimization:</strong> ระบบคำนวณเส้นทางที่เหมาะสมที่สุดเพื่อลดระยะทางและเวลาในการขนส่ง</li>
                            <li data-i18n="technology.tms_li2"><strong>Real-time Tracking:</strong> ติดตามสถานะการขนส่งแบบเรียลไทม์ผ่าน GPS</li>
                            <li data-i18n="technology.tms_li3"><strong>Automated Reporting:</strong> ระบบสร้างรายงานการขนส่งอัตโนมัติ</li>
                            <li data-i18n="technology.tms_li4"><strong>Integration with Other Systems:</strong> สามารถเชื่อมต่อกับระบบอื่นๆ ของบริษัทได้อย่างง่ายดาย</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Truck Monitoring -->
    <div class="modal-ui-overlay" id="modal-wms">
        <div class="modal-ui-container">
            <button class="modal-ui-close" aria-label="ปิด">&times;</button>
            <div class="modal-ui-body">
                <div class="modal-ui-two-col">
                    <div class="modal-ui-col-img">
                        <img src="../img/other/technology/wms.jpeg" alt="Truck Monitoring" loading="lazy">
                    </div>
                    <div class="modal-ui-col-text">
                        <h2 class="modal-ui-title" data-i18n="technology.tab_wms">Truck Monitoring</h2>
                        <p class="modal-ui-subtitle" data-i18n="technology.wms_subtitle">Truck Monitoring System</p>
                        <p class="modal-ui-desc" data-i18n="technology.wms_desc">ระบบติดตามและแสดงผลการทำงานของรถขนส่งผ่าน Dashboard กลาง</p>
                        <ul class="modal-ui-list">
                            <li data-i18n="technology.wms_li1"><strong>Real-time Tracking:</strong> ติดตามสถานะการขนส่งแบบเรียลไทม์ผ่าน GPS</li>
                            <li data-i18n="technology.wms_li2"><strong>Automated Reporting:</strong> ระบบสร้างรายงานการขนส่งอัตโนมัติ</li>
                            <li data-i18n="technology.wms_li3"><strong>Alert System:</strong> ระบบแจ้งเตือนเมื่อมีการเปลี่ยนแปลงสถานะการขนส่ง</li>
                            <li data-i18n="technology.wms_li4"><strong>Integration with Other Systems:</strong> สามารถเชื่อมต่อกับระบบอื่นๆ ของบริษัทได้อย่างง่ายดาย</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 3: Geofencing -->
    <div class="modal-ui-overlay" id="modal-geo">
        <div class="modal-ui-container">
            <button class="modal-ui-close" aria-label="ปิด">&times;</button>
            <div class="modal-ui-body">
                <div class="modal-ui-two-col">
                    <div class="modal-ui-col-img">
                        <img src="../img/other/technology/geofencing.jpeg" alt="Geofencing" loading="lazy">
                    </div>
                    <div class="modal-ui-col-text">
                        <h2 class="modal-ui-title" data-i18n="technology.tab_geo">Geofencing</h2>
                        <p class="modal-ui-subtitle" data-i18n="technology.geo_subtitle">Geofencing & Line Auto Alert System</p>
                        <p class="modal-ui-desc" data-i18n="technology.geo_desc">TNB ใช้เทคโนโลยี Geofencing เพื่อกำหนดขอบเขตพื้นที่สำคัญ เช่น ท่าเรือ คลังสินค้า หรือโรงงานลูกค้า</p>
                        <ul class="modal-ui-list">
                            <li data-i18n="technology.geo_li1"><strong>Automatic Boundary Detection:</strong> เมื่อรถขนส่งผ่านเข้า–ออกพื้นที่ที่กำหนด ระบบจะบันทึกข้อมูลเวลาและสถานะโดยอัตโนมัติ</li>
                            <li data-i18n="technology.geo_li2"><strong>Real-time Notifications:</strong> เชื่อมต่อกับระบบแจ้งเตือนผ่าน Line Auto Alert เพื่อแจ้งข้อมูลแบบเรียลไทม์</li>
                            <li data-i18n="technology.geo_li3"><strong>Time Control:</strong> ช่วยควบคุมเวลาในการขนส่ง ลดความเสี่ยงจากความล่าช้า</li>
                            <li data-i18n="technology.geo_li4"><strong>Transparency & Tracking:</strong> เพิ่มความโปร่งใสในการติดตามตู้สินค้าตลอดเวลา</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 4: Camera Control -->
    <div class="modal-ui-overlay" id="modal-cam">
        <div class="modal-ui-container">
            <button class="modal-ui-close" aria-label="ปิด">&times;</button>
            <div class="modal-ui-body">
                <div class="modal-ui-two-col">
                    <div class="modal-ui-col-img">
                        <img src="../img/other/technology/cameracontrol.jpeg" alt="Camera Control" loading="lazy">
                    </div>
                    <div class="modal-ui-col-text">
                        <h2 class="modal-ui-title" data-i18n="technology.tab_cam">Camera Control</h2>
                        <p class="modal-ui-subtitle" data-i18n="technology.cam_subtitle">Camera Control & Driver Behavior Monitoring</p>
                        <p class="modal-ui-desc" data-i18n="technology.cam_desc">ระบบกล้องตรวจสอบของ TNB ถูกติดตั้งทั้งบริเวณด้านหน้ารถ ภายในตู้สินค้า และจุดตรวจสอบพฤติกรรมพนักงานขับรถ</p>
                        <ul class="modal-ui-list">
                            <li data-i18n="technology.cam_li1"><strong>Live Monitoring:</strong> สามารถดูภาพสดแบบออนไลน์ บันทึกข้อมูล และเรียกดูภาพย้อนหลังได้</li>
                            <li data-i18n="technology.cam_li2"><strong>Safety Enhancement:</strong> ช่วยเพิ่มความปลอดภัยในการขนส่งสินค้า ป้องกันความเสียหายและลดความเสี่ยงจากอุบัติเหตุ</li>
                            <li data-i18n="technology.cam_li3"><strong>Driver Behavior Analysis:</strong> ใช้เป็นเครื่องมือในการควบคุมมาตรฐานการขับขี่ วิเคราะห์พฤติกรรมการทำงานของพนักงาน</li>
                            <li data-i18n="technology.cam_li4"><strong>Service Quality Control:</strong> ยกระดับคุณภาพการให้บริการให้เป็นไปตามมาตรฐานของบริษัท</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; ?>
</body>

</html>