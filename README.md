# Reuchne Tooth Fairy Clinic — Full-Stack Dental Management System

## 🌸 Overview
A complete dental clinic management system with a soft pink & white professional theme.

---

## ⚙️ Setup Instructions

### 1. Requirements
- PHP 7.4+ (PHP 8.x recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache (with mod_rewrite enabled) or Nginx
- XAMPP / WAMP / LAMP / MAMP (for local development)

### 2. Installation

**Step 1 — Clone / Copy Files**
Place the `reuchne-clinic` folder inside your web server root:
- XAMPP: `C:/xampp/htdocs/reuchne-clinic`
- WAMP: `C:/wamp64/www/reuchne-clinic`
- Linux/Mac: `/var/www/html/reuchne-clinic`

**Step 2 — Create Database**
Open phpMyAdmin or your MySQL client and run:
```sql
SOURCE /path/to/reuchne-clinic/database.sql
```
Or copy-paste the full contents of `database.sql` into your SQL editor.

**Step 3 — Configure Database Connection**
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'reuchne_clinic');
define('BASE_URL', 'http://localhost/reuchne-clinic');
```

**Step 4 — Add Your Logo**
Replace the placeholder at:
```
assets/images/placeholders/logo.png
```
Recommended size: **200×80px** (PNG with transparent background)

**Step 5 — Add Clinic & Doctor Photos**
Place images in `assets/images/placeholders/`:
- `clinic-1.jpg` — Hero carousel image 1
- `clinic-2.jpg` — Hero carousel image 2
- `clinic-3.jpg` — Hero carousel image 3
- `doctor-hero.jpg` — Doctor carousel image
- `doctor-main.jpg` — Doctor profile main photo
- `doctor-2.jpg` to `doctor-4.jpg` — Doctor gallery thumbnails

Recommended: **1920×1080px** for carousel, **600×750px** for doctor photos.

**Step 6 — File Permissions**
```bash
chmod 755 uploads/
chmod 755 uploads/doctor/
chmod 755 uploads/receipts/
```

**Step 7 — Launch**
Visit: `http://localhost/reuchne-clinic`

---

## 🔑 Default Login Credentials

| Role    | Email                          | Password     |
|---------|-------------------------------|--------------|
| Admin   | admin@reuchneclinic.com        | password     |
| Doctor  | drbermas@reuchneclinic.com     | password     |

> ⚠️ **IMPORTANT:** Change these passwords immediately after setup!
> The default seeded password hash corresponds to the string `password`.

To change, go to the login page and use the profile settings, or run:
```sql
UPDATE users SET password = '$2y$10$YOUR_NEW_BCRYPT_HASH' WHERE id = 1;
```

---

## 📁 Project Structure

```
reuchne-clinic/
├── index.php                   ← Landing page (Home)
├── database.sql                ← Full database schema + seed data
├── .htaccess                   ← Apache security config
├── README.md
│
├── includes/                   ← Shared PHP includes
│   ├── config.php              ← DB connection & constants
│   ├── auth.php                ← Auth functions
│   ├── header.php              ← Public navbar
│   ├── footer.php              ← Public footer
│   ├── admin-sidebar.php       ← Admin dashboard layout
│   ├── doctor-sidebar.php      ← Doctor dashboard layout
│   ├── patient-sidebar.php     ← Patient dashboard layout
│   └── dashboard-footer.php    ← Dashboard closing tags
│
├── pages/                      ← Public-facing pages
│   ├── login.php
│   ├── register.php
│   ├── book.php                ← Appointment booking
│   ├── services.php
│   └── doctor-profile.php
│
├── patient/                    ← Patient dashboard
│   ├── index.php
│   ├── appointments.php
│   ├── profile.php
│   ├── medical-history.php
│   ├── payments.php
│   ├── receipt.php
│   ├── cancel-appointment.php
│   └── notifications.php
│
├── doctor/                     ← Doctor dashboard
│   ├── index.php
│   ├── appointments.php
│   ├── patients.php
│   ├── patient-detail.php
│   ├── add-notes.php
│   ├── complete-appointment.php
│   ├── schedule.php
│   ├── announcements.php
│   └── notifications.php
│
├── admin/                      ← Admin dashboard
│   ├── index.php               ← Overview + charts
│   ├── revenue.php             ← Revenue analytics
│   ├── appointments.php
│   ├── payments.php            ← Process & issue receipts
│   ├── patients.php
│   ├── doctors.php
│   ├── services.php
│   └── testimonials.php
│
├── api/                        ← AJAX endpoints
│   ├── logout.php
│   └── timeslots.php           ← Available time slots
│
├── assets/
│   ├── css/main.css            ← Full stylesheet (pink theme)
│   ├── js/main.js              ← Carousel, calendar, interactions
│   └── images/placeholders/   ← Add your clinic photos here
│
└── uploads/                    ← User-uploaded files
    ├── doctor/
    └── receipts/
```

---

## 🎨 Customization

### Change Clinic Name
Search and replace `Reuchne Tooth Fairy Clinic` across all files.

### Change Colors
Edit CSS variables in `assets/css/main.css`:
```css
:root {
  --pink-500: #f04e7d;   /* Primary accent */
  --pink-600: #dc3060;   /* Darker accent */
  /* ... */
}
```

### Change Doctor Info
Update the `doctor_profiles` table in MySQL:
```sql
UPDATE doctor_profiles SET
  full_name = 'Dr. Your Name',
  specialty = 'Your Specialty',
  bio = 'Your biography...'
WHERE id = 1;
```

### Add Services
Use the Admin panel → Services, or insert directly:
```sql
INSERT INTO services (name, description, price, duration_minutes, category, icon)
VALUES ('Service Name', 'Description', 1500.00, 60, 'Category', '🦷');
```

---

## 🔒 Security Notes

1. Change all default passwords after setup
2. Keep `includes/` protected via `.htaccess`
3. Use HTTPS in production (`BASE_URL` should be `https://`)
4. Regularly backup the database
5. Consider adding CSRF tokens to forms for production use

---

## 🌸 Features Summary

| Feature | Details |
|---------|---------|
| 🎨 Theme | Soft pink & white, professional |
| 🏥 Roles | Admin, Doctor, Patient |
| 📅 Booking | Calendar UI, time slots, double-booking prevention |
| 💳 Payments | Cash, GCash, Maya, Credit Card, Insurance |
| 🧾 Receipts | Printable receipt generator |
| 📊 Revenue | Charts by day/month/service/doctor |
| 📢 Announcements | Doctor posts to all patients |
| 🔔 Notifications | Real-time in-app notifications |
| 📋 Medical History | Patient-managed health records |
| 👩‍⚕️ Doctor Profile | Dedicated page with gallery |

---

Made with 💗 for Reuchne Tooth Fairy Clinic
