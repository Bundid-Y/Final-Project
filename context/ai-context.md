# 📋 Allweb-main — AI Context & Project Summary

> ไฟล์นี้ใช้สำหรับให้ AI (หรือ Developer ใหม่) เข้าใจโปรเจ็กต์ได้ทันที
> อัปเดตล่าสุด: 2026-03-14

---

## 1. ภาพรวมโปรเจ็กต์ (Overview)

โปรเจ็กต์นี้คือ **Multi-site Corporate Website** ที่รวม 2 เว็บไซต์บริษัทในกลุ่มเดียวกันไว้ใน Monorepo เดียว:

| Site | บริษัท | ธุรกิจ |
|------|--------|--------|
| `koch/` | Koch Packaging and Packing Services Co.,Ltd | บรรจุภัณฑ์สำหรับอุตสาหกรรมยานยนต์ |
| `tnb/` | TNB Logistics Co.,Ltd | ขนส่งและโลจิสติกส์ครบวงจร |

ทั้ง 2 เว็บมีโครงสร้างเหมือนกัน แต่ content, สี, บริการ และ branding ต่างกัน

---

## 2. เทคโนโลยีที่ใช้ (Tech Stack)

### Frontend
- **PHP** — ใช้เฉพาะ `include` component (menubar, footer) ไม่มี backend logic
- **Vanilla JavaScript (ES6+)** — ไม่มี framework (ไม่ใช้ React/Vue/Angular)
- **CSS3** — monolithic stylesheet ไฟล์เดียวต่อ site (ไม่ใช้ Tailwind/SCSS)
- **GSAP 3.12** — animation library (ScrollTrigger, SplitText)
- **Lenis 1.0.19** — smooth scrolling library
- **Google Fonts** — Inter, Sarabun, Poppins, Noto Sans SC, Noto Sans JP
- **Font Awesome 6.4** — icons (เฉพาะหน้า Login)

### ระบบภาษา (i18n)
- **Custom client-side i18n engine** เขียนเอง
- รองรับ **4 ภาษา**: ไทย (th), อังกฤษ (en), ญี่ปุ่น (jp), จีน (zh)
- ใช้ `data-i18n` attribute บน HTML element
- ข้อมูลภาษาเก็บใน JSON files (`lang/*.json`)
- ภาษาที่เลือกเก็บใน `localStorage`

### Server / Hosting
- **XAMPP (Apache + PHP)** — local development
- Path: `c:/xampp/htdocs/all-web/`

### Tools
- **Node.js scripts** — `patch_lang.js`, `patch_vision.js` สำหรับ batch update เนื้อหาภาษา
- **PHP/Python scripts** — `update_json.php`, `update_json.py` สำหรับ sync content จาก `data.json`

### ❌ สิ่งที่ยังไม่มี (ณ ปัจจุบัน)
- ไม่มี Database
- ไม่มี Backend API
- ไม่มี Authentication system (Login UI only)
- ไม่มี Build tool / Bundler (ไม่มี Webpack/Vite)
- ไม่มี CSS preprocessor
- ไม่มี Package manager (ไม่มี package.json สำหรับ frontend)

---

## 3. โครงสร้างระบบ (Architecture)

