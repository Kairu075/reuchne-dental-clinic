    <footer class="site-footer" id="contact">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-white-transparent.png" alt="Reuchne Logo" class="footer-logo-img">
                    <div>
                        <span class="footer-name">Reuchne Tooth Fairy Clinic</span>
                        <span class="footer-tagline">Magical Care for Your Perfect Smile</span>
                    </div>
                </div>
                <p class="footer-desc">Providing compassionate, high-quality dental care in a warm and welcoming environment. Your perfect smile is our passion.</p>
                <div class="footer-socials">
                    <a href="https://www.facebook.com/rchnb" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="https://www.instagram.com/rchne.b" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@.madylily" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/doctor-profile.php">Our Doctor</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/services.php">Services</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/book.php">Book Appointment</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact Us</h4>
                <div class="contact-item"><i class="fas fa-map-marker-alt"></i><span>14 Sampaguita Street, San Mateo Rizal, Philippines</span></div>
                <div class="contact-item"><i class="fas fa-phone"></i><span>+63 994 173 1251</span></div>
                <div class="contact-item"><i class="fas fa-envelope"></i><span>bruechne@gmail.com</span></div>
                <div class="contact-item"><i class="fas fa-clock"></i>
                    <span>Mon–Fri: 9AM–5PM<br>Saturday: 9AM–1PM</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> © 2025 Reuchne Tooth Fairy Clinic. Disclaimer: This website is a personal project created for demonstration and educational purposes as a dental clinic booking and management system. Developed by Kyle Dominic Yap.<span style="color:var(--pink-400)">♥</span></p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <?= isset($extraJS) ? $extraJS : '' ?>
</body>
</html>
