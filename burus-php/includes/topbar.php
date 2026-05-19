<?php
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$unreadCount = count(array_filter(getNotificationsForCurrentUser(), fn($item) => !$item['is_read']));
?>
<header class="topbar">
    <form class="search-box" method="get" action="index.php">
        <input type="hidden" name="page" value="<?php echo e($page ?? 'resident-dashboard'); ?>">
        <input class="search-input" type="search" name="q" value="<?php echo e($_GET['q'] ?? ''); ?>" placeholder="Search tickets, locations, residents">
    </form>
    <div class="top-actions">
        <button class="chip-button language-chip" type="button">EN</button>
        <a class="icon-button notification-chip" href="index.php?page=notifications" aria-label="Notifications">
            <span></span>
            <?php if ($unreadCount > 0): ?><b><?php echo e($unreadCount); ?></b><?php endif; ?>
        </a>
        <a class="profile-chip" href="index.php?page=profile">
            <span class="avatar"><?php echo e(substr($user['name'] ?? 'G', 0, 1)); ?></span>
            <span>
                <strong><?php echo e($user['name'] ?? 'Guest'); ?></strong>
                <small><?php echo e(roleLabel($user)); ?></small>
            </span>
        </a>
    </div>
</header>
