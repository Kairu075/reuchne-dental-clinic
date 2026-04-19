<?php
$pageTitle = 'Book Appointment';
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();

$services = $db->query("SELECT * FROM services WHERE is_active=1 ORDER BY category,name")->fetch_all(MYSQLI_ASSOC);
$doctors  = $db->query("SELECT u.id, dp.full_name, dp.specialty FROM users u JOIN doctor_profiles dp ON u.id=dp.user_id WHERE u.role='doctor' AND u.is_active=1")->fetch_all(MYSQLI_ASSOC);

$error = ''; $success = '';
$preService = (int)($_GET['service_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $patient_id = $_SESSION['user_id'];
    $doctor_id  = (int)($_POST['doctor_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $appt_date  = sanitize($_POST['appointment_date'] ?? '');
    $appt_time  = sanitize($_POST['appointment_time'] ?? '');
    $notes      = sanitize($_POST['patient_notes'] ?? '');

    if (!$doctor_id || !$service_id || !$appt_date || !$appt_time) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check double booking
        $chk = $db->prepare("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND status NOT IN ('cancelled')");
        $chk->bind_param("iss",$doctor_id,$appt_date,$appt_time);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This time slot is already booked. Please choose another.';
        } else {
            $ins = $db->prepare("INSERT INTO appointments (patient_id,doctor_id,service_id,appointment_date,appointment_time,patient_notes) VALUES (?,?,?,?,?,?)");
            $ins->bind_param("iiisss",$patient_id,$doctor_id,$service_id,$appt_date,$appt_time,$notes);
            if ($ins->execute()) {
                $appt_id = $db->insert_id;
                // Get service price for pending payment
                $svc = $db->prepare("SELECT price FROM services WHERE id=?");
                $svc->bind_param("i",$service_id);
                $svc->execute();
                $svcData = $svc->get_result()->fetch_assoc();
                // Create pending payment
                $pay = $db->prepare("INSERT INTO payments (appointment_id,patient_id,amount,payment_status) VALUES (?,?,?,'pending')");
                $pay->bind_param("iid",$appt_id,$patient_id,$svcData['price']);
                $pay->execute();
                // Notify doctor
                createNotification($doctor_id, 'New Appointment Request', 'A patient has booked an appointment for ' . $appt_date . ' at ' . $appt_time, 'appointment');
                $success = 'Your appointment has been booked successfully! We will confirm it shortly.';
            } else {
                $error = 'Booking failed. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:calc(72px + 3rem);background:linear-gradient(135deg,var(--pink-50),white);padding-bottom:2rem">
    <div class="container">
        <div style="text-align:center">
            <span class="section-label">Schedule a Visit</span>
            <h1 class="mt-2">Book an <em style="color:var(--pink-500)">Appointment</em></h1>
            <p style="color:var(--gray-500);margin-top:0.75rem">Choose your service, preferred date and time, and we'll confirm within 24 hours.</p>
        </div>
    </div>
</div>

<section class="section" style="padding-top:2rem">
    <div class="container" style="max-width:900px">

        <?php if (!isLoggedIn()): ?>
        <div class="alert alert-info" style="margin-bottom:2rem">
            <i class="fas fa-info-circle"></i>
            <div>Please <a href="<?= BASE_URL ?>/pages/login.php" style="color:var(--pink-500);font-weight:600">login</a> or <a href="<?= BASE_URL ?>/pages/register.php" style="color:var(--pink-500);font-weight:600">create an account</a> to book an appointment.
            <br><small>You can browse services without logging in.</small></div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error" data-dismiss="5000"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= $success ?> <a href="<?= BASE_URL ?>/patient/appointments.php" style="color:var(--pink-500)">View my appointments →</a></div>
        <?php endif; ?>

        <form method="POST" action="" <?= !isLoggedIn() ? 'onsubmit="alert(\'Please login first\');return false;"' : '' ?>>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem" class="responsive-grid-1">
                <!-- Left: Service & Doctor -->
                <div>
                    <div class="card" style="padding:1.75rem;margin-bottom:1.5rem">
                        <h4 style="margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem">
                            <span style="color:var(--pink-500)">1.</span> Choose Service
                        </h4>
                        <div class="form-group mb-0">
                            <select name="service_id" id="service_id" class="form-control" required>
                                <option value="">-- Select a service --</option>
                                <?php
                                $prevCat = '';
                                foreach ($services as $svc):
                                    if ($svc['category'] !== $prevCat):
                                        if ($prevCat) echo '</optgroup>';
                                        echo '<optgroup label="' . sanitize($svc['category']) . '">';
                                        $prevCat = $svc['category'];
                                    endif;
                                ?>
                                <option value="<?= $svc['id'] ?>"
                                        data-price="<?= $svc['price'] ?>"
                                        data-duration="<?= $svc['duration_minutes'] ?>"
                                        <?= $preService == $svc['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($svc['name']) ?> — ₱<?= number_format($svc['price'],0) ?>
                                </option>
                                <?php endforeach; if ($prevCat) echo '</optgroup>'; ?>
                            </select>
                        </div>
                        <div id="serviceInfo" style="margin-top:1rem;display:none;background:var(--pink-50);padding:0.75rem;border-radius:var(--radius-sm)">
                            <div style="font-size:0.85rem;color:var(--gray-600)">
                                <span id="servicePriceDisplay"></span> • <span id="serviceDurationDisplay"></span>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="padding:1.75rem">
                        <h4 style="margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem">
                            <span style="color:var(--pink-500)">2.</span> Choose Doctor
                        </h4>
                        <?php foreach ($doctors as $doc): ?>
                        <label style="display:flex;align-items:center;gap:1rem;padding:1rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-md);cursor:pointer;margin-bottom:0.75rem;transition:all 0.2s" class="doctor-radio-label">
                            <input type="radio" name="doctor_id" id="doctor_id" value="<?= $doc['id'] ?>" <?= $doc['id'] == 2 ? 'checked' : '' ?> style="accent-color:var(--pink-500)">
                            <div style="width:44px;height:44px;border-radius:50%;background:var(--pink-100);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">👩‍⚕️</div>
                            <div>
                                <div style="font-weight:600;font-size:0.9rem"><?= sanitize($doc['full_name']) ?></div>
                                <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($doc['specialty']) ?></div>
                            </div>
                        </label>
                        <?php endforeach; ?>

                        <div class="form-group mt-3">
                            <label class="form-label">Additional Notes (optional)</label>
                            <textarea name="patient_notes" class="form-control" rows="3" placeholder="Any special concerns or requests..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right: Calendar & Time -->
                <div>
                    <div class="card" style="padding:1.75rem;margin-bottom:1.5rem">
                        <h4 style="margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem">
                            <span style="color:var(--pink-500)">3.</span> Choose Date
                        </h4>
                        <div class="calendar-wrapper">
                            <div class="calendar-header">
                                <button type="button" class="calendar-nav" id="calPrev"><i class="fas fa-chevron-left"></i></button>
                                <h3 id="calMonth"></h3>
                                <button type="button" class="calendar-nav" id="calNext"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div class="calendar-grid">
                                <div class="calendar-days">
                                    <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?>
                                    <div class="calendar-day-label"><?= $d ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="calendar-cells" id="calCells"></div>
                            </div>
                        </div>
                        <input type="hidden" name="appointment_date" id="appointment_date" required>
                        <div id="selectedDateDisplay" style="margin-top:0.75rem;font-size:0.85rem;color:var(--gray-500);text-align:center"></div>
                    </div>

                    <div class="card" style="padding:1.75rem">
                        <h4 style="margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem">
                            <span style="color:var(--pink-500)">4.</span> Choose Time
                        </h4>
                        <div id="timeSlots" style="color:var(--gray-400);font-size:0.88rem">
                            <div style="text-align:center;padding:2rem">
                                <i class="fas fa-calendar" style="font-size:2rem;color:var(--pink-200);display:block;margin-bottom:0.75rem"></i>
                                Please select a date first
                            </div>
                        </div>
                        <input type="hidden" name="appointment_time" id="appointment_time" required>
                    </div>

                    <?php if (isLoggedIn()): ?>
                    <button type="submit" class="btn-primary w-full mt-3" style="padding:1rem">
                        <i class="fas fa-calendar-check"></i> Confirm Booking
                    </button>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/pages/login.php" class="btn-primary w-full mt-3" style="padding:1rem;text-align:center;display:block">
                        <i class="fas fa-sign-in-alt"></i> Login to Book
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<?php $extraJS = '<script>
// Disabled days: Sunday=0, Sunday not available by default
window.disabledDays = [0];
window.bookedDates = [];

// Show service info
document.getElementById("service_id").addEventListener("change", function() {
    const opt = this.options[this.selectedIndex];
    const price = opt.dataset.price;
    const dur = opt.dataset.duration;
    const info = document.getElementById("serviceInfo");
    if (price) {
        document.getElementById("servicePriceDisplay").textContent = "₱" + parseFloat(price).toLocaleString("en-PH", {minimumFractionDigits:2});
        document.getElementById("serviceDurationDisplay").textContent = dur + " minutes";
        info.style.display = "block";
    } else {
        info.style.display = "none";
    }
});

// Trigger if preselected
if (document.getElementById("service_id").value) {
    document.getElementById("service_id").dispatchEvent(new Event("change"));
}

// Doctor radio style
document.querySelectorAll(".doctor-radio-label").forEach(label => {
    const radio = label.querySelector("input");
    if (radio.checked) label.style.borderColor = "var(--pink-400)";
    radio.addEventListener("change", () => {
        document.querySelectorAll(".doctor-radio-label").forEach(l => l.style.borderColor = "var(--gray-200)");
        label.style.borderColor = "var(--pink-400)";
        // Reload time slots if date selected
        const dateVal = document.getElementById("appointment_date").value;
        if (dateVal) loadTimeSlots(dateVal);
    });
});

// Update date display
const dateInput = document.getElementById("appointment_date");
if (dateInput) {
    const observer = new MutationObserver(() => {
        const d = dateInput.value;
        const disp = document.getElementById("selectedDateDisplay");
        if (d && disp) {
            const opts = {weekday:"long",year:"numeric",month:"long",day:"numeric"};
            disp.textContent = "Selected: " + new Date(d + "T00:00:00").toLocaleDateString("en-PH", opts);
            disp.style.color = "var(--pink-500)";
            disp.style.fontWeight = "600";
        }
    });
    observer.observe(dateInput, {attributes:true, attributeFilter:["value"]});
}
</script>'; ?>

<style>
@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
