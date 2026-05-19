<?php
$currentUser = getCurrentUser();
$currentPage = $page ?? '';

$residentLinks = [
    'resident-dashboard' => 'Dashboard',
    'my-reports' => 'My Reports',
    'report-new' => 'Report New Issue',
    'map-view' => 'Map View',
    'messages' => 'Feedback',
    'notifications' => 'Notifications',
    'profile' => 'Account Settings',
];

$officialLinks = [
    'official-dashboard' => 'Dashboard',
    'issue-reports' => 'Issue Reports',
    'map-view' => 'Map View',
    'messages' => 'Feedback',
    'notifications' => 'Notifications',
    'profile' => 'Profile',
];

$adminLinks = [
    'admin-dashboard' => 'Dashboard',
    'issue-reports' => 'Issue Reports',
    'resident-directory' => 'Resident Directory',
    'analytics' => 'Analytics',
    'system-settings' => 'System Settings',
    'notifications' => 'Notifications',
];

if (isAdmin($currentUser)) {
    $links = $adminLinks;
} elseif (isOfficial($currentUser)) {
    $links = $officialLinks;
} else {
    $links = $residentLinks;
}
?>
<aside class="sidebar">
    <a class="sidebar-logo" href="index.php?page=<?php echo e(routeForUser($currentUser)); ?>">
        <span class="sidebar-mark">B</span>
        <span>
            <strong>BURUS</strong>
            <small>Barangay Utility Reporting</small>
        </span>
    </a>

    <nav class="sidebar-nav">
        <?php foreach ($links as $linkPage => $label): ?>
            <a class="nav-item <?php echo $currentPage === $linkPage ? 'active' : ''; ?>" href="index.php?page=<?php echo e($linkPage); ?>">
                <span class="nav-dot"></span>
                <?php echo e($label); ?>
            </a>
        <?php endforeach; ?>
        <a class="nav-item" href="logout.php">
            <span class="nav-dot"></span>
            Sign Out
        </a>
    </nav>

    <div class="side-card">
        <span class="mini-label">Current Role</span>
        <strong><?php echo e(roleLabel($currentUser)); ?></strong>
        <small><?php echo e(getCurrentBarangay()['name']); ?></small>
    </div>
</aside>
