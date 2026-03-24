<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';

$loggedInUser = authenticated_user();
if ($loggedInUser !== null) {
    $targetUrl = in_array((string) ($loggedInUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true)
        ? project_url('admin/dashboard.php')
        : user_page_by_company((string) ($loggedInUser['company_code'] ?? 'KOCH'));

    redirect_to($targetUrl);
}

$successMessage = flash('success_message');
$errorMessage = flash('error_message');
$showRegister = $errorMessage !== null && (
    old_input('register_username') !== ''
    || old_input('register_email') !== ''
    || old_input('register_first_name') !== ''
    || old_input('register_last_name') !== ''
    || old_input('register_phone') !== ''
    || old_input('register_nick_name') !== ''
    || old_input('register_accept_terms') === '1'
);
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koch Packaging - Login</title>

  <!-- Google SEO -->
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://koch-packaging-services.com/login" />

  <!-- Facebook Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="th_TH" />
  <meta property="og:site_name" content="KOCH Packaging and Packing Services Co.,Ltd" />
  <meta property="og:title" content="KOCH Packaging and Packing Services Co.,Ltd" />
  <meta property="og:description" content="Smart, Fast, and Sustainable Solutions สำหรับอุตสาหกรรมยานยนต์ในประเทศไทย - บริการบรรจุภัณฑ์และคลังสินค้าครบวงจร" />
  <meta property="og:url" content="https://koch-packaging-services.com/login" />
  <meta property="og:image" content="https://koch-packaging-services.com/scr/assets/carousel/company/Gemini_Generated_Image_o9ab0wo9.png" />

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../img/company_logo/logo 2.png" />
  
  <!-- Login Page Specific Styles & Script -->
  <link rel="stylesheet" href="../css/style.css">
  <script src="../js/script.js" defer></script>
</head>

<body>
  <?php include '../component/menubar.php'; ?>

  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <div class="login-page-wrapper">
    <div class="login-container<?php echo $showRegister ? ' sign-up-mode' : ''; ?>">
      <?php if ($successMessage || $errorMessage): ?>
        <div style="position:absolute;top:16px;left:50%;transform:translateX(-50%);z-index:100;width:90%;max-width:420px;padding:14px 18px;border-radius:14px;background:<?php echo $successMessage ? '#e8f7ee' : '#fdecec'; ?>;color:<?php echo $successMessage ? '#0f7a3a' : '#b42318'; ?>;box-shadow:0 4px 16px rgba(0,0,0,.12);text-align:center;font-weight:600;">
          <?php echo h((string) ($successMessage ?: $errorMessage)); ?>
        </div>
      <?php endif; ?>
      <div class="forms-container">
        <div class="signin-signup">
          <form action="../../admin/api/auth/login.php" method="POST" class="sign-in-form login-form">
            <h2 class="login-title" data-i18n="login.titleIn">เข้าสู่ระบบ</h2>
            <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>" />
            <input type="hidden" name="company" value="koch" />
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="identifier" value="<?php echo h(old_input('identifier')); ?>" data-i18n-placeholder="login.username" placeholder="ชื่อผู้ใช้หรืออีเมล" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" data-i18n-placeholder="login.password" placeholder="รหัสผ่าน" />
            </div>
            <button type="submit" data-i18n="login.submitIn" class="login-btn solid">เข้าสู่ระบบ</button>
            <p class="social-text" data-i18n="login.socialInText">หรือเข้าสู่ระบบด้วยแพลตฟอร์มโซเชียล</p>
            <div class="social-media">
              <a href="#" class="social-icon">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#" class="social-icon">
                <i class="fab fa-line"></i>
              </a>
              <a href="#" class="social-icon">
                <i class="fab fa-google"></i>
              </a>
            </div>
          </form>
          <form action="../../admin/api/auth/register.php" method="POST" class="sign-up-form login-form">
            <h2 class="login-title" data-i18n="login.titleUp">ลงทะเบียน</h2>
            <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>" />
            <input type="hidden" name="company" value="koch" />
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="username" value="<?php echo h(old_input('register_username')); ?>" data-i18n-placeholder="login.username" placeholder="ชื่อผู้ใช้" />
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" value="<?php echo h(old_input('register_email')); ?>" data-i18n-placeholder="login.email" placeholder="อีเมล" />
            </div>
            <div class="input-field">
              <i class="fas fa-id-card"></i>
              <input type="text" name="first_name" value="<?php echo h(old_input('register_first_name')); ?>" placeholder="ชื่อ" />
            </div>
            <div class="input-field">
              <i class="fas fa-id-card"></i>
              <input type="text" name="last_name" value="<?php echo h(old_input('register_last_name')); ?>" placeholder="นามสกุล" />
            </div>
            <div class="input-field">
              <i class="fas fa-phone"></i>
              <input type="tel" name="phone" value="<?php echo h(old_input('register_phone')); ?>" placeholder="เบอร์โทรศัพท์" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" data-i18n-placeholder="login.password" placeholder="รหัสผ่าน" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" />
            </div>
            <label style="display:flex; gap:10px; align-items:flex-start; font-size:14px; color:#555; margin: 8px 0 14px;">
              <input type="checkbox" name="accept_terms" value="1" <?php echo old_input('register_accept_terms') === '1' ? 'checked' : ''; ?> style="margin-top:3px;" />
              <span>ฉันยอมรับเงื่อนไขการใช้งานและนโยบายความเป็นส่วนตัว</span>
            </label>
            <button type="submit" class="login-btn" data-i18n="login.submitUp">ลงทะเบียน</button>
            <p class="social-text" data-i18n="login.socialUpText">หรือลงทะเบียนด้วยแพลตฟอร์มโซเชียล</p>
            <div class="social-media">
              <a href="#" class="social-icon">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#" class="social-icon">
                <i class="fab fa-line"></i>
              </a>
              <a href="#" class="social-icon">
                <i class="fab fa-google"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3 data-i18n="login.newcomerTitle">เพิ่งมาใหม่ใช่ไหม?</h3>
            <p data-i18n="login.newcomerText">
              ยกระดับซัพพลายเชนของคุณ! เข้าร่วมเป็นพันธมิตรกับเรา และสัมผัสโซลูชันบรรจุภัณฑ์ที่ฉลาด รวดเร็ว และยั่งยืน
            </p>
            <button class="login-btn transparent" id="sign-up-btn" data-i18n="login.signUpBtn">
              ลงทะเบียน
            </button>
          </div>
          <img src="https://i.ibb.co/6HXL6q1/Privacy-policy-rafiki.png" class="image" alt="" />
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3 data-i18n="login.memberTitle">หนึ่งในสมาชิกที่ทรงคุณค่าของเรา</h3>
            <p data-i18n="login.memberText">
              ขอบคุณที่ไว้วางใจให้ KOCH เป็นพันธมิตรด้านบรรจุภัณฑ์ของคุณ มาร่วมขับเคลื่อนนวัตกรรมไปด้วยกัน!
            </p>
            <button class="login-btn transparent" id="sign-in-btn" data-i18n="login.signInBtn">
              เข้าสู่ระบบ
            </button>
          </div>
          <img src="https://i.ibb.co/nP8H853/Mobile-login-rafiki.png" class="image" alt="" />
        </div>
      </div>
    </div>
  </div>



  <?php include '../component/footer.php'; ?>
  <?php clear_old_input(); ?>
</body>

</html>