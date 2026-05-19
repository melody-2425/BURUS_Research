<?php
global $reports, $feedback;
$barangay = getCurrentBarangay();
$barangayReports = getReportsByBarangay($reports, $barangay['id']);
$barangayFeedback = getFeedbackByBarangay($feedback, $barangay['id']);
?>
<section class="content-grid">
    <div class="welcome-card">
        <span class="section-kicker"><?php echo e($barangay['name']); ?></span>
        <h2>Operations Overview</h2>
        <p>Monitor barangay-wide reports, assignments, response progress, and recent activity logs.</p>
        <a class="btn btn-primary" href="index.php?page=issue-reports">Manage Reports</a>
    </div>
    <div class="stats-row">
        <article class="stat-card"><span>Total</span><strong><?php echo count($barangayReports); ?></strong><small>Complaints</small></article>
        <article class="stat-card"><span>Pending</span><strong><?php echo countReportsByStatus($barangayReports, 'Pending'); ?></strong><small>Need review</small></article>
        <article class="stat-card"><span>In Progress</span><strong><?php echo countReportsByStatus($barangayReports, 'In Progress'); ?></strong><small>Assigned</small></article>
        <article class="stat-card"><span>Resolved</span><strong><?php echo countReportsByStatus($barangayReports, 'Resolved'); ?></strong><small>Closed</small></article>
    </div>
</section>
<section class="two-column">
    <div class="panel">
        <h2>Complaint Trend</h2>
        <div class="chart-placeholder">
            <i style="height:45%"></i><i style="height:64%"></i><i style="height:38%"></i><i style="height:80%"></i><i style="height:52%"></i><i style="height:70%"></i>
        </div>
    </div>
    <div class="panel">
        <h2>Activity Logs</h2>
        <div class="activity-list">
            <?php foreach (getActivityLogsByBarangay($barangay['id']) as $log): ?>
                <div><span></span><p><?php echo e($log['message']); ?><small><?php echo e($log['date']); ?></small></p></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Feedback & Response</h2>
            <p>Resident comments, official replies, and ratings for barangay reports.</p>
        </div>
        <a class="ghost-link" href="index.php?page=messages">Manage Feedback</a>
    </div>
    <div class="feedback-mini-grid">
        <article><span>New Resident Comments</span><strong><?php echo e(count($barangayFeedback)); ?></strong></article>
        <article><span>Awaiting Official Reply</span><strong><?php echo e(countFeedbackByStatus($barangayFeedback, 'Awaiting Reply')); ?></strong></article>
        <article><span>Low Service Ratings</span><strong><?php echo e(countLowRatings($barangayFeedback)); ?></strong></article>
        <article>
            <span>Latest Feedback Activity</span>
            <strong><?php echo e($barangayFeedback[0]['ticket_id'] ?? 'None'); ?></strong>
            <small><?php echo e($barangayFeedback[0]['last_message'] ?? 'No feedback yet.'); ?></small>
        </article>
    </div>
</section>
<section class="panel">
    <div class="panel-heading"><div><h2>Latest Barangay Reports</h2><p>Scoped only to <?php echo e($barangay['name']); ?>.</p></div></div>
    <?php $reports = $barangayReports; include __DIR__ . '/partials/report-table.php'; ?>
</section>
