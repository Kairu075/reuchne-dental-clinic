<!-- Logout Confirmation Modal -->
<div class="modal-backdrop" id="logoutModal">
    <div class="modal" style="max-width: 440px; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); overflow: hidden;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid var(--pink-100); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-sign-out-alt" style="color: var(--pink-400); font-size: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: var(--gray-900);">Confirm Logout</h3>
            </div>
            <button class="modal-close" style="font-size: 1.25rem; width: 32px; height: 32px; border-radius: 50%; background: var(--pink-50); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--gray-500); transition: var(--transition);">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <p style="margin: 0; color: var(--gray-700); line-height: 1.6;">Are you sure you want to log out of your account? You will need to sign in again to continue.</p>
        </div>
        <div class="modal-footer" style="justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid var(--pink-100);">
            <button class="btn-secondary" onclick="document.getElementById('logoutModal').classList.remove('open')">Cancel</button>
            <a href="<?= BASE_URL ?>/api/logout.php" class="btn-primary" style="background: var(--pink-500); border: none; box-shadow: var(--shadow-sm);">Log Out</a>
        </div>
    </div>
</div>
