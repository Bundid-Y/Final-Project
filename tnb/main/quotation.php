<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';

$loggedInUser = authenticated_user();

// Validate company if user is logged in (allow admins)
if ($loggedInUser !== null) {
    $userCompany = strtoupper((string) ($loggedInUser['company_code'] ?? ''));
    $isAdmin = in_array((string) ($loggedInUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true);
    
    if ($userCompany !== 'TNB' && !$isAdmin) {
        // Redirect to correct company quotation page
        redirect_to(project_url(company_slug_from_code($userCompany) . '/main/quotation.php'));
    }
}

$successMessage = flash('success_message');
$errorMessage = flash('error_message');
?>
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
                    <?php if ($successMessage || $errorMessage): ?>
                        <div style="margin-bottom: 16px; padding: 14px 16px; border-radius: 14px; background: <?php echo $successMessage ? '#e8f7ee' : '#fdecec'; ?>; color: <?php echo $successMessage ? '#0f7a3a' : '#b42318'; ?>;">
                            <?php echo h((string) ($successMessage ?: $errorMessage)); ?>
                        </div>
                    <?php endif; ?>
                    <h2 class="form-title" data-i18n="quotation.title">ขอใบเสนอราคา</h2>
                    <form action="../../admin/api/quotations/tnb/create.php" method="POST" enctype="multipart/form-data" class="quotation-form" id="tnbQuotationForm">
                        <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>" />

                        <div class="form-row row-3">
                            <div class="form-group">
                                <label><span data-i18n="quotation.firstName">First Name (ชื่อ)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="first_name" value="<?php echo h(old_input('first_name', (string) ($loggedInUser['first_name'] ?? ''))); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.lastName">Last Name (นามสกุล)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="last_name" value="<?php echo h(old_input('last_name', (string) ($loggedInUser['last_name'] ?? ''))); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.nickName">Nick name (ชื่อเล่น)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="nick_name" value="<?php echo h(old_input('nick_name', (string) ($loggedInUser['nick_name'] ?? ''))); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.phone">Mobile Phone Number (เบอร์มือถือ)</span> <span
                                        class="required">*</span></label>
                                <input type="tel" name="phone" value="<?php echo h(old_input('phone', (string) ($loggedInUser['phone'] ?? ''))); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.email">Email</span> <span
                                        class="required">*</span></label>
                                <input type="email" name="email" value="<?php echo h(old_input('email', (string) ($loggedInUser['email'] ?? ''))); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.productType">Logistics Service Type (ประเภทบริการขนส่ง)</span> <span class="required">*</span></label>
                                <select name="service_type" required>
                                    <option value="" disabled <?php echo old_input('service_type') === '' ? 'selected' : ''; ?> data-i18n="quotation.productTypeSelect">
                                        เลือกประเภทบริการขนส่ง</option>
                                    <option value="Container Transport" <?php echo old_input('service_type') === 'Container Transport' ? 'selected' : ''; ?> data-i18n="quotation.ptContainer">บริการขนส่งตู้คอนเทนเนอร์
                                        (Container Transport)</option>
                                    <option value="Domestic Transport" <?php echo old_input('service_type') === 'Domestic Transport' ? 'selected' : ''; ?> data-i18n="quotation.ptDomestic">บริการขนส่งสินค้าในประเทศ
                                        (Domestic Transport)</option>
                                    <option value="Import Export" <?php echo old_input('service_type') === 'Import Export' ? 'selected' : ''; ?> data-i18n="quotation.ptImportExport">บริการนำเข้า-ส่งออก
                                        (Import-Export Service)</option>
                                    <option value="Warehouse Shuttle" <?php echo old_input('service_type') === 'Warehouse Shuttle' ? 'selected' : ''; ?> data-i18n="quotation.ptShuttle">บริการรถรับ-ส่งระหว่างคลังสินค้า
                                        (Warehouse Shuttle)</option>
                                    <option value="Truck Parking" <?php echo old_input('service_type') === 'Truck Parking' ? 'selected' : ''; ?> data-i18n="quotation.ptParking">บริการจอดรถบรรทุก
                                        (Truck Parking)</option>
                                    <option value="Last Mile Delivery" <?php echo old_input('service_type') === 'Last Mile Delivery' ? 'selected' : ''; ?> data-i18n="quotation.ptLastMile">บริการจัดส่งปลายทาง
                                        (Last Mile Delivery)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.weight">Cargo Weight (น้ำหนักสินค้าโดยประมาณ)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="weight" data-i18n-placeholder="quotation.weightPlaceholder"
                                    placeholder="น้ำหนักสินค้าโดยประมาณ (กก.)" value="<?php echo h(old_input('weight')); ?>" required>
                            </div>
                        </div>

                        <div class="form-row row-2">
                            <div class="form-group">
                                <label><span data-i18n="quotation.brand">Origin - Destination (จุดเริ่มต้น - จุดหมายปลายทาง)</span> <span
                                        class="required">*</span></label>
                                <input type="text" name="route" data-i18n-placeholder="quotation.brandPlaceholder"
                                    placeholder="เช่น กรุงเทพฯ - เชียงใหม่ หรือ ท่าเรือแหลมฉบัง - โรงงาน" value="<?php echo h(old_input('route')); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.packagingType">Vehicle Type Required (ประเภทรถที่ต้องการ)</span> <span class="required">*</span></label>
                                <select name="vehicle_type" required>
                                    <option value="" disabled <?php echo old_input('vehicle_type') === '' ? 'selected' : ''; ?> data-i18n="quotation.pkgSelect">
                                        เลือกประเภทรถบรรทุก</option>
                                    <option value="6 Wheel Truck" <?php echo old_input('vehicle_type') === '6 Wheel Truck' ? 'selected' : ''; ?> data-i18n="quotation.pkg6Wheel">รถบรรทุก 6 ล้อ
                                        (6 Wheel Truck)</option>
                                    <option value="10 Wheel Truck" <?php echo old_input('vehicle_type') === '10 Wheel Truck' ? 'selected' : ''; ?> data-i18n="quotation.pkg10Wheel">รถบรรทุก 10 ล้อ
                                        (10 Wheel Truck)</option>
                                    <option value="6 Wheel Trailer" <?php echo old_input('vehicle_type') === '6 Wheel Trailer' ? 'selected' : ''; ?> data-i18n="quotation.pkg6WheelTrailer">รถพ่วง 6 ล้อ
                                        (6 Wheel Trailer)</option>
                                    <option value="10 Wheel Trailer" <?php echo old_input('vehicle_type') === '10 Wheel Trailer' ? 'selected' : ''; ?> data-i18n="quotation.pkg10WheelTrailer">รถพ่วง 10 ล้อ
                                        (10 Wheel Trailer)</option>
                                    <option value="Pickup Truck" <?php echo old_input('vehicle_type') === 'Pickup Truck' ? 'selected' : ''; ?> data-i18n="quotation.pkgPickup">รถกระบะ
                                        (Pickup Truck)</option>
                                    <option value="Container Truck" <?php echo old_input('vehicle_type') === 'Container Truck' ? 'selected' : ''; ?> data-i18n="quotation.pkgContainer">รถลากตู้คอนเทนเนอร์
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
                                        data-i18n-placeholder="quotation.width" placeholder="กว้าง" value="<?php echo h(old_input('cargo_width')); ?>" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="cargo_length"
                                        data-i18n-placeholder="quotation.length" placeholder="ยาว" value="<?php echo h(old_input('cargo_length')); ?>" required
                                        style="min-width: 0;">
                                    <span style="color: #666; font-size: 14px;">x</span>
                                    <input type="number" step="0.01" name="cargo_height"
                                        data-i18n-placeholder="quotation.height" placeholder="สูง" value="<?php echo h(old_input('cargo_height')); ?>" required
                                        style="min-width: 0;">
                                    <select name="cargo_unit" required style="min-width: 70px; padding: 10px 8px;">
                                        <option value="cm" <?php echo old_input('cargo_unit', 'cm') === 'cm' ? 'selected' : ''; ?> data-i18n="quotation.unitCm">ซม.</option>
                                        <option value="mm" <?php echo old_input('cargo_unit') === 'mm' ? 'selected' : ''; ?> data-i18n="quotation.unitMm">มม.</option>
                                        <option value="inch" <?php echo old_input('cargo_unit') === 'inch' ? 'selected' : ''; ?> data-i18n="quotation.unitInch">นิ้ว</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><span data-i18n="quotation.quantity">Quantity for Transport (จำนวนที่ต้องการขนส่ง)</span> <span
                                        class="required">*</span></label>
                                <input type="number" name="quantity" min="1" value="<?php echo h(old_input('quantity', '1')); ?>" required>
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
                                <textarea name="comments" rows="5" data-i18n-placeholder="quotation.commentsPlaceholder" placeholder="กรุณาระบุรายละเอียดเพิ่มเติมเกี่ยวกับการขนส่ง เช่น เวลาที่ต้องการ จุดรับส่งพิเศษ หรือข้อกำหนดอื่นๆ" required><?php echo h(old_input('comments')); ?></textarea>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('tnbQuotationForm');
        if (!form) return;
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('.submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerText = 'กำลังส่งข้อมูล...';

            const formData = new FormData(form);
            const formspreeData = new FormData();
            
            formspreeData.append('_subject', 'ขอใบเสนอราคา (TNB)');
            formspreeData.append('บริษัท', 'TNB Logistics Co., Ltd.');
            
            formspreeData.append('01. ชื่อจริง (First Name)', formData.get('first_name') || '-');
            formspreeData.append('02. นามสกุล (Last Name)', formData.get('last_name') || '-');
            formspreeData.append('03. ชื่อเล่น (Nickname)', formData.get('nick_name') || '-');
            formspreeData.append('04. เบอร์มือถือ (Phone)', formData.get('phone') || '-');
            formspreeData.append('05. อีเมล (Email)', formData.get('email') || '-');
            formspreeData.append('06. ประเภทบริการ (Service Type)', formData.get('service_type') || '-');
            formspreeData.append('07. น้ำหนักสินค้า กก. (Weight)', formData.get('weight') || '-');
            formspreeData.append('08. เส้นทาง (Route)', formData.get('route') || '-');
            formspreeData.append('09. ประเภทรถที่ต้องการ (Vehicle Type)', formData.get('vehicle_type') || '-');
            
            let dimensions = `${formData.get('cargo_width')} x ${formData.get('cargo_length')} x ${formData.get('cargo_height')} ${formData.get('cargo_unit')}`;
            formspreeData.append('10. ขนาดสินค้า (Cargo Dimensions)', dimensions);
            formspreeData.append('11. จำนวน (Quantity)', formData.get('quantity') || '-');
            formspreeData.append('12. รายละเอียดเพิ่มเติม (Comments)', formData.get('comments') || '-');
            
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                formspreeData.append('13. ไฟล์แนบอ้างอิง (Attachment)', fileInput.files[0]);
            }

            try {
                await fetch('https://formspree.io/f/xreoqzve', {
                    method: 'POST',
                    body: formspreeData,
                    headers: { 'Accept': 'application/json' }
                });
            } catch (err) {
                console.error('Formspree error:', err);
            }

            form.submit();
        });
    });
    </script>
</body>

</html>