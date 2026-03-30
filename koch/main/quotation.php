<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';

$loggedInUser = authenticated_user();
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koch Packaging - Quotation</title>

    <!-- Google SEO -->
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://koch-packaging-services.com/expertise.html" />

    <!-- Facebook Open Graph -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="th_TH" />
    <meta property="og:site_name" content="KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:title" content="ความเชี่ยวชาญของเรา - KOCH Packaging and Packing Services Co.,Ltd" />
    <meta property="og:description"
        content="ความเชี่ยวชาญของ KOCH - ยกระดับการจัดการซัพพลายเชนสู่อนาคต ด้วยความเชี่ยวชาญระดับมืออาชีพ" />
    <meta property="og:url" content="https://koch-packaging-services.com/expertise.html" />
    <meta property="og:image"
        content="https://koch-packaging-services.com/scr/assets/carousel/company/Gemini_Generated_Image_o9ab0wo9.png" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/logo 2.png" />

    <!-- Custom CSS & JS -->
    <!-- CSS ของหน้านี้อยู่ใน: css/style.css หัวข้อ "Quotation Page (quotation.php)" -->
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>

<!-- page-quotation: ใช้ scope CSS ให้เฉพาะหน้านี้ ป้องกันไม่ให้กระทบหน้าอื่น -->

