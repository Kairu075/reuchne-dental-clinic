# Reuchne Tooth Fairy Clinic — Full-Stack Dental Services Booking & Management System
 View the Deployed Website Here: https://reuchnetoothfairyclinic.free.nf/index.php
 I used a free web hosting for deployment: https://www.infinityfree.com/


---

##  Default Login Credentials

| Role    | Email                          | Password     |
|---------|-------------------------------|--------------|
| Admin   | admin@reuchneclinic.com        | password     |
| Doctor  | drbermas@reuchneclinic.com     | password     |


> The default seeded password hash corresponds to the string `password`.

To change, go to the login page and use the profile settings, or run:
```sql
UPDATE users SET password = '$2y$10$YOUR_NEW_BCRYPT_HASH' WHERE id = 1;
```

---

##  Project Structure

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


##  Features Summary

| Feature | Details |
|---------|---------|
|  Roles | Admin, Doctor, Patient |
|  Booking | Calendar UI, time slots, double-booking prevention |
|  Payments | Cash, GCash, Maya, Credit Card, Insurance |
|  Receipts | Printable receipt generator |
|   Revenue | Charts by day/month/service/doctor |
|  Announcements | Doctor posts to all patients |
|  Notifications | Real-time in-app notifications |
|  Medical History | Patient-managed health records |
|  Doctor Profile | Dedicated page with gallery |

---

© 2026 Reuchne Tooth Fairy Clinic
Developed by Kyle Dominic Yap.
