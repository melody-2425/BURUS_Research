<?php
$currentUser = getCurrentUser();
$currentPage = $page ?? '';
$residentLinks = [
    'resident-dashboard' => 'Dashboard',
    'my-reports' => 'My Reports',
    'report-new' => 'Report New Issue',
    'notifications' => 'Notifications',
    'messages' => 'Feedback',
    'map-view' => 'Map View',
    'profile' => 'Profile',
];
$adminLinks = [
    'admin-dashboard' => 'Dashboard',
    'issue-reports' => 'Issue Reports',
    'resident-directory' => 'Residents',
    'analytics' => 'Analytics',
    'map-view' => 'Map View',
    'messages' => 'Feedback',
    'system-settings' => 'Settings',
    'profile' => 'Profile',
];
$links = isAdminLike($currentUser) ? $adminLinks : $residentLinks;
?>
<aside class="sidebar">
    <a class="brand" href="index.php">
        <span class="brand-mark">B</span>
        <span>
            <strong>BURUS</strong>
            <small>Barangay Utility Reporting</small>
        </span>
    </a>
    <nav class="side-nav">
        <?php foreach ($links as $linkPage => $label): ?>
            <a class="<?php echo $currentPage === $linkPage ? 'active' : ''; ?>" href="index.php?page=<?php echo e($linkPage); ?>">
                <span class="nav-dot"></span>
                <?php echo e($label); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="side-card">
        <span class="mini-label">Current Role</span>
        <strong><?php echo e(ucfirst($currentUser['role'] ?? 'guest')); ?></strong>
        <small><?php echo e(getCurrentBarangay()['name']); ?></small>
    </div>
</aside>