<body class="page-quotation">
    <?php include '../component/menubar.php'; ?>

    <main>
        <section class="quotation-section layout_padding"
            style="padding-top: 60px; padding-bottom: 60px; background-color: #f9f9f9;">
            <div class="container" style="max-width: 900px; margin: 0 auto; padding: 0 20px;">
                <div class="quotation-card">
                    <?php if ($successMessage || $errorMessage): ?>
                        <div
                            style="margin-bottom: 16px; padding: 14px 16px; border-radius: 14px; background: <?php echo $successMessage ? '#e8f7ee' : '#fdecec'; ?>; color: <?php echo $successMessage ? '#0f7a3a' : '#b42318'; ?>;">
                            <?php echo h((string) ($successMessage ?: $errorMessage)); ?>
                        </div>
                    <?php endif; ?>
                    <h2 class="form-title" data-i18n="quotation.title">ขอใบเสนอราคา</h2>
                    <form action="https://formspree.io/f/xreoqzve" method="POST" enctype="multipart/form-data"
                        class="quotation-form">
                        <input type="hidden" name="_subject" value="ขอใบเสนอราคา (KOCH)" />
                        <input type="hidden" name="บริษัท" value="KOCH Packaging and Packing Services Co.,Ltd" />
                        <input type="text" name="_gotcha" style="display:none" />

                        <div class="form-row row-3">
                            <div class="form-group">
                                <label><span data-i18n="quotation.firstName">First Name (ชื่อ)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="01. ชื่อจริง (First Name)"
                                    value="<?php echo h(old_input('first_name', (string) ($loggedInUser['first_name'] ?? ''))); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.lastName">Last Name (นามสกุล)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="02. นามสกุล (Last Name)"
                                    value="<?php echo h(old_input('last_name', (string) ($loggedInUser['last_name'] ?? ''))); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.nickName">Nick name (ชื่อเล่น)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="03. ชื่อเล่น (Nickname)"
                                    value="<?php echo h(old_input('nick_name', (string) ($loggedInUser['nick_name'] ?? ''))); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.phone">Mobile Phone Number (เบอร์มือถือ)</span> <span
                                        class="required">*</span></label>
                                <input type="tel" name="04. เบอร์โทรศัพท์ (Phone)"
                                    value="<?php echo h(old_input('phone', (string) ($loggedInUser['phone'] ?? ''))); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.email">Email</span> <span
                                        class="required">*</span></label>
                                <input type="email" name="email"
                                    value="<?php echo h(old_input('email', (string) ($loggedInUser['email'] ?? ''))); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.productType">Items inside your box
                                        (ประเภทสินค้าที่บรรจุ)</span> <span class="required">*</span></label>
                                <select name="06. ประเภทสินค้า (Product Type)" required>
                                    <option value="" disabled <?php echo old_input('product_type') === '' ? 'selected' : ''; ?> data-i18n="quotation.productTypeSelect">
                                        เลือกประเภทสินค้าที่บรรจุ</option>
                                    <option value="Engine Parts" <?php echo old_input('product_type') === 'Engine Parts' ? 'selected' : ''; ?> data-i18n="quotation.ptEngine">ชิ้นส่วนเครื่องยนต์
                                        (Engine Parts)</option>
                                    <option value="Body Parts" <?php echo old_input('product_type') === 'Body Parts' ? 'selected' : ''; ?> data-i18n="quotation.ptBody">ชิ้นส่วนตัวถัง (Body Parts)
                                    </option>
                                    <option value="Suspension and Transmission" <?php echo old_input('product_type') === 'Suspension and Transmission' ? 'selected' : ''; ?>
                                        data-i18n="quotation.ptSuspension">
                                        ชิ้นส่วนช่วงล่างและระบบส่งกำลัง
                                        (Suspension & Transmission)</option>
                                    <option value="Electrical Parts" <?php echo old_input('product_type') === 'Electrical Parts' ? 'selected' : ''; ?> data-i18n="quotation.ptElectrical">
                                        ชิ้นส่วนระบบไฟฟ้า (Electrical Parts)</option>
                                    <option value="Car Accessories" <?php echo old_input('product_type') === 'Car Accessories' ? 'selected' : ''; ?> data-i18n="quotation.ptAccessories">
                                        อุปกรณ์ตกแต่งรถยนต์ (Car Accessories)</option>
                                    <option value="Other Auto Parts" <?php echo old_input('product_type') === 'Other Auto Parts' ? 'selected' : ''; ?> data-i18n="quotation.ptOther">ชิ้นส่วนรถยนต์อื่นๆ
                                        (Other Auto Parts)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.weight">Total Weight (น้ำหนักสินค้า)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="07. น้ำหนักสินค้ารวม KG (Total Weight)"
                                    data-i18n-placeholder="quotation.weightPlaceholder"
                                    placeholder="น้ำหนักรวมสินค้า (กก.) ที่บรรจุภายในกล่อง"
                                    value="<?php echo h(old_input('weight')); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.brand">Brand name (ชื่อแบรนด์)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="08. ชื่อแบรนด์ (Brand)"
                                    data-i18n-placeholder="quotation.brandPlaceholder"
                                    placeholder="Mazda, Toyota, Honda, Isuzu, BMW"
                                    value="<?php echo h(old_input('brand')); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.packagingType">Packaging Type
                                        (ประเภทบรรจุภัณฑ์)</span> <span class="required">*</span></label>
                                <select name="09. ประเภทบรรจุภัณฑ์ (Packaging Type)" required>
                                    <option value="" disabled <?php echo old_input('packaging_type') === '' ? 'selected' : ''; ?> data-i18n="quotation.pkgSelect">
                                        เลือกประเภทบรรจุภัณฑ์</option>
                                    <option value="Paper Box" <?php echo old_input('packaging_type') === 'Paper Box' ? 'selected' : ''; ?> data-i18n="quotation.pkgPaper">กล่องกระดาษ (Paper Box)
                                    </option>
                                    <option value="Wooden Packaging" <?php echo old_input('packaging_type') === 'Wooden Packaging' ? 'selected' : ''; ?> data-i18n="quotation.pkgWooden">บรรจุภัณฑ์ไม้
                                        (Wooden Packaging)</option>
                                    <option value="Plastic Packaging" <?php echo old_input('packaging_type') === 'Plastic Packaging' ? 'selected' : ''; ?> data-i18n="quotation.pkgPlastic">
                                        บรรจุภัณฑ์พลาสติก
                                        (Plastic Packaging)</option>
                                    <option value="Steel Packaging" <?php echo old_input('packaging_type') === 'Steel Packaging' ? 'selected' : ''; ?> data-i18n="quotation.pkgSteel">บรรจุภัณฑ์เหล็ก
                                        (Steel Packaging)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.boxSize">Box Size (ขนาดกล่องที่ต้องการ)</span> <span
                                        class="required">*</span></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" step="0.01" name="10. ความกว้างกล่อง (Width)"
                                        data-i18n-placeholder="quotation.width" placeholder="กว้าง"
                                        value="<?php echo h(old_input('box_width')); ?>" required style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="11. ความยาวกล่อง (Length)"
                                        data-i18n-placeholder="quotation.length" placeholder="ยาว"
                                        value="<?php echo h(old_input('box_length')); ?>" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="12. ความสูงกล่อง (Height)"
                                        data-i18n-placeholder="quotation.height" placeholder="สูง"
                                        value="<?php echo h(old_input('box_height')); ?>" required
                                        style="min-width: 0;">
                                    <select name="13. หน่วยวัดกล่อง (Unit)" required
                                        style="min-width: 70px; padding: 10px 8px;">
                                        <option value="cm" <?php echo old_input('box_unit', 'cm') === 'cm' ? 'selected' : ''; ?> data-i18n="quotation.unitCm">ซม.</option>
                                        <option value="mm" <?php echo old_input('box_unit') === 'mm' ? 'selected' : ''; ?>
                                            data-i18n="quotation.unitMm">มม.</option>
                                        <option value="inch" <?php echo old_input('box_unit') === 'inch' ? 'selected' : ''; ?> data-i18n="quotation.unitInch">นิ้ว</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.quantity">Order quantity (จำนวนสั่งซื้อ)</span> <span
                                        class="required">*</span></label>
                                <input type="number" name="14. จำนวนที่สั่งผลิต (Quantity)" min="1"
                                    value="<?php echo h(old_input('quantity', '1')); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row-1">
                            <div class="form-group file-group">
                                <label data-i18n="quotation.reference">Reference (แนบไฟล์ตัวอย่างอ้างอิงที่ชอบ)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="15. ไฟล์แนบอ้างอิง (Attachment)" id="reference_file"
                                        class="file-input" accept="image/*, .pdf">
                                    <span class="file-hint" data-i18n="quotation.fileHint">Max. file size: 128
                                        MB.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row row-1">
                            <div class="form-group">
                                <label><span data-i18n="quotation.comments">Other Comments
                                        (อื่นที่ต้องการแจ้งทีมงาน)</span> <span class="required">*</span></label>
                                <textarea name="16. รายละเอียดเพิ่มเติม (Message)" rows="5"
                                    required><?php echo h(old_input('message')); ?></textarea>
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
    <?php clear_old_input(); ?>
</body>

</html>