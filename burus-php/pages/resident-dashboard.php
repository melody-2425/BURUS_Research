<?php
global $feedback;
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$myReports = getReportsByResident($user['id']);
$myFeedback = getFeedbackByResident($feedback, $user['id']);
$recentReply = null;
foreach ($myFeedback as $item) {
    if (!empty($item['official_reply'])) {
        $recentReply = $item;
        break;
    }
}
$readyToRate = array_values(array_filter($myFeedback, fn($item) => canResidentRate($item)));
?>
<section class="content-grid">
    <div class="welcome-card">
        <span class="section-kicker"><?php echo e($barangay['name']); ?></span>
        <h2>Hello, <?php echo e($user['name']); ?></h2>
        <p>Track your submitted infrastructure concerns and receive updates from barangay staff.</p>
        <a class="btn btn-primary" href="index.php?page=report-new">Report New Issue</a>
    </div>
    <div class="stats-row">
        <article class="stat-card"><span>Total</span><strong><?php echo count($myReports); ?></strong><small>My complaints</small></article>
        <article class="stat-card"><span>Pending</span><strong><?php echo countReportsByStatus($myReports, 'Pending'); ?></strong><small>Waiting review</small></article>
        <article class="stat-card"><span>In Progress</span><strong><?php echo countReportsByStatus($myReports, 'In Progress'); ?></strong><small>Being handled</small></article>
        <article class="stat-card"><span>Resolved</span><strong><?php echo countReportsByStatus($myReports, 'Resolved'); ?></strong><small>Completed</small></article>
    </div>
</section>
<section class="panel">
    <div class="panel-heading">
        <div><h2>Recent Reports</h2><p>Your latest submitted tickets.</p></div>
        <a class="ghost-link" href="index.php?page=my-reports">View all</a>
    </div>
    <?php $reports = $myReports; include __DIR__ . '/partials/report-table.php'; ?>
</section>
<section class="two-column">
    <div class="panel">
        <h2>Issue Types</h2>
        <div class="bar-list">
            <?php foreach (getCommonIssueTypes($myReports) as $type => $count): ?>
                <div class="bar-item"><span><?php echo e($type); ?></span><b style="width: <?php echo e(30 + ($count * 20)); ?>%"></b><em><?php echo e($count); ?></em></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading compact-heading">
            <div>
                <h2>Feedback & Response</h2>
                <p>Official replies and service ratings for your reports.</p>
            </div>
            <a class="ghost-link" href="index.php?page=messages">Open</a>
        </div>
        <div class="feedback-mini-list">
            <div>
                <span>Recent Official Replies</span>
                <strong><?php echo e(count(array_filter($myFeedback, fn($item) => !empty($item['official_reply'])))); ?></strong>
            </div>
            <div>
                <span>Reports Ready for Rating</span>
                <strong><?php echo e(count($readyToRate)); ?></strong>
            </div>
            <?php if ($recentReply): ?>
                <a href="index.php?page=messages&feedback_id=<?php echo e($recentReply['id']); ?>">
                    <span>Latest Feedback Thread</span>
                    <strong><?php echo e($recentReply['ticket_id']); ?></strong>
                    <small><?php echo e($recentReply['official_reply']); ?></small>
                </a>
            <?php else: ?>
                <a href="index.php?page=messages">
                    <span>Latest Feedback Thread</span>
                    <strong>No official replies yet</strong>
                    <small>Open the feedback page to add a report comment.</small>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