```
Allweb-main/
├── data.json                     # ข้อมูลเนื้อหา multi-lang (company/vision/expertise)
├── update_json.php               # PHP script sync data.json → tnb/lang/*.json
├── update_json.py                # Python script sync data.json (extended version)
├── patch_lang.js                 # Node.js patch ข้อมูลภาษา TNB (index page)
├── patch_lang_2.js               # Node.js patch ภาษาเพิ่มเติม
├── patch_vision.js               # Node.js patch ข้อมูล vision Koch
├── ai-context.md                 # ← ไฟล์นี้
│
├── koch/                         # 🏭 เว็บ Koch Packaging
│   ├── about/
│   │   ├── company.php           # ประวัติบริษัท
│   │   ├── expertise.php         # ความเชี่ยวชาญ
│   │   └── vision.php            # วิสัยทัศน์
│   ├── component/
│   │   ├── menubar.php           # Navigation + Language Switcher + <head> tags
│   │   └── footer.php            # Footer component
│   ├── css/
│   │   └── style.css             # CSS ทั้ง site (~114KB, monolithic)
│   ├── img/
│   │   ├── company_logo/         # Logo บริษัท
│   │   ├── customer_logo/        # Logo ลูกค้า/พันธมิตร
│   │   ├── products/             # รูปสินค้า
│   │   └── other/                # รูปประกอบหน้าต่างๆ
│   ├── js/
│   │   ├── i18n.js               # ระบบแปลภาษา (namespace: window.kochLang)
│   │   └── script.js             # UI logic ทุกหน้า (slider, menu, product, GSAP)
│   ├── lang/
│   │   ├── th.json               # ภาษาไทย
│   │   ├── en.json               # ภาษาอังกฤษ
│   │   ├── jp.json               # ภาษาญี่ปุ่น
│   │   └── zh.json               # ภาษาจีน
│   ├── main/
│   │   ├── index.php             # หน้าแรก (Slider, About, Services, Partners, Products)
│   │   ├── login.php             # Login / Register (UI only)
│   │   ├── contact.php           # ติดต่อเรา + Google Maps
│   │   ├── quotation.php         # ฟอร์มขอใบเสนอราคา (UI only)
│   │   ├── product.php           # แคตตาล็อกสินค้า (filter by category, infinite scroll)
│   │   ├── partners.php          # หน้าพันธมิตร/ลูกค้า
│   │   ├── branches.php          # สาขา
│   │   └── technology.php        # เทคโนโลยี
│   └── service/
│       ├── development.php       # Packaging Development (scroll-based image change)
│       ├── supply_management.php # Supply Management System
│       ├── warehouse.php         # Warehouse & Operation Management
│       └── transportation.php    # Transportation Inhouse Fleet
│
└── tnb/                          # 🚛 เว็บ TNB Logistics
    ├── about/
    │   ├── company.php           # ประวัติบริษัท
    │   ├── expertise.php         # ความเชี่ยวชาญ
    │   └── vision.php            # วิสัยทัศน์
    ├── component/
    │   ├── menubar.php           # Navigation + Language Switcher + <head> tags
    │   └── footer.php            # Footer component
    ├── css/
    │   └── style.css             # CSS ทั้ง site (~125KB, monolithic)
    ├── img/
    │   ├── company_logo/         # Logo บริษัท
    │   ├── customer_logo/        # Logo ลูกค้า
    │   ├── alltruck/             # รูปประเภทรถ
    │   ├── truckshow/            # รูปรถรวม
    │   └── other/                # รูปประกอบหน้าต่างๆ
    ├── js/
    │   ├── i18n.js               # ระบบแปลภาษา (namespace: window.tnbLang)
    │   └── script.js             # UI logic ทุกหน้า
    ├── lang/
    │   ├── th.json               # ภาษาไทย
    │   ├── en.json               # ภาษาอังกฤษ
    │   ├── jp.json               # ภาษาญี่ปุ่น
    │   └── zh.json               # ภาษาจีน
    ├── main/
    │   ├── index.php             # หน้าแรก (Tilt Panel Slider, About, Services, Partners, Trucks)
    │   ├── Login.php             # Login / Register (UI only)
    │   ├── contact.php           # ติดต่อเรา + Google Maps
    │   ├── quotation.php         # ฟอร์มขอใบเสนอราคา (UI only)
    │   ├── partners.php          # พันธมิตร
    │   ├── branches.php          # สาขา
    │   ├── technology.php        # เทคโนโลยี
    │   └── trucktypes.php        # ประเภทรถ (เฉพาะ TNB)
    └── service/
        ├── domestic.php          # ขนส่งในประเทศ
        ├── shuttle.php           # Shuttle Truck (WH to WH)
        ├── import-export.php     # ตู้คอนเทนเนอร์นำเข้า-ส่งออก
        ├── container.php         # จัดการตู้คอนเทนเนอร์ & ลานตู้
        ├── nationwide.php        # กระจายสินค้าทั่วประเทศ
        └── parking.php           # ที่จอดรถบรรทุก & บริหารพื้นที่จอด
```

