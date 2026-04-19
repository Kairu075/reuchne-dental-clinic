<?php
$pageTitle = 'Our Services';
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();

$category = sanitize($_GET['category'] ?? '');
$where = $category ? "WHERE is_active=1 AND category='{$db->real_escape_string($category)}'" : "WHERE is_active=1";
$services = $db->query("SELECT * FROM services $where ORDER BY category, price")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT DISTINCT category FROM services WHERE is_active=1 ORDER BY category")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:calc(72px + 3rem);padding-bottom:1rem;background:linear-gradient(135deg,var(--pink-50),white)">
    <div class="container">
        <div style="text-align:center" data-aos="fade-up">
            <span class="section-label">What We Offer</span>
            <h1 class="mt-2">Our <em style="color:var(--pink-500)">Dental Services</em></h1>
            <p style="max-width:560px;margin:1rem auto 0;color:var(--gray-500)">From routine checkups to complex treatments, we offer comprehensive dental solutions for the whole family.</p>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Category Filter -->
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:2.5rem;justify-content:center" data-aos="fade-up">
            <a href="?category=" class="<?= !$category ? 'btn-primary btn-sm' : 'btn-secondary btn-sm' ?>">All Services</a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat['category']) ?>"
               class="<?= $category === $cat['category'] ? 'btn-primary btn-sm' : 'btn-secondary btn-sm' ?>">
                <?= sanitize($cat['category']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Services Grid -->
        <div class="services-grid">
            <?php foreach ($services as $i => $svc): ?>
            <div class="card" style="padding:2rem" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 60 ?>">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                    <span style="font-size:2.5rem"><?= $svc['icon'] ?: '🦷' ?></span>
                    <span class="badge badge-pink"><?= sanitize($svc['category']) ?></span>
                </div>
                <h3 style="font-size:1.15rem;margin-bottom:0.5rem"><?= sanitize($svc['name']) ?></h3>
                <p style="font-size:0.88rem;color:var(--gray-500);margin-bottom:1.25rem"><?= sanitize($svc['description']) ?></p>
                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:1rem;border-top:1px solid var(--gray-100)">
                    <div>
                        <div style="color:var(--pink-500);font-weight:700;font-size:1.15rem">₱<?= number_format($svc['price'], 0) ?></div>
                        <div style="font-size:0.75rem;color:var(--gray-400)">~<?= $svc['duration_minutes'] ?> minutes</div>
                    </div>
                    <a href="<?= BASE_URL ?>/pages/book.php?service_id=<?= $svc['id'] ?>" class="btn-primary btn-sm">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($services)): ?>
        <div style="text-align:center;padding:4rem;color:var(--gray-400)">
            <div style="font-size:3rem;margin-bottom:1rem">🦷</div>
            <p>No services found in this category.</p>
            <a href="?" class="btn-secondary mt-2">View All Services</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
