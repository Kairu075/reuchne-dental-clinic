<?php
$pageTitle = 'Meet the Doctor';
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();

$stmt = $db->prepare("SELECT dp.*, u.email FROM doctor_profiles dp JOIN users u ON dp.user_id = u.id WHERE dp.is_featured = 1 LIMIT 1");
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

$gallery = $doctor ? json_decode($doctor['gallery_photos'] ?? '[]', true) : [];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Doctor Hero -->
<section class="doctor-hero" style="padding-top:calc(72px + 4rem)">
    <div class="container">
        <?php if ($doctor): ?>
        <div class="doctor-hero-grid">
            <!-- Photo Gallery -->
            <div class="doctor-photo-gallery" data-aos="fade-right">
                <div class="gallery-main">
                    <img id="mainDoctorImg"
                         src="<?= BASE_URL ?>/assets/images/placeholders/doc_reu.jpg"
                         alt="Dr. <?= sanitize($doctor['full_name']) ?>"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'500\'><rect fill=\'%23fce7f3\' width=\'400\' height=\'500\'/><text x=\'200\' y=\'260\' text-anchor=\'middle\' fill=\'%23f04e7d\' font-size=\'120\'>👩‍⚕️</text></svg>'">
                </div>
                <div class="gallery-thumbs">
                    <?php
                    $placeholders = ['doc_reu.jpg','doc_reu2.jpg','doc_reu3.jpg','doc_reu4.jpg'];
                    foreach ($placeholders as $img): ?>
                    <div class="gallery-thumb">
                        <img src="<?= BASE_URL ?>/assets/images/placeholders/<?= $img ?>"
                             alt="Dr. Bermas"
                             onerror="this.parentElement.style.background='var(--pink-100)';this.style.display='none'">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Doctor Info -->
            <div data-aos="fade-left">
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
                    <span class="badge badge-pink"><i class="fas fa-certificate"></i> Licensed Dentist</span>
                    <span class="badge" style="background:#dbeafe;color:#1e40af"><i class="fas fa-graduation-cap"></i> CEU Manila</span>
                    <span class="badge" style="background:#dcfce7;color:#166534"><i class="fas fa-star"></i> 5.0 Rated</span>
                </div>
                <h1 class="doctor-name"><?= sanitize($doctor['full_name']) ?></h1>
                <div class="doctor-title-badge">
                    <i class="fas fa-stethoscope"></i>
                    <?= sanitize($doctor['specialty']) ?>
                </div>
                <p class="doctor-bio"><?= nl2br(sanitize($doctor['bio'])) ?></p>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:1.5rem 0">
                    <div style="text-align:center;background:var(--pink-50);border-radius:var(--radius-md);padding:1.25rem">
                        <div style="font-size:2rem;font-family:var(--font-display);color:var(--pink-500);font-weight:600"><?= $doctor['experience_years'] ?>+</div>
                        <div style="font-size:0.78rem;color:var(--gray-500)">Years Experience</div>
                    </div>
                    <div style="text-align:center;background:var(--pink-50);border-radius:var(--radius-md);padding:1.25rem">
                        <div style="font-size:2rem;font-family:var(--font-display);color:var(--pink-500);font-weight:600">500+</div>
                        <div style="font-size:0.78rem;color:var(--gray-500)">Happy Patients</div>
                    </div>
                    <div style="text-align:center;background:var(--pink-50);border-radius:var(--radius-md);padding:1.25rem">
                        <div style="font-size:2rem;font-family:var(--font-display);color:var(--pink-500);font-weight:600">5.0</div>
                        <div style="font-size:0.78rem;color:var(--gray-500)">Patient Rating</div>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/pages/book.php" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Book Appointment with Dr. Bermas
                </a>
            </div>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:4rem">
            <div style="font-size:4rem;margin-bottom:1rem">👩‍⚕️</div>
            <h2>Doctor profile coming soon</h2>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($doctor): ?>
<!-- Education & Qualifications -->
<section class="section section-alt">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem" class="responsive-grid-1">
            <!-- Education -->
            <div data-aos="fade-up">
                <span class="section-label">Education</span>
                <h3 class="mt-2 mb-3">Academic Background</h3>
                <div class="card" style="padding:2rem">
                    <div style="display:flex;gap:1.25rem;align-items:flex-start">
