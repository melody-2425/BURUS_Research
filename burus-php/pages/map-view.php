<?php
global $reports;
$currentUser = getCurrentUser();
$barangay = getCurrentBarangay();
$mapReports = getReportsByBarangay($reports, $currentUser['barangay_id']);
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
if ($search !== '') {
    $mapReports = array_values(array_filter($mapReports, function ($report) use ($search) {
        $resident = getUserById($report['resident_id']);
        return stripos($report['ticket_id'], $search) !== false
            || stripos($report['issue_type'], $search) !== false
            || stripos($report['location'], $search) !== false
            || stripos($resident['name'] ?? '', $search) !== false;
    }));
}
$totalReports = count($mapReports);
$pendingReports = countReportsByStatus($mapReports, 'Pending');
$progressReports = countReportsByStatus($mapReports, 'In Progress');
$resolvedReports = countReportsByStatus($mapReports, 'Resolved');
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Reports</span><strong><?php echo e($totalReports); ?></strong><small><?php echo e($barangay['name']); ?></small></article>
    <article class="stat-card warning"><span>Pending</span><strong><?php echo e($pendingReports); ?></strong><small>Waiting review</small></article>
    <article class="stat-card"><span>In Progress</span><strong><?php echo e($progressReports); ?></strong><small>Assigned work</small></article>
    <article class="stat-card success"><span>Resolved</span><strong><?php echo e($resolvedReports); ?></strong><small>Solved</small></article>
</section>

<section class="map-page">
    <aside class="map-sidebar">
        <h2>Issue Legend</h2>
        <div class="legend">
            <span><b class="water"></b>Water Leak</span>
            <span><b class="pothole"></b>Pothole</span>
            <span><b class="power"></b>Power Outage</span>
            <span><b class="resolved"></b>Resolved</span>
        </div>

        <h2>Nearby Active Issues</h2>
        <div class="activity-list">
            <?php foreach ($mapReports as $report): ?>
                <?php $resident = getUserById($report['resident_id']); ?>
                <a class="map-card" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">
                    <strong><?php echo e($report['ticket_id']); ?></strong>
                    <small><?php echo e($report['issue_type']); ?> by <?php echo e($resident['name'] ?? 'Resident'); ?></small>
                    <span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <div class="map-canvas">
        <span class="map-road road-a"></span>
        <span class="map-road road-b"></span>
        <span class="map-road road-c"></span>

        <?php foreach ($mapReports as $report): ?>
            <?php
            $resident = getUserById($report['resident_id']);
            $isSolved = $report['status'] === 'Resolved';
            $statusClass = $report['status'] === 'In Progress' ? 'status-progress' : 'status-' . strtolower($report['status']);
            $sameAreaCount = count(array_filter($mapReports, fn($item) => $item['location'] === $report['location']));
            ?>
            <a class="map-pin <?php echo e($statusClass); ?>" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>" style="left: <?php echo e($report['pin']['x']); ?>%; top: <?php echo e($report['pin']['y']); ?>%;">
                <span class="pin-dot"></span>
                <span class="map-popup">
                    <strong><?php echo e($report['ticket_id']); ?></strong>
                    <small>Resident: <?php echo e($resident['name'] ?? 'Resident'); ?></small>
                    <small>Issue: <?php echo e($report['issue_type']); ?></small>
                    <small>Location: <?php echo e($report['location']); ?></small>
                    <small>Status: <?php echo e($report['status']); ?></small>
                    <small><?php echo e($isSolved ? 'Solved' : 'Not Solved'); ?></small>
                    <small><?php echo e($sameAreaCount); ?> report<?php echo $sameAreaCount === 1 ? '' : 's'; ?> in this area</small>
                </span>
            </a>
        <?php endforeach; ?>

        <a class="btn btn-primary map-new-button" href="index.php?page=report-new">Report New Issue</a>
    </div>
</section>