---

## 4. หน้าที่ของไฟล์สำคัญ (Key Files)

### 4.1 ระบบภาษา (i18n)

| ไฟล์ | หน้าที่ |
|------|---------|
| `koch/js/i18n.js` | Engine แปลภาษาของ Koch — namespace `window.kochLang`, localStorage key `koch_lang` |
| `tnb/js/i18n.js` | Engine แปลภาษาของ TNB — namespace `window.tnbLang`, localStorage key `tnb_lang` |
| `*/lang/th.json` | ข้อมูลภาษาไทย — เป็นภาษาตั้งต้น (DEFAULT_LANG) |
| `*/lang/en.json` | ข้อมูลภาษาอังกฤษ — เป็นภาษา fallback (FALLBACK_LANG) |
| `*/lang/jp.json` | ข้อมูลภาษาญี่ปุ่น |
| `*/lang/zh.json` | ข้อมูลภาษาจีน |

**วิธีใช้ i18n:**
```html
<!-- textContent -->
<span data-i18n="nav.services">บริการของเรา</span>

<!-- placeholder -->
<input data-i18n-placeholder="login.username" placeholder="ชื่อผู้ใช้" />

<!-- title attribute -->
<div data-i18n-title="tooltip.info">...</div>

<!-- input value (submit button) -->
<input type="submit" data-i18n-value="form.submit" value="ส่ง" />

<!-- innerHTML (Koch only) -->
<div data-i18n-html="section.rich_content">...</div>
```

**วิธีเพิ่มข้อความแปลภาษาใหม่:**
1. เพิ่ม key ใน `lang/*.json` ทั้ง 4 ไฟล์
2. เพิ่ม `data-i18n="section.key"` บน HTML element
3. ไม่ต้องแก้ JS

### 4.2 UI Logic (script.js)

`script.js` ของแต่ละ site รวม logic ทุกหน้าไว้ในไฟล์เดียว:

| Feature | ใช้ในหน้า | หมายเหตุ |
|---------|----------|----------|
| Image Slider | `index.php` | Koch: 3D carousel, TNB: Tilt Panel |
| Logo Carousel | `index.php`, `partners.php` | CSS infinite loop animation |
| GSAP ScrollTrigger | ทุกหน้า | Fade-up, parallax decorative shapes |
| Lenis Smooth Scroll | ทุกหน้า | Smooth scrolling library |
| Menubar Logic | ทุกหน้า | Hamburger toggle, dropdown, scroll compact |
| Product Filter | `product.php` | Category filter + infinite scroll (Koch) |
| Scroll Image Change | `development.php` | เปลี่ยนรูปตาม scroll position (Koch, ใช้ jQuery) |
| Login Toggle | `login.php` | สลับ Sign-in / Sign-up panel |
| Expanding Cards | `index.php` | Truck type cards (TNB) |

### 4.3 PHP Components

| ไฟล์ | หน้าที่ |
|------|---------|
| `*/component/menubar.php` | Header + Navigation + Language Switcher — **มี `<html>`, `<head>`, `<body>` ครบ** (include แล้วจะซ้อน HTML tags) |
| `*/component/footer.php` | Footer — Brand info, contact, quick links, business hours, copyright |

### 4.4 Content Management Scripts (Root level)

| ไฟล์ | หน้าที่ |
|------|---------|
| `data.json` | แหล่งข้อมูลกลาง — เก็บเนื้อหา company, vision, expertise ใน 4 ภาษา |
| `update_json.php` | อ่าน `data.json` → เขียนทับ sections ใน `tnb/lang/*.json` |
| `update_json.py` | เวอร์ชัน Python ของ update script (extended) |
| `patch_lang.js` | Node.js — patch index page content (about, services) ใน `tnb/lang/*.json` |
| `patch_lang_2.js` | Node.js — patch เพิ่มเติม |
| `patch_vision.js` | Node.js — patch vision content ใน `koch/lang/*.json` + ลบ key เก่า |

