<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ | TNB Logistics</title>
  <meta name="description" content="เข้าสู่ระบบ TNB Logistics สำหรับลูกค้าและพันธมิตร" />

     <!-- Google SEO -->
     <meta name="robots" content="noindex, nofollow" />
     <link rel="canonical" href="https://tnb-logistics.com/login.html" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://tnb-logistics.com/login.html" />
    <meta property="og:title" content="เข้าสู่ระบบ | TNB Logistics" />
    <meta property="og:description" content="เข้าสู่ระบบ TNB Logistics สำหรับลูกค้าและพันธมิตร" />
    <meta property="og:image" content="https://tnb-logistics.com/scr/assets/homepage.webp" />
    <meta property="og:site_name" content="TNB Logistics" />
    <meta property="og:locale" content="th_TH" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp" />
  <!-- Login Page Specific Styles & Script -->
  
    <!-- Google Fonts: Inter (EN) + Sarabun (TH) + Noto Sans SC (ZH) + Noto Sans JP (JP) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Custom CSS & JS -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <script src="../js/i18n.js" defer></script>
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script>
</head>

<body>
  <?php include '../component/menubar.php'; ?>

  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <div class="login-page-wrapper">
    <div class="login-container">
      <div class="forms-container">
        <div class="signin-signup">
          <form action="#" class="sign-in-form login-form">
            <h2 class="login-title" data-i18n="login.titleIn">เข้าสู่ระบบ</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" data-i18n-placeholder="login.username" placeholder="ชื่อผู้ใช้" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" data-i18n-placeholder="login.password" placeholder="รหัสผ่าน" />
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
          <form action="#" class="sign-up-form login-form">
            <h2 class="login-title" data-i18n="login.titleUp">ลงทะเบียน</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" data-i18n-placeholder="login.username" placeholder="ชื่อผู้ใช้" />
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input type="email" data-i18n-placeholder="login.email" placeholder="อีเมล" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" data-i18n-placeholder="login.password" placeholder="รหัสผ่าน" />
            </div>
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
              ขับเคลื่อนธุรกิจของคุณไปข้างหน้า! เข้าร่วมเป็นพันธมิตรกับเรา และสัมผัสประสบการณ์บริการโลจิสติกส์ที่รวดเร็ว
              ปลอดภัย และยั่งยืน
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
              ขอบคุณที่ไว้วางใจให้ TNB เป็นพันธมิตรด้านโลจิสติกส์ของคุณ มาร่วมเติบโตทางธุรกิจไปด้วยกัน!
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
</body>

</html>