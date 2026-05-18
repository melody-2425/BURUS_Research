<section class="panel">
    <div class="panel-heading">
        <div><h2>Notifications</h2><p>In-app updates for accepted, changed, and resolved reports.</p></div>
    </div>
    <div class="notification-list">
        <?php foreach (getNotificationsForCurrentUser() as $notification): ?>
            <article class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                <span class="notification-type <?php echo e($notification['type']); ?>"></span>
                <div>
                    <strong><?php echo e($notification['title']); ?></strong>
                    <p><?php echo e($notification['message']); ?></p>
                    <small><?php echo e($notification['date']); ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
