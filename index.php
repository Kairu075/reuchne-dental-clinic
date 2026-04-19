<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/auth.php';
$db = getDB();

// Fetch services
$services = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY category, price LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Fetch testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);

// Fetch featured doctor
$doctorStmt = $db->prepare("SELECT dp.*, u.email FROM doctor_profiles dp JOIN users u ON dp.user_id = u.id WHERE dp.is_featured = 1 LIMIT 1");
$doctorStmt->execute();
$doctor = $doctorStmt->get_result()->fetch_assoc();

// Fetch latest announcement
$announcements = $db->query("SELECT a.*, dp.full_name FROM announcements a JOIN doctor_profiles dp ON a.doctor_id = dp.user_id WHERE a.is_published = 1 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE()) ORDER BY a.created_at DESC LIMIT 1")->fetch_assoc();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($announcements): ?>
<div class="announcement-bar">
    <i class="fas fa-star"></i>
    <strong><?= sanitize($announcements['title']) ?>:</strong>
    <?= sanitize(substr($announcements['content'], 0, 100)) ?>...
    <a href="<?= BASE_URL ?>/pages/services.php">Learn More</a>
</div>
<style>.hero { margin-top: calc(72px + 38px) !important; }</style>
<?php endif; ?>

<!-- HERO -->
<section class="hero">
    <div class="carousel-wrapper">
        <!-- Slide 1: Hero -->
        <div class="carousel-slide">
            <img src="<?= BASE_URL ?>/assets/images/placeholders/clinic-1.jpg" alt="Clinic Interior" onerror="this.style.background='linear-gradient(135deg,#fff0f4,#ffc0d3)';this.style.objectFit='none'">
            <div class="carousel-overlay"></div>
        </div>
        <!-- Slide 2: Doctor -->
        <div class="carousel-slide">
            <img src="<?= BASE_URL ?>/assets/images/placeholders/clinic-4.jpg" alt="Dr. Bermas" onerror="this.style.background='linear-gradient(135deg,#fce7f3,#fbcfe8)';this.style.objectFit='none'">
            <div class="carousel-overlay"></div>
        </div>
        <!-- Slide 3: Services -->
        <div class="carousel-slide">
            <img src="<?= BASE_URL ?>/assets/images/placeholders/clinic-2.jpg" alt="Clinic Services" onerror="this.style.background='linear-gradient(135deg,#fff1f2,#ffe4e6)';this.style.objectFit='none'">
            <div class="carousel-overlay"></div>
        </div>
        <!-- Slide 4 -->
        <div class="carousel-slide">
            <img src="<?= BASE_URL ?>/assets/images/placeholders/clinic-3.jpg" alt="Clinic Highlight" onerror="this.style.background='linear-gradient(135deg,#fdf2f8,#fce7f3)';this.style.objectFit='none'">
            <div class="carousel-overlay"></div>
        </div>

        <!-- Content (always visible) -->
        <div class="carousel-content">
            <div class="hero-brand">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-white-transparent.png" alt="Reuchne Tooth Fairy Clinic Logo" class="hero-logo">
                <div>
                    <h1 class="hero-title">
                        <span class="hero-title-main">Reuchne</span>
                        <span class="hero-title-sub">Tooth Fairy Clinic</span>
                    </h1>
                </div>
            </div>
            <p class="hero-tagline">Magical Care for Your Perfect Smile</p>
            <div class="hero-cta">
                <a href="<?= BASE_URL ?>/pages/book.php" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
                <a href="<?= BASE_URL ?>/pages/doctor-profile.php" class="btn-secondary">
                    <i class="fas fa-user-md"></i> Meet the Doctor
                </a>
                <?php if (!isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/pages/login.php" class="btn-white">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Carousel Controls -->
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
        <div class="carousel-controls">
            <button class="carousel-dot"></button>
            <button class="carousel-dot"></button>
            <button class="carousel-dot"></button>
            <button class="carousel-dot"></button>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Why Choose Us</span>
            <h2>Dental Care With a <em style="color:var(--pink-500)">Magical Touch</em></h2>
            <p>We combine professional excellence with genuine warmth to give you the smile you deserve.</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem">
            <?php
            $features = [
                ['icon'=>'fas fa-tooth','title'=>'Pain-Free Experience','desc'=>'Modern techniques for comfortable, anxiety-free dental visits.'],
                ['icon'=>'fas fa-user-md','title'=>'Expert Doctor','desc'=>'Highly qualified dental professional with years of experience.'],
                ['icon'=>'fas fa-shield-alt','title'=>'Safe & Sterile','desc'=>'Strict sterilization protocols for your safety and peace of mind.'],
                ['icon'=>'fas fa-heart','title'=>'Patient-Centered','desc'=>'Your comfort and satisfaction are at the heart of everything we do.'],
            ];
            foreach ($features as $f): ?>
            <div class="card" style="padding:2rem;text-align:center" data-aos="fade-up">
                <div style="width:60px;height:60px;background:var(--pink-100);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.4rem;color:var(--pink-500)">
                    <i class="<?= $f['icon'] ?>"></i>
                </div>
                <h4 style="margin-bottom:0.5rem"><?= $f['title'] ?></h4>
                <p style="font-size:0.88rem;color:var(--gray-500)"><?= $f['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SERVICES PREVIEW -->
<section class="section section-alt" id="services">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Our Services</span>
            <h2>Comprehensive <em style="color:var(--pink-500)">Dental Solutions</em></h2>
            <p>From routine cleanings to cosmetic transformations, we offer a full range of dental services.</p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $i => $svc): ?>
            <div class="card service-card" data-aos="fade-up" data-aos-delay="<?= $i * 60 ?>">
                <span class="service-icon"><?= $svc['icon'] ?: '🦷' ?></span>
                <h3><?= sanitize($svc['name']) ?></h3>
                <p><?= sanitize(substr($svc['description'], 0, 80)) ?>...</p>
                <div class="service-price">Starting at ₱<?= number_format($svc['price'], 0) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:2.5rem">
            <a href="<?= BASE_URL ?>/pages/services.php" class="btn-secondary">View All Services <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- DOCTOR PREVIEW -->
<?php if ($doctor): ?>
<section class="section" id="about">
    <div class="container">
        <div class="doctor-preview">
            <div class="doctor-img-wrap" data-aos="fade-right">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/doc_reu.jpg"
                     alt="Dr. <?= sanitize($doctor['full_name']) ?>"
                     onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'500\' viewBox=\'0 0 400 500\'><rect fill=\'%23fce7f3\' width=\'400\' height=\'500\'/><text x=\'200\' y=\'250\' text-anchor=\'middle\' fill=\'%23f04e7d\' font-size=\'80\'>👩‍⚕️</text></svg>'">
                <div class="doctor-img-badge">
                    <div style="font-size:1.5rem">🌟</div>
                    <div>
                        <div style="font-weight:700;font-size:0.85rem;color:var(--gray-900)">Top Rated Doctor</div>
                        <div style="font-size:0.75rem;color:var(--gray-500)">5.0 ★ Patient Satisfaction</div>
                    </div>
                </div>
            </div>
            <div class="doctor-content" data-aos="fade-left">
                <span class="section-label">Meet the Doctor</span>
                <h2 class="mt-2"><?= sanitize($doctor['full_name']) ?></h2>
                <div class="doctor-title-badge">
                    <i class="fas fa-stethoscope"></i>
                    <?= sanitize($doctor['specialty']) ?>
                </div>
                <div class="doctor-meta">
                    <div class="doctor-meta-item">
                        <strong><?= $doctor['experience_years'] ?>+</strong>
                        <span>Years Experience</span>
                    </div>
                    <div class="doctor-meta-item">
                        <strong>500+</strong>
                        <span>Happy Patients</span>
                    </div>
                    <div class="doctor-meta-item">
                        <strong>CEU</strong>
                        <span>Manila Graduate</span>
                    </div>
                </div>
                <p style="margin-bottom:1.5rem"><?= sanitize(substr($doctor['bio'], 0, 280)) ?>...</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap">
                    <a href="<?= BASE_URL ?>/pages/doctor-profile.php" class="btn-primary">
                        <i class="fas fa-user-md"></i> View Full Profile
                    </a>
                    <a href="<?= BASE_URL ?>/pages/book.php" class="btn-secondary">
                        <i class="fas fa-calendar-plus"></i> Book with Dr. Bermas
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS -->
<?php if (!empty($testimonials)): ?>
<section class="section section-alt" id="testimonials">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Testimonials</span>
            <h2>What Our <em style="color:var(--pink-500)">Patients Say</em></h2>
            <p>Real stories from real patients who found their perfect smile with us.</p>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="card testimonial-card" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="stars"><?= str_repeat('★', $t['rating']) ?></div>
                <p>"<?= sanitize($t['content']) ?>"</p>
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--pink-200);display:flex;align-items:center;justify-content:center;font-size:1rem">👤</div>
                    <div>
                        <div class="testimonial-author"><?= sanitize($t['patient_name']) ?></div>
                        <div style="font-size:0.78rem;color:var(--gray-400)">Verified Patient</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA SECTION -->
<section class="section" style="background:linear-gradient(135deg,var(--pink-500),var(--pink-600));color:white;text-align:center">
    <div class="container">
        <div data-aos="fade-up">
            <div style="font-size:3rem;margin-bottom:1rem">🦷</div>
            <h2 style="color:white;margin-bottom:1rem">Ready for Your Perfect Smile?</h2>
            <p style="color:rgba(255,255,255,0.85);max-width:500px;margin:0 auto 2rem">Book your appointment today and experience dental care like never before. First consultation is FREE for new patients!</p>
            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                <a href="<?= BASE_URL ?>/pages/book.php" class="btn-white">
                    <i class="fas fa-calendar-plus"></i> Book Consultation
                </a>
                <?php if (!isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/pages/register.php" class="btn-secondary" style="border-color:rgba(255,255,255,0.6);color:white">
                    Create Account
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
