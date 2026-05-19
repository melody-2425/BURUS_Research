<?php
$currentUser = getCurrentUser();
$currentPage = $currentPage ?? ($page ?? 'resident-dashboard');
$currentPageMeta = $currentPageMeta ?? [
    'title' => pageTitle($currentPage),
    'subtitle' => '',
    'show_search' => false,
    'search_placeholder' => '',
];
$unreadCount = count(array_filter(getNotificationsForCurrentUser(), fn($item) => !$item['is_read']));
$searchValue = $_GET['search'] ?? ($_GET['q'] ?? '');
?>
<header class="topbar">
    <div class="topbar-title">
        <h1><?php echo e($currentPageMeta['title']); ?></h1>
        <?php if (!empty($currentPageMeta['subtitle'])): ?>
            <p><?php echo e($currentPageMeta['subtitle']); ?></p>
        <?php endif; ?>
    </div>

    <div class="topbar-actions">
        <?php if (!empty($currentPageMeta['show_search'])): ?>
            <form class="topbar-search" method="get" action="index.php">
                <input type="hidden" name="page" value="<?php echo e($currentPage); ?>">
                <input
                    type="text"
                    name="search"
                    placeholder="<?php echo e($currentPageMeta['search_placeholder']); ?>"
                    value="<?php echo e($searchValue); ?>"
                >
            </form>
        <?php endif; ?>

        <button class="language-chip" type="button">Eng (US)</button>
        <a href="index.php?page=notifications" class="notification-chip" aria-label="Notifications">
            Notifications<?php if ($unreadCount > 0): ?> <strong><?php echo e($unreadCount); ?></strong><?php endif; ?>
        </a>
        <a class="profile-chip" href="index.php?page=profile">
            <span><?php echo e($currentUser['name'] ?? 'User'); ?></span>
            <small><?php echo e(roleLabel($currentUser)); ?></small>
        </a>
    </div>
</header>
