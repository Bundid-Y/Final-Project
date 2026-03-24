<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ขอใบเสนอราคา | TNB Logistics</title>
    <meta name="description" content="ขอใบเสนอราคาบริการขนส่งและโลจิสติกส์ครบวงจรจาก TNB Logistics สำหรับอุตสาหกรรมยานยนต์และอุตสาหกรรมที่เกี่ยวข้อง" />

     <!-- Google SEO -->
     <meta name="robots" content="index, follow" />
     <link rel="canonical" href="https://tnb-logistics.com/quotation.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/quotation.html" />
    <meta property="og:title" content="ขอใบเสนอราคา | TNB Logistics" />
    <meta property="og:description" content="ขอใบเสนอราคาบริการขนส่งและโลจิสติกส์ครบวงจรจาก TNB Logistics สำหรับอุตสาหกรรมยานยนต์และอุตสาหกรรมที่เกี่ยวข้อง" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />

    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Quotation Page (quotation.php)" -->
    
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<!-- page-quotation: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-quotation">
    <?php include '../component/menubar.php'; ?>

    <main>
        <section class="quotation-section layout_padding"
            style="padding-top: 60px; padding-bottom: 60px; background-color: #f9f9f9;">
            <div class="container" style="max-width: 900px; margin: 0 auto; padding: 0 20px;">
                <div class="quotation-card">
                    <h2 class="form-title" data-i18n="quotation.title">ขอใบเสนอราคา</h2>
                    <form action="#" method="POST" enctype="multipart/form-data" class="quotation-form">

                        <div class="form-row row-3">
                            <div class="form-group">
                                <label><span data-i18n="quotation.firstName">First Name (ชื่อ)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.lastName">Last Name (นามสกุล)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.nickName">Nick name (ชื่อเล่น)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="nick_name" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.phone">Mobile Phone Number (เบอร์มือถือ)</span> <span
                                        class="required">*</span></label>
                                <input type="tel" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.email">Email</span> <span
                                        class="required">*</span></label>
                                <input type="email" name="email" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.productType">Logistics Service Type (ประเภทบริการขนส่ง)</span> <span class="required">*</span></label>
                                <select name="service_type" required>
                                    <option value="" disabled selected data-i18n="quotation.productTypeSelect">
                                        เลือกประเภทบริการขนส่ง</option>
                                    <option value="Container Transport" data-i18n="quotation.ptContainer">บริการขนส่งตู้คอนเทนเนอร์
                                        (Container Transport)</option>
                                    <option value="Domestic Transport" data-i18n="quotation.ptDomestic">บริการขนส่งสินค้าในประเทศ
                                        (Domestic Transport)</option>
                                    <option value="Import Export" data-i18n="quotation.ptImportExport">บริการนำเข้า-ส่งออก
                                        (Import-Export Service)</option>
                                    <option value="Warehouse Shuttle" data-i18n="quotation.ptShuttle">บริการรถรับ-ส่งระหว่างคลังสินค้า
                                        (Warehouse Shuttle)</option>
                                    <option value="Truck Parking" data-i18n="quotation.ptParking">บริการจอดรถบรรทุก
                                        (Truck Parking)</option>
                                    <option value="Last Mile Delivery" data-i18n="quotation.ptLastMile">บริการจัดส่งปลายทาง
                                        (Last Mile Delivery)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.weight">Cargo Weight (น้ำหนักสินค้าโดยประมาณ)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="weight" data-i18n-placeholder="quotation.weightPlaceholder"
                                    placeholder="น้ำหนักสินค้าโดยประมาณ (กก.)" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.brand">Origin - Destination (จุดเริ่มต้น - จุดหมายปลายทาง)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="route" data-i18n-placeholder="quotation.brandPlaceholder"
                                    placeholder="เช่น กรุงเทพฯ - เชียงใหม่ หรือ ท่าเรือแหลมฉบัง - โรงงาน" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.packagingType">Vehicle Type Required (ประเภทรถที่ต้องการ)</span> <span class="required">*</span></label>
                                <select name="vehicle_type" required>
                                    <option value="" disabled selected data-i18n="quotation.pkgSelect">
                                        เลือกประเภทรถบรรทุก</option>
                                    <option value="6 Wheel Truck" data-i18n="quotation.pkg6Wheel">รถบรรทุก 6 ล้อ
                                        (6 Wheel Truck)</option>
                                    <option value="10 Wheel Truck" data-i18n="quotation.pkg10Wheel">รถบรรทุก 10 ล้อ
                                        (10 Wheel Truck)</option>
                                    <option value="6 Wheel Trailer" data-i18n="quotation.pkg6WheelTrailer">รถพ่วง 6 ล้อ
                                        (6 Wheel Trailer)</option>
                                    <option value="10 Wheel Trailer" data-i18n="quotation.pkg10WheelTrailer">รถพ่วง 10 ล้อ
                                        (10 Wheel Trailer)</option>
                                    <option value="Pickup Truck" data-i18n="quotation.pkgPickup">รถกระบะ
                                        (Pickup Truck)</option>
                                    <option value="Container Truck" data-i18n="quotation.pkgContainer">รถลากตู้คอนเทนเนอร์
                                        (Container Truck)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.boxSize">Cargo Dimensions (ขนาดสินค้าโดยประมาณ)</span> <span
                                        class="required">*</span></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" step="0.01" name="cargo_width"
                                        data-i18n-placeholder="quotation.width" placeholder="กว้าง" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="cargo_length"
                                        data-i18n-placeholder="quotation.length" placeholder="ยาว" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="cargo_height"
                                        data-i18n-placeholder="quotation.height" placeholder="สูง" required
                                        style="min-width: 0;">
                                    <select name="cargo_unit" required style="min-width: 70px; padding: 10px 8px;">
                                        <option value="cm" data-i18n="quotation.unitCm">ซม.</option>
                                        <option value="mm" data-i18n="quotation.unitMm">มม.</option>
                                        <option value="inch" data-i18n="quotation.unitInch">นิ้ว</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.quantity">Quantity for Transport (จำนวนที่ต้องการขนส่ง)</span> <span
                                        class="required">*</span></label>
                                <input type="number" name="quantity" min="1" required>
                            </div>
                        </div>

                        <div class="form-row row-1">
                            <div class="form-group file-group">
                                <label data-i18n="quotation.reference">Attach Reference Files (แนบไฟล์ข้อมูลเพิ่มเติม)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="reference_file" id="reference_file" class="file-input"
                                        accept="image/*, .pdf">
                                    <span class="file-hint" data-i18n="quotation.fileHint">Max. file size: 128
                                        MB.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row row-1">
                            <div class="form-group">
                                <label><span data-i18n="quotation.comments">Additional Requirements (รายละเอียดเพิ่มเติม)</span> <span class="required">*</span></label>
                                <textarea name="comments" rows="5" data-i18n-placeholder="quotation.commentsPlaceholder" placeholder="กรุณาระบุรายละเอียดเพิ่มเติมเกี่ยวกับการขนส่ง เช่น เวลาที่ต้องการ จุดรับส่งพิเศษ หรือข้อกำหนดอื่นๆ" required></textarea>
                            </div>
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="submit-btn"
                                data-i18n="quotation.submitBtn">ส่งข้อมูลขอใบเสนอราคา</button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </main>
    <?php include '../component/footer.php'; ?>
</body>

</html>