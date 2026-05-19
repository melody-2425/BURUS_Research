<?php
global $reports;
$reports = getReportsByBarangay($reports, getCurrentBarangay()['id']);
$types = getCommonIssueTypes($reports);
?>
<section class="stats-row">
    <article class="stat-card"><span>Resolution Rate</span><strong><?php echo count($reports) ? round((countReportsByStatus($reports, 'Resolved') / count($reports)) * 100) : 0; ?>%</strong><small>Closed tickets</small></article>
    <article class="stat-card"><span>Average Response</span><strong>1.8d</strong><small>Prototype metric</small></article>
    <article class="stat-card"><span>High Priority</span><strong><?php echo count(array_filter($reports, fn($r) => $r['priority'] === 'High')); ?></strong><small>Needs attention</small></article>
    <article class="stat-card"><span>Staff Teams</span><strong><?php echo count(getStaffByBarangay(getCurrentBarangay()['id'])); ?></strong><small>Available locally</small></article>
</section>
<section class="two-column">
    <div class="panel">
        <h2>Common Issue Types</h2>
        <div class="bar-list tall">
            <?php foreach ($types as $type => $count): ?>
                <div class="bar-item"><span><?php echo e($type); ?></span><b style="width: <?php echo e(35 + ($count * 18)); ?>%"></b><em><?php echo e($count); ?></em></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <h2>Status Distribution</h2>
        <div class="donut-placeholder">
            <span><?php echo count($reports); ?><small>Total</small></span>
        </div>
        <div class="legend">
            <span><b class="pending"></b>Pending</span>
            <span><b class="progress"></b>In Progress</span>
            <span><b class="resolved"></b>Resolved</span>
        </div>
    </div>
</section>
