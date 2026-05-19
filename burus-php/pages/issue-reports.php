<?php
global $reports;
$barangay = getCurrentBarangay();
$reports = getReportsByBarangay($reports, $barangay['id']);
$statusFilter = $_GET['status'] ?? 'All';
$typeFilter = $_GET['type'] ?? 'All';
if ($statusFilter !== 'All') {
    $reports = array_values(array_filter($reports, fn($report) => $report['status'] === $statusFilter));
}
if ($typeFilter !== 'All') {
    $reports = array_values(array_filter($reports, fn($report) => $report['issue_type'] === $typeFilter));
}
?>
<section class="panel">
    <div class="panel-heading">
        <div><h2>Issue Reports Management</h2><p>Review, filter, assign, and update reports for <?php echo e($barangay['name']); ?>.</p></div>
    </div>
    <form class="filter-bar" method="get">
        <input type="hidden" name="page" value="issue-reports">
        <select name="status"><option>All</option><option>Pending</option><option>In Progress</option><option>Resolved</option></select>
        <select name="type"><option>All</option><option>Water Leak</option><option>Street Light</option><option>Road Damage</option><option>Waste / Trash</option></select>
        <input type="text" name="location" placeholder="Filter by location">
        <button class="btn btn-primary" type="submit">Apply Filters</button>
    </form>
    <?php include __DIR__ . '/partials/report-table.php'; ?>
</section>
<section class="panel">
    <h2>Quick Assignment</h2>
    <div class="assignment-grid">
        <?php foreach (getStaffByBarangay($barangay['id']) as $member): ?>
            <article class="staff-card">
                <span class="avatar"><?php echo e(substr($member['name'], 0, 1)); ?></span>
                <div><strong><?php echo e($member['name']); ?></strong><small><?php echo e($member['department']); ?></small></div>
                <span class="badge badge-neutral"><?php echo e($member['status']); ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>
