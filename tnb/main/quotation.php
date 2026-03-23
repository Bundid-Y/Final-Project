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
                                <label><span data-i18n="quotation.productType">Items inside your box
                                        (ประเภทสินค้าที่บรรจุ)</span> <span class="required">*</span></label>
                                <select name="product_type" required>
                                    <option value="" disabled selected data-i18n="quotation.productTypeSelect">
                                        เลือกประเภทสินค้าที่บรรจุ</option>
                                    <option value="Engine Parts" data-i18n="quotation.ptEngine">ชิ้นส่วนเครื่องยนต์
                                        (Engine Parts)</option>
                                    <option value="Body Parts" data-i18n="quotation.ptBody">ชิ้นส่วนตัวถัง (Body Parts)
                                    </option>
                                    <option value="Suspension and Transmission" data-i18n="quotation.ptSuspension">
                                        ชิ้นส่วนช่วงล่างและระบบส่งกำลัง
                                        (Suspension & Transmission)</option>
                                    <option value="Electrical Parts" data-i18n="quotation.ptElectrical">
                                        ชิ้นส่วนระบบไฟฟ้า (Electrical Parts)</option>
                                    <option value="Car Accessories" data-i18n="quotation.ptAccessories">
                                        อุปกรณ์ตกแต่งรถยนต์ (Car Accessories)</option>
                                    <option value="Other Auto Parts" data-i18n="quotation.ptOther">ชิ้นส่วนรถยนต์อื่นๆ
                                        (Other Auto Parts)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.weight">Total Weight (น้ำหนักสินค้า)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="weight" data-i18n-placeholder="quotation.weightPlaceholder"
                                    placeholder="น้ำหนักรวมสินค้า (กก.) ที่บรรจุภายในกล่อง" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.brand">Brand name (ชื่อแบรนด์)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="brand" data-i18n-placeholder="quotation.brandPlaceholder"
                                    placeholder="Mazda, Toyota, Honda, Isuzu, BMW" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.packagingType">Packaging Type
                                        (ประเภทบรรจุภัณฑ์)</span> <span class="required">*</span></label>
                                <select name="packaging_type" required>
                                    <option value="" disabled selected data-i18n="quotation.pkgSelect">
                                        เลือกประเภทบรรจุภัณฑ์</option>
                                    <option value="Paper Box" data-i18n="quotation.pkgPaper">กล่องกระดาษ (Paper Box)
                                    </option>
                                    <option value="Wooden Packaging" data-i18n="quotation.pkgWooden">บรรจุภัณฑ์ไม้
                                        (Wooden Packaging)</option>
                                    <option value="Plastic Packaging" data-i18n="quotation.pkgPlastic">บรรจุภัณฑ์พลาสติก
                                        (Plastic Packaging)</option>
                                    <option value="Steel Packaging" data-i18n="quotation.pkgSteel">บรรจุภัณฑ์เหล็ก
                                        (Steel Packaging)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.boxSize">Box Size (ขนาดกล่องที่ต้องการ)</span> <span
                                        class="required">*</span></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" step="0.01" name="box_width"
                                        data-i18n-placeholder="quotation.width" placeholder="กว้าง" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="box_length"
                                        data-i18n-placeholder="quotation.length" placeholder="ยาว" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="box_height"
                                        data-i18n-placeholder="quotation.height" placeholder="สูง" required
                                        style="min-width: 0;">
                                    <select name="box_unit" required style="min-width: 70px; padding: 10px 8px;">
                                        <option value="cm" data-i18n="quotation.unitCm">ซม.</option>
                                        <option value="mm" data-i18n="quotation.unitMm">มม.</option>
                                        <option value="inch" data-i18n="quotation.unitInch">นิ้ว</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.quantity">Order quantity (จำนวนสั่งซื้อ)</span> <span
                                        class="required">*</span></label>
                                <input type="number" name="quantity" min="1" required>
                            </div>
                        </div>

                        <div class="form-row row-1">
                            <div class="form-group file-group">
                                <label data-i18n="quotation.reference">Reference (แนบไฟล์ตัวอย่างอ้างอิงที่ชอบ)</label>
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
                                <label><span data-i18n="quotation.comments">Other Comments
                                        (อื่นที่ต้องการแจ้งทีมงาน)</span> <span class="required">*</span></label>
                                <textarea name="comments" rows="5" required></textarea>
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