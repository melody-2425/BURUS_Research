<?php
global $reports;
$barangay = getCurrentBarangay();
$reports = isAdmin(getCurrentUser()) ? array_map('normalizeReportRecord', $reports) : getReportsByBarangay($reports, $barangay['id']);
$types = getCommonIssueTypes($reports);
$resolutionRate = count($reports) ? round((countReportsByStatus($reports, 'Resolved') / count($reports)) * 100) : 0;
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Complaints</span><strong><?php echo count($reports); ?></strong><small>Tracked tickets</small></article>
    <article class="stat-card success"><span>Resolution Rate</span><strong><?php echo e($resolutionRate); ?>%</strong><small>Resolved reports</small></article>
    <article class="stat-card"><span>Avg. Response Time</span><strong>1.8d</strong><small>Prototype metric</small></article>
    <article class="stat-card warning"><span>High Priority</span><strong><?php echo count(array_filter($reports, fn($r) => $r['priority'] === 'High')); ?></strong><small>Needs attention</small></article>
</section>

<section class="grid grid-2">
    <article class="panel">
        <h2>Complaint Volume Trend</h2>
        <div class="chart-placeholder">
            <i style="height:36%"></i><i style="height:54%"></i><i style="height:44%"></i><i style="height:82%"></i><i style="height:68%"></i><i style="height:76%"></i>
        </div>
    </article>
    <article class="panel">
        <h2>Issue Distribution</h2>
        <div class="donut-placeholder">
            <span><?php echo count($reports); ?><small>Total</small></span>
        </div>
    </article>
    <article class="panel">
        <h2>Avg. Resolution Time per Category</h2>
        <div class="bar-list">
            <?php foreach ($types as $type => $count): ?>
                <div class="bar-item"><span><?php echo e($type); ?></span><b style="width: <?php echo e(35 + ($count * 18)); ?>%"></b><em><?php echo e($count + 1); ?>d</em></div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="panel">
        <h2>Critical Performance Milestones</h2>
        <div class="activity-list">
            <div><strong>First response target</strong><small>Respond to pending complaints within 24 hours.</small></div>
            <div><strong>Assignment target</strong><small>Assign high-priority tickets before end of day.</small></div>
            <div><strong>Resolution quality</strong><small>Monitor low ratings and unresolved follow-up comments.</small></div>
        </div>
    </article>
</section>
