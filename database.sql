-- ============================================================
-- Reuchne Tooth Fairy Clinic - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS reuchne_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reuchne_clinic;

-- Users table (base auth)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor profiles
CREATE TABLE doctor_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    specialty VARCHAR(200),
    education TEXT,
    bio TEXT,
    mission TEXT,
    vision TEXT,
    experience_years INT DEFAULT 0,
    qualifications TEXT,
    profile_photo VARCHAR(255),
    gallery_photos TEXT COMMENT 'JSON array of image paths',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Patient profiles
CREATE TABLE patient_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male','female','other'),
    phone VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(200),
    emergency_phone VARCHAR(20),
    blood_type VARCHAR(5),
    allergies TEXT,
    medical_conditions TEXT,
    current_medications TEXT,
    dental_history TEXT,
    insurance_provider VARCHAR(150),
    insurance_number VARCHAR(100),
    preferred_payment ENUM('cash','gcash','maya','credit_card','insurance') DEFAULT 'cash',
    profile_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration_minutes INT DEFAULT 60,
    category VARCHAR(100),
    icon VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Schedules (doctor availability)
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration INT DEFAULT 60 COMMENT 'in minutes',
    max_appointments INT DEFAULT 10,
    is_available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Blocked dates (doctor leaves/emergencies)
CREATE TABLE blocked_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    blocked_date DATE NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    service_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending','approved','completed','cancelled','rescheduled') DEFAULT 'pending',
    notes TEXT,
    diagnosis TEXT,
    prescription TEXT,
    patient_notes TEXT,
    cancel_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Payments / Transactions
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','gcash','maya','credit_card','insurance') DEFAULT 'cash',
    payment_status ENUM('pending','paid','partial','refunded') DEFAULT 'pending',
    reference_number VARCHAR(100),
    receipt_path VARCHAR(255),
    notes TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('general','promo','emergency','schedule_change') DEFAULT 'general',
    is_published TINYINT(1) DEFAULT 1,
    expires_at DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Testimonials
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    patient_name VARCHAR(150),
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('appointment','payment','announcement','system') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user (password: Admin@123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@reuchneclinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Doctor user (password: Doctor@123)
INSERT INTO users (username, email, password, role) VALUES
('dr_bermas', 'drbermas@reuchneclinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor');

-- Doctor profile
INSERT INTO doctor_profiles (user_id, full_name, specialty, education, bio, mission, vision, experience_years, qualifications, is_featured) VALUES
(2, 'Dr. Reuchne Mary Adelein A. Bermas',
'General Dentistry & Orthodontics',
'Doctor of Dental Medicine (DMD) – Centro Escolar University, Manila',
'Dr. Reuchne Mary Adelein A. Bermas is a passionate and dedicated dental professional committed to providing compassionate, high-quality dental care to every patient. With a warm and gentle approach, she ensures every visit feels comfortable and stress-free — especially for children and those with dental anxiety. She believes that a healthy smile is the foundation of confidence and overall well-being.',
'To provide exceptional dental care in a warm, welcoming environment where every patient feels valued, comfortable, and confident in their smile.',
'To be the most trusted dental clinic in the community — known not just for clinical excellence, but for the genuine care and compassion we give to every patient we serve.',
5,
'Doctor of Dental Medicine (DMD) – Centro Escolar University, Manila\nPhilippine Dental Association Member\nCertified in Basic Life Support (BLS)\nContinuous Professional Development in Orthodontics and Cosmetic Dentistry',
1);

-- Services
INSERT INTO services (name, description, price, duration_minutes, category, icon) VALUES
('Dental Cleaning', 'Professional cleaning to remove plaque, tartar, and surface stains for a brighter, healthier smile.', 800.00, 60, 'Preventive', '🦷'),
('Tooth Extraction', 'Safe and gentle removal of damaged or problematic teeth under local anesthesia.', 1200.00, 45, 'Surgical', '🔧'),
('Dental Filling', 'Tooth-colored composite fillings to restore cavities and damaged teeth naturally.', 1500.00, 60, 'Restorative', '✨'),
('Teeth Whitening', 'Professional in-office whitening treatment for a brilliantly white smile.', 5000.00, 90, 'Cosmetic', '🌟'),
('Orthodontic Braces', 'Metal or ceramic braces to correct teeth alignment and bite issues.', 35000.00, 90, 'Orthodontics', '😁'),
('Root Canal Treatment', 'Pain-relieving treatment to save infected or severely damaged teeth.', 8000.00, 120, 'Endodontic', '🏥'),
('Dental Crown', 'Custom-fitted crowns to protect and restore broken or weakened teeth.', 9000.00, 90, 'Restorative', '👑'),
('Dental Veneers', 'Thin porcelain shells that transform the appearance of your smile.', 12000.00, 90, 'Cosmetic', '💎'),
('Dentures', 'Custom removable dentures to replace missing teeth comfortably.', 15000.00, 60, 'Prosthetic', '🦷'),
('Pediatric Dentistry', 'Gentle dental care specially designed for children of all ages.', 800.00, 45, 'Preventive', '🧒'),
('Monthly Orthodontics Braces Adjustment', 'Monthly adjustment visit for orthodontic braces patients. Essential maintenance to ensure proper alignment and comfort.', 800.00, 45, 'Orthodontics', '🔧');

-- Doctor schedule
INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES
(2, 'Monday', '09:00:00', '17:00:00', 60),
(2, 'Tuesday', '09:00:00', '17:00:00', 60),
(2, 'Wednesday', '09:00:00', '17:00:00', 60),
(2, 'Thursday', '09:00:00', '17:00:00', 60),
(2, 'Friday', '09:00:00', '17:00:00', 60),
(2, 'Saturday', '09:00:00', '13:00:00', 60);

-- Sample testimonials
INSERT INTO testimonials (patient_name, content, rating, is_approved) VALUES
('Maria Santos', 'Dr. Bermas is absolutely wonderful! She made my root canal completely painless. I was so nervous but she calmed me down right away. Highly recommend!', 5, 1),
('Jose Reyes', 'The clinic is so clean and beautifully designed. Dr. Bermas is very professional and caring. My teeth have never looked better after the whitening treatment!', 5, 1),
('Ana Cruz', 'I brought my 5-year-old daughter here and Dr. Bermas was so patient and gentle with her. My daughter actually looks forward to dental visits now!', 5, 1),
('Mark Dela Rosa', 'Best dental experience I have ever had. The staff is friendly, the clinic is pristine, and Dr. Bermas is truly a skilled professional. 10/10!', 5, 1);

-- Sample announcement
INSERT INTO announcements (doctor_id, title, content, type) VALUES
(2, 'Welcome to Reuchne Tooth Fairy Clinic!', 'We are now accepting new patients! Book your appointment today and experience dental care like never before. First consultation is FREE for new patients this month!', 'promo');