---

## 5. Data Flow (การไหลของข้อมูล)

### 5.1 เมื่อผู้ใช้เปิดเว็บ
```
Browser → GET /koch/main/index.php
       → Apache/PHP ประกอบ HTML (include menubar + footer)
       → Browser โหลด style.css, i18n.js, script.js
       → i18n.js: อ่าน localStorage → fetch lang/th.json
       → แทนที่ textContent ทุก [data-i18n] element
       → script.js: init slider, GSAP animations, menu
       → หน้าเว็บพร้อมใช้งาน
```

### 5.2 เมื่อผู้ใช้เปลี่ยนภาษา
```
คลิกปุ่ม "English"
→ window.kochLang.setLang('en')
→ fetch lang/en.json (ถ้ายังไม่ cache)
→ แทนที่ text ทุก [data-i18n], [data-i18n-placeholder], [data-i18n-value]
→ อัปเดต <html lang="en">, font-family
→ localStorage.setItem('koch_lang', 'en')
→ หน้าถัดไปจะใช้ภาษาอังกฤษอัตโนมัติ
```

### 5.3 Content Pipeline (Developer workflow)
```
แก้ data.json → รัน update_json.php / patch_*.js → อัปเดต lang/*.json → เว็บแสดงเนื้อหาใหม่
```

---

## 6. แผนระบบหลังบ้าน (Backend — อนาคต)

> Section นี้สำหรับ AI / Developer ที่จะมาพัฒนาต่อ
> เพื่อให้เข้าใจว่าระบบหน้าบ้านเตรียมไว้อย่างไร และหลังบ้านควรต่อตรงไหน

### 6.1 หน้าที่รอเชื่อมต่อ Backend

| หน้า | สิ่งที่ต้องเชื่อม | หมายเหตุ |
|------|-------------------|----------|
| `login.php` | Authentication (Login/Register) | มี form UI พร้อม — `form action="#"` ต้องเปลี่ยนเป็น API endpoint |
| `quotation.php` | Form submission + บันทึกลง DB | มี form fields พร้อม (ชื่อ, เบอร์, อีเมล, ข้อความ, แนบไฟล์) |
| `contact.php` | Contact form submission | ปัจจุบันแสดงข้อมูลติดต่อเท่านั้น อาจเพิ่ม contact form |
| `product.php` | ดึงข้อมูลสินค้าจาก DB | ปัจจุบัน hardcode ใน HTML — ควรเปลี่ยนเป็น dynamic |
| `partners.php` | ดึง logo พันธมิตรจาก DB | ปัจจุบัน hardcode `<img>` tags |
| `branches.php` | ดึงข้อมูลสาขาจาก DB | ปัจจุบัน static content |

### 6.2 แนะนำ Backend Stack

เนื่องจากหน้าบ้านใช้ **PHP** อยู่แล้ว ตัวเลือกที่เหมาะสม:

**Option A: PHP Backend (ต่อยอดจากที่มี)**
- PHP + MySQL/MariaDB (XAMPP มี MariaDB อยู่แล้ว)
- ใช้ PDO สำหรับ database connection
- สร้าง API endpoints เป็น PHP files (เช่น `api/login.php`, `api/quotation.php`)

**Option B: Node.js Backend (แยก API)**
- Express.js / Fastify
- MySQL / PostgreSQL / MongoDB
- RESTful API หรือ GraphQL
- หน้าบ้าน PHP เรียก API ผ่าน `fetch()` จาก JavaScript

### 6.3 Database Tables ที่ควรมี

```
users              — ผู้ใช้ระบบ (login/register)
quotations         — ใบเสนอราคาที่ลูกค้าส่งมา
products           — สินค้า/บริการ (สำหรับ product.php)
partners           — พันธมิตร/ลูกค้า (logo + ข้อมูล)
branches           — สาขา (ชื่อ, ที่อยู่, พิกัด)
contacts           — ข้อความติดต่อจากลูกค้า
content_pages      — เนื้อหาหน้าเว็บ (ทดแทน lang/*.json บางส่วน)
```

