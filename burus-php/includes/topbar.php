<?php
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$unreadCount = count(array_filter(getNotificationsForCurrentUser(), fn($item) => !$item['is_read']));
?>
<header class="topbar">
    <div>
        <span class="mini-label"><?php echo e($barangay['city']); ?></span>
        <h1><?php echo e(pageTitle($page ?? 'landing')); ?></h1>
    </div>
    <form class="search-box" method="get" action="index.php">
        <input type="hidden" name="page" value="<?php echo e($page ?? 'resident-dashboard'); ?>">
        <input type="search" name="q" placeholder="Search tickets, locations, residents">
    </form>
    <div class="top-actions">
        <button class="chip-button" type="button">EN</button>
        <a class="icon-button" href="index.php?page=notifications" aria-label="Notifications">
            <span></span>
            <?php if ($unreadCount > 0): ?><b><?php echo e($unreadCount); ?></b><?php endif; ?>
        </a>
        <a class="profile-pill" href="index.php?page=profile">
            <span class="avatar"><?php echo e(substr($user['name'] ?? 'G', 0, 1)); ?></span>
            <span>
                <strong><?php echo e($user['name'] ?? 'Guest'); ?></strong>
                <small><?php echo e($barangay['name']); ?></small>
            </span>
        </a>
    </div>
</header>
