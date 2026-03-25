<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/content.php';

$pdo = Database::connection();
$companyId = get_company_id_by_code($pdo, 'KOCH');
$companyInfo = get_company_info($pdo, 'KOCH');
$dbBranches = get_active_branches($pdo, $companyId);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koch Packaging - Contact</title>

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

<body style="background-color: #fbfbfb;">
    <!-- Light grey background like standard modern sites to let white blocks pop -->
    <?php include '../component/menubar.php'; ?>

    <!-- CONTENT SECTION matching technology.php layout -->
    <div class="tech_section layout_padding" style="padding-top: 80px; padding-bottom: 90px; overflow: hidden;">

        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">

            <div class="row" style="display: flex; flex-direction: column; align-items: center; margin: 0 -15px;">

                <!-- Top Row: Content -->
                <div class="col-md-12"
                    style="width: 100%; max-width: 900px; padding: 0 15px; box-sizing: border-box; text-align: center; margin-bottom: 60px;">

                    <h1 class="tech_subtitle"
                        style="font-size: 40px; color: #1c1c1c; font-weight: bold; margin-bottom: 8px; position: relative; display: inline-block;">
                        <span data-i18n="contact.title">ติดต่อเรา</span>
                    </h1>

                    <p class="tech_text"
                        style="font-size: 16px; color: #1c1c1c; line-height: 1.6; padding-top: 0; margin-top: 0; margin-bottom: 50px;"
                        data-i18n="contact.intro">
                        สอบถามข้อมูลบริการบรรจุภัณฑ์ครบวงจร หรือบริการอื่นๆ ของ KOCH
                        Packaging
                        <br>
                        เราพร้อมให้คำปรึกษาและประสานงานโดยทีมงานมืออาชีพ
                    </p>

                    <div class="contact-list"
                        style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; text-align: left;">
                        <!-- ที่อยู่ -->
                        <div class="contact-item"
                            style="display: flex; align-items: flex-start; flex: 1; min-width: 260px; max-width: 320px;">
                            <div class="contact-icon"
                                style="background: #ED2A2A; color: #fff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 15px;">
                                <!-- SVG Map Pin -->
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="contact-text-group">
                                <h4 style="margin: 0 0 5px 0; font-size: 18px; color: #1c1c1c; font-weight: 700;"
                                    data-i18n="contact.address_label">
                                    ที่อยู่สำนักงาน</h4>
                                <p style="margin: 0; color: #444; font-size: 15px; line-height: 1.8;"
                                    data-i18n-html="contact.address">742/5 หมู่ที่ 1 ตำบลหนองไผ่แก้ว<br>อำเภอบ้านบึง จังหวัดชลบุรี 20220</p>
                            </div>
                        </div>

                        <!-- เบอร์โทร -->
                        <div class="contact-item"
                            style="display: flex; align-items: flex-start; flex: 1; min-width: 200px; max-width: 250px;">
                            <div class="contact-icon"
                                style="background: #ED2A2A; color: #fff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 15px;">
                                <!-- SVG Phone -->
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <div class="contact-text-group">
                                <h4 style="margin: 0 0 5px 0; font-size: 18px; color: #1c1c1c; font-weight: 700;"
                                    data-i18n="contact.phone_label">
                                    ติดต่อฝ่ายขาย</h4>
                                <p style="margin: 0; color: #444; font-size: 15px; line-height: 1.5;">
                                    081-5758823<br>062-6392499</p>
                            </div>
                        </div>

                        <!-- อีเมล -->
                        <div class="contact-item"
                            style="display: flex; align-items: flex-start; flex: 1; min-width: 280px; max-width: 350px;">
                            <div class="contact-icon"
                                style="background: #ED2A2A; color: #fff; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 15px;">
                                <!-- SVG Mail -->
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                    </path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="contact-text-group">
                                <h4 style="margin: 0 0 5px 0; font-size: 18px; color: #1c1c1c; font-weight: 700;"
                                    data-i18n="contact.email_label">อีเมล
                                </h4>
                                <p
                                    style="margin: 0; color: #444; font-size: 15px; line-height: 1.5; white-space: nowrap;">
                                    salesteam@koch-packaging.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div style="width:100%;max-width:700px;padding:0 15px;box-sizing:border-box;margin-top:50px;">
                    <?php if($successMessage):?><div style="padding:14px 18px;border-radius:12px;background:#e8f7ee;color:#0f7a3a;font-weight:600;margin-bottom:20px;text-align:center"><?php echo h((string)$successMessage);?></div><?php endif;?>
                    <?php if($errorMessage):?><div style="padding:14px 18px;border-radius:12px;background:#fdecec;color:#b42318;font-weight:600;margin-bottom:20px;text-align:center"><?php echo h((string)$errorMessage);?></div><?php endif;?>
                    <div style="background:#fff;border-radius:16px;padding:40px;box-shadow:0 4px 20px rgba(0,0,0,.08)">
                        <h2 style="font-size:24px;font-weight:700;margin-bottom:6px;color:#1c1c1c" data-i18n="contact.form_title">ส่งข้อความถึงเรา</h2>
                        <p style="font-size:14px;color:#666;margin-bottom:24px" data-i18n="contact.form_sub">กรอกข้อมูลด้านล่าง เราจะติดต่อกลับโดยเร็วที่สุด</p>
                        <form action="../../admin/api/contact/submit.php" method="POST">
                            <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>">
                            <input type="hidden" name="company" value="koch">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                                <div><label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px">ชื่อ-นามสกุล *</label><input type="text" name="name" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s" onfocus="this.style.borderColor='#ED2A2A'" onblur="this.style.borderColor='#e2e8f0'"></div>
                                <div><label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px">อีเมล *</label><input type="email" name="email" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s" onfocus="this.style.borderColor='#ED2A2A'" onblur="this.style.borderColor='#e2e8f0'"></div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                                <div><label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px">เบอร์โทรศัพท์</label><input type="tel" name="phone" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s" onfocus="this.style.borderColor='#ED2A2A'" onblur="this.style.borderColor='#e2e8f0'"></div>
                                <div><label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px">หัวข้อ</label><input type="text" name="subject" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s" onfocus="this.style.borderColor='#ED2A2A'" onblur="this.style.borderColor='#e2e8f0'"></div>
                            </div>
                            <div style="margin-bottom:20px"><label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px">ข้อความ *</label><textarea name="message" rows="5" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;resize:vertical;transition:border .2s" onfocus="this.style.borderColor='#ED2A2A'" onblur="this.style.borderColor='#e2e8f0'"></textarea></div>
                            <button type="submit" style="width:100%;padding:12px;background:#ED2A2A;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:background .2s" onmouseover="this.style.background='#c41f1f'" onmouseout="this.style.background='#ED2A2A'" data-i18n="contact.form_submit">ส่งข้อความ</button>
                        </form>
                    </div>
                </div>

                <!-- Bottom Row: Map matching the image style -->
                <div class="col-md-10"
                    style="width: 100%; max-width: 1000px; padding: 0 15px; box-sizing: border-box; position: relative; margin-top: 50px;">
                    <!-- Red Polygon Background behind Map -->
                    <div class="map-bg-shape"></div>

                    <div class="tech_box_main" style="position: relative;">
                        <div class="tech_image"
                            style="box-shadow: 0 15px 40px rgba(0,0,0,0.15); height: 500px; background: #fff; display: flex; align-items: stretch; border-radius: 0;">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124291.35992560715!2d101.06124759726562!3d13.218724100000008!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3102d30061100aa1%3A0xfcf1e516c1c8beac!2sKOCH%20Packaging%20and%20packing%20services%20co.%2Cltd!5e0!3m2!1sth!2sth!4v1772423982751!5m2!1sth!2sth"
                                frameborder="0" style="border:0; width: 100%; height: 100%; display: block;"
                                allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <?php include '../component/footer.php'; ?>
</body>

</html>