### 6.4 จุดเชื่อมต่อ Frontend ↔ Backend

**Login:**
```javascript
// login.php — เปลี่ยน form action
<form action="/api/auth/login.php" method="POST">
// หรือใช้ fetch() + JSON
fetch('/api/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
})
```

**Quotation:**
```javascript
// quotation.php — เปลี่ยน form action
<form action="/api/quotation/submit.php" method="POST" enctype="multipart/form-data">
```

**Products (dynamic):**
```javascript
// product.php — โหลดสินค้าจาก API
fetch('/api/products/list.php?category=all&limit=8&offset=0')
    .then(res => res.json())
    .then(products => renderProductGrid(products));
```

### 6.5 สิ่งที่ต้องระวังเมื่อเพิ่ม Backend

- **menubar.php มี `<html><head><body>` ครบ** — เมื่อ include จะทำให้ HTML ซ้อนกัน (ปัจจุบันทำงานได้เพราะ browser ช่วย fix) ควรแก้ให้ menubar เป็น fragment เท่านั้น
- **i18n เป็น client-side** — ถ้าต้องการ SEO ที่ดี ควรพิจารณา server-side rendering ภาษา
- **ไม่มี CSRF protection** — ฟอร์มทุกตัวยังไม่มี token ป้องกัน
- **ไม่มี input validation** — ต้องเพิ่มทั้ง client-side และ server-side
- **ไม่มี session management** — ต้องเพิ่มระบบ session/JWT สำหรับ auth
- **CSS เป็น monolithic** — ถ้าเพิ่มหน้าเยอะขึ้น ควรพิจารณาแยก CSS หรือใช้ preprocessor
- **ทั้ง 2 site (Koch/TNB) ใช้โครงสร้างเดียวกัน** — ควรพิจารณา shared backend เพื่อลดการ duplicate

---

## 7. Naming Conventions

| ประเภท | Convention | ตัวอย่าง |
|--------|-----------|----------|
| PHP pages | lowercase, underscore | `supply_management.php`, `import-export.php` |
| CSS classes | BEM-like, lowercase-hyphen | `.tnb-news-card__title`, `.koch-about-cta` |
| i18n keys | dot notation | `nav.services`, `index.about_p1`, `footer.desc` |
| JS namespaces | camelCase | `window.kochLang`, `window.tnbLang` |
| localStorage keys | snake_case | `koch_lang`, `tnb_lang` |
| Image folders | lowercase | `company_logo/`, `customer_logo/` |

---

## 8. Koch vs TNB — ความแตกต่าง

| ด้าน | Koch | TNB |
|------|------|-----|
| ธุรกิจ | Packaging (บรรจุภัณฑ์) | Logistics (ขนส่ง) |
| จำนวนบริการ | 4 (development, supply, warehouse, transport) | 6 (domestic, shuttle, import-export, container, nationwide, parking) |
| Slider หน้าแรก | 3D Carousel (5 slides) | Tilt Panel (7 slides with dots) |
| สินค้า | มี product.php (กล่อง, พาเลท) | มี trucktypes.php (ประเภทรถ) |
| i18n namespace | `window.kochLang` | `window.tnbLang` |
| localStorage key | `koch_lang` | `tnb_lang` |
| CSS ขนาด | ~114KB | ~125KB |
| สี Brand | แดง + น้ำเงินเข้ม (#ED2A2A, #325662) | น้ำเงิน + ขาว |
| Truck animation | ไม่มี | มี TruckLoader CSS animation |

---

## 9. Quick Commands

```bash
# อัปเดตเนื้อหาภาษา TNB จาก data.json (PHP)
php update_json.php

# Patch ข้อมูล index page ของ TNB (Node.js)
node patch_lang.js

# Patch ข้อมูล vision ของ Koch (Node.js)
node patch_vision.js

# เปิดเว็บด้วย XAMPP
# Koch: http://localhost/all-web/koch/main/index.php
# TNB:  http://localhost/all-web/tnb/main/index.php
```