<div style="width:56px;height:56px;background:var(--pink-100);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <img src="<?= BASE_URL ?>/assets/images/ceu-logo.png" alt="CEU Logo" style="width:56px;height:52px;object-fit:contain;" onerror="this.style.display='none';this.parentElement.style.backgroundImage='url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTYiIGN5PSIxNiIgcj0iMTYiIGZpbGw9IiNGRkU3RjMiLz4KPHRleHQgeD0iMTYiIHk9IjIxIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LXNpemU9IjE0IiBmaWxsPSIjRjA0RTdEIj7wn4Pwn6Pwn6Pwg8J+Do8J+DwCI8L3RleHQ+Cjwvc3ZnPg==)'">
</div>
                        <div>
                            <h4 style="color:var(--pink-600);margin-bottom:0.25rem">Doctor of Dental Medicine (DMD)</h4>
                            <div style="font-weight:600;font-size:0.9rem;margin-bottom:0.25rem">Centro Escolar University</div>
                            <div style="font-size:0.82rem;color:var(--gray-500)">Manila, Philippines</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Qualifications -->
            <div data-aos="fade-up" data-aos-delay="100">
                <span class="section-label">Credentials</span>
                <h3 class="mt-2 mb-3">Qualifications</h3>
                <ul class="qual-list">
                    <?php
                    $quals = explode("\n", $doctor['qualifications'] ?? '');
                    foreach ($quals as $q): if (!trim($q)) continue; ?>
                    <li><i class="fas fa-check-circle"></i><?= sanitize(trim($q)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Specializations -->
<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Expertise</span>
            <h2>Areas of <em style="color:var(--pink-500)">Specialization</em></h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(6, 1fr);gap:1.25rem">
            <?php
            $specs = [
                ['icon'=>'🦷','name'=>'General Dentistry','desc'=>'Comprehensive oral health care for all ages'],
                ['icon'=>'😁','name'=>'Orthodontics','desc'=>'Braces and teeth alignment solutions'],
                ['icon'=>'✨','name'=>'Cosmetic Dentistry','desc'=>'Whitening, veneers, and smile makeovers'],
                ['icon'=>'🧒','name'=>'Pediatric Dentistry','desc'=>'Gentle care for children and teens'],
                ['icon'=>'🏥','name'=>'Endodontics','desc'=>'Root canal and pulp treatments'],
                ['icon'=>'👑','name'=>'Prosthodontics','desc'=>'Crowns, bridges, and dentures'],
            ];
            foreach ($specs as $spec): ?>
            <div class="card" style="padding:1.75rem;text-align:center" data-aos="fade-up">
                <div style="font-size:2.2rem;margin-bottom:0.75rem"><?= $spec['icon'] ?></div>
                <h4 style="margin-bottom:0.4rem;font-size:0.95rem"><?= $spec['name'] ?></h4>
                <p style="font-size:0.8rem;color:var(--gray-400)"><?= $spec['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section section-alt">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem" class="responsive-grid-1">
            <div class="card" style="padding:2.5rem;border-top:4px solid var(--pink-400)" data-aos="fade-up">
                <div style="font-size:2rem;margin-bottom:1rem">🌟</div>
                <h3 style="color:var(--pink-600);margin-bottom:1rem">Mission</h3>
                <p style="font-style:italic;line-height:1.9">"<?= sanitize($doctor['mission']) ?>"</p>
            </div>
            <div class="card" style="padding:2.5rem;border-top:4px solid var(--pink-300)" data-aos="fade-up" data-aos-delay="100">
                <div style="font-size:2rem;margin-bottom:1rem">💎</div>
                <h3 style="color:var(--pink-600);margin-bottom:1rem">Vision</h3>
                <p style="font-style:italic;line-height:1.9">"<?= sanitize($doctor['vision']) ?>"</p>
            </div>
        </div>
    </div>
</section>

<!-- Book CTA -->
<section class="section" style="background:linear-gradient(135deg,var(--pink-500),var(--pink-600));color:white;text-align:center">
    <div class="container">
        <div data-aos="fade-up">
            <h2 style="color:white;margin-bottom:1rem">Book Your Appointment with Dr. Bermas</h2>
            <p style="color:rgba(255,255,255,0.85);max-width:480px;margin:0 auto 2rem">Experience the magical, compassionate dental care that has made hundreds of patients smile.</p>
            <a href="<?= BASE_URL ?>/pages/book.php" class="btn-white">
                <i class="fas fa-calendar-plus"></i> Schedule Now
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
