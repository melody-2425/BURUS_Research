<?php
global $reports;
$currentUser = getCurrentUser();
$barangay = getCurrentBarangay();
$mapReports = getReportsByBarangay($reports, $currentUser['barangay_id']);
$totalReports = count($mapReports);
$pendingReports = countReportsByStatus($mapReports, 'Pending');
$progressReports = countReportsByStatus($mapReports, 'In Progress');
$resolvedReports = countReportsByStatus($mapReports, 'Resolved');
?>
<section class="map-counts">
    <article class="stat-card"><span>Total Reports</span><strong><?php echo e($totalReports); ?></strong><small><?php echo e($barangay['name']); ?></small></article>
    <article class="stat-card danger"><span>Pending Reports</span><strong><?php echo e($pendingReports); ?></strong><small>Not solved</small></article>
    <article class="stat-card warning"><span>In Progress Reports</span><strong><?php echo e($progressReports); ?></strong><small>Not solved</small></article>
    <article class="stat-card success"><span>Resolved Reports</span><strong><?php echo e($resolvedReports); ?></strong><small>Solved</small></article>
</section>
<section class="map-layout">
    <div class="panel map-panel">
        <div class="panel-heading"><div><h2>Barangay Map View</h2><p>Transparent overlay map showing reported issues for <?php echo e($barangay['name']); ?>.</p></div></div>
        <div class="static-map">
            <div class="map-glass"></div>
            <span class="road road-a"></span>
            <span class="road road-b"></span>
            <span class="road road-c"></span>
            <span class="zone zone-a"></span>
            <span class="zone zone-b"></span>
            <?php foreach ($mapReports as $report): ?>
                <?php
                $resident = getUserById($report['resident_id']);
                $isSolved = $report['status'] === 'Resolved';
                $mapStatusClass = strtolower(str_replace(' ', '-', $report['status']));
                ?>
                <a class="map-overlay-pin map-<?php echo e($mapStatusClass); ?>" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>" style="left: <?php echo e($report['pin']['x']); ?>%; top: <?php echo e($report['pin']['y']); ?>%;" title="<?php echo e($report['ticket_id'] . ' - ' . $report['issue_type']); ?>">
                    <span class="pin-dot"></span>
                    <span class="pin-card">
                        <strong><?php echo e($report['ticket_id']); ?></strong>
                        <small><?php echo e($report['issue_type']); ?></small>
                        <small><?php echo e($resident['name'] ?? 'Resident'); ?></small>
                        <small><?php echo e($report['location']); ?></small>
                        <small><?php echo e($report['date_submitted']); ?></small>
                        <small><?php echo e($report['status']); ?></small>
                        <em><?php echo e($isSolved ? 'Solved' : 'Not Solved'); ?></em>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <aside class="panel map-side-panel">
        <div class="panel-heading compact-heading">
            <div>
                <h2>Pinned Issues</h2>
                <p><?php echo e($totalReports); ?> report<?php echo $totalReports === 1 ? '' : 's'; ?> shown on map</p>
            </div>
        </div>
        <div class="map-card-list">
            <?php foreach ($mapReports as $report): ?>
                <?php
                $resident = getUserById($report['resident_id']);
                $isSolved = $report['status'] === 'Resolved';
                $mapStatusClass = strtolower(str_replace(' ', '-', $report['status']));
                ?>
                <a href="index.php?page=report-details&id=<?php echo e($report['id']); ?>" class="map-card map-<?php echo e($mapStatusClass); ?> <?php echo e($isSolved ? 'solved' : 'not-solved'); ?>">
                    <span class="map-card-head">
                        <strong><?php echo e($report['ticket_id']); ?></strong>
                        <em class="badge <?php echo e(getStatusBadgeClass($report['status'])); ?>"><?php echo e($report['status']); ?></em>
                    </span>
                    <span><b>Issue:</b> <?php echo e($report['issue_type']); ?></span>
                    <span><b>Resident:</b> <?php echo e($resident['name'] ?? 'Resident'); ?></span>
                    <span><b>Location:</b> <?php echo e($report['location']); ?></span>
                    <span><b>Date:</b> <?php echo e($report['date_submitted']); ?></span>
                    <span class="solved-label"><?php echo e($isSolved ? 'Solved' : 'Not Solved'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>
</section>
