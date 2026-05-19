<?php
$user = getCurrentUser();
$barangay = getCurrentBarangay();
?>
<section class="profile-layout">
    <aside class="profile-card">
        <span class="avatar large"><?php echo e(substr($user['name'], 0, 1)); ?></span>
        <h2><?php echo e($user['name']); ?></h2>
        <p class="muted"><?php echo e(roleLabel($user)); ?> of <?php echo e($barangay['name']); ?></p>
        <nav class="settings-menu">
            <a class="active" href="index.php?page=profile">Profile Information</a>
            <a href="index.php?page=profile">Security</a>
            <a href="index.php?page=profile">Notifications</a>
            <a href="index.php?page=profile">Login History</a>
        </nav>
        <a class="btn btn-outline full" href="logout.php">Sign Out</a>
    </aside>

    <div class="profile-main-panel">
        <div class="settings-grid">
            <article class="settings-card">
                <h2>Profile Information</h2>
                <form class="form-grid">
                    <label class="form-group">Full Name<input class="form-control" type="text" value="<?php echo e($user['name']); ?>"></label>
                    <label class="form-group">Email<input class="form-control" type="email" value="<?php echo e($user['email']); ?>"></label>
                    <label class="form-group">Contact<input class="form-control" type="text" value="<?php echo e($user['contact']); ?>"></label>
                    <label class="form-group">Barangay<input class="form-control" type="text" value="<?php echo e($barangay['name']); ?>"></label>
                    <label class="form-group full">Address<input class="form-control" type="text" value="<?php echo e($user['address']); ?>"></label>
                    <div class="form-group full"><button class="btn btn-primary" type="button">Save Profile</button></div>
                </form>
            </article>

            <article class="settings-card">
                <h2>Security & Authentication</h2>
                <div class="toggle-list">
                    <label><input type="checkbox" checked> Email login enabled</label>
                    <label><input type="checkbox"> Two-step verification</label>
                </div>
            </article>

            <article class="settings-card">
                <h2>Notification Preferences</h2>
                <div class="toggle-list">
                    <label><input type="checkbox" checked> Status updates</label>
                    <label><input type="checkbox" checked> Official replies</label>
                    <label><input type="checkbox"> Community announcements</label>
                </div>
            </article>

            <article class="settings-card">
                <h2>Login History</h2>
                <div class="table-wrap">
                    <table class="report-table">
                        <thead><tr><th>Date</th><th>Device</th><th>Location</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td>May 19, 2026</td><td>Browser Session</td><td><?php echo e($barangay['city']); ?></td><td><span class="status-badge status-resolved">Active</span></td></tr>
                            <tr><td>May 18, 2026</td><td>Prototype Login</td><td><?php echo e($barangay['city']); ?></td><td><span class="status-badge status-neutral">Completed</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="settings-card alert-card">
                <h2>Danger Zone</h2>
                <p class="muted">Account deletion and credential reset controls are disabled in this prototype.</p>
                <button class="btn btn-outline" type="button">Request Support</button>
            </article>
        </div>
    </div>
</section>
