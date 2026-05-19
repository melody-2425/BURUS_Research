<?php
$notifications = getNotificationsForCurrentUser();
?>
<section class="page-header">
    <div>
        <h2>Notifications</h2>
        <p>Report status changes, official comments, maintenance notices, and community announcements.</p>
    </div>
</section>

<section class="filter-tabs">
    <span class="active">All Notifications</span>
    <span>Unread</span>
    <span>Important</span>
</section>

<section class="dashboard-grid">
    <div class="grid">
        <article class="card alert-card">
            <h2>Important Alert</h2>
            <p class="muted">Scheduled barangay maintenance may affect response times for non-urgent infrastructure reports.</p>
        </article>

        <article class="panel">
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <article class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                        <span class="notification-type <?php echo e($notification['type']); ?>"></span>
                        <div>
                            <strong><?php echo e($notification['title']); ?></strong>
                            <p class="muted"><?php echo e($notification['message']); ?></p>
                            <small><?php echo e($notification['date']); ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
                <article class="notification-item">
                    <span class="notification-type"></span>
                    <div><strong>Scheduled Maintenance</strong><p class="muted">Road clearing is scheduled near the public market this week.</p><small>Community announcement</small></div>
                </article>
                <article class="notification-item">
                    <span class="notification-type success"></span>
                    <div><strong>Profile Verified</strong><p class="muted">Your barangay resident profile is ready for report submissions.</p><small>Account notice</small></div>
                </article>
            </div>
        </article>
    </div>

    <aside class="quick-card">
        <h2>Configure Alerts</h2>
        <p>Choose which updates should appear in your notification center.</p>
        <div class="quick-list">
            <a>Report Updated</a>
            <a>New Comment from Official</a>
            <a>Community Announcements</a>
        </div>
    </aside>
</section>
