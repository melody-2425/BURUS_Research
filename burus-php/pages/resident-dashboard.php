<?php
global $reports, $feedback, $announcements;
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$myReports = getReportsByResident($reports, $user['id']);
$myFeedback = getFeedbackByResident($feedback, $user['id']);
$recentReports = array_slice($myReports, 0, 4);
$latestAnnouncements = getLatestAnnouncements(getAnnouncementsForUser($announcements, $user), 3);
$recentReply = null;
foreach ($myFeedback as $item) {
    if (!empty($item['official_reply'])) {
        $recentReply = $item;
        break;
    }
}
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Reports</span><strong><?php echo count($myReports); ?></strong><small>Submitted by you</small></article>
    <article class="stat-card warning"><span>Pending</span><strong><?php echo countReportsByStatus($myReports, 'Pending'); ?></strong><small>Waiting for review</small></article>
    <article class="stat-card"><span>In Progress</span><strong><?php echo countReportsByStatus($myReports, 'In Progress'); ?></strong><small>Currently assigned</small></article>
    <article class="stat-card success"><span>Resolved</span><strong><?php echo countReportsByStatus($myReports, 'Resolved'); ?></strong><small>Completed tickets</small></article>
</section>

<section class="dashboard-grid">
    <div class="grid">
        <article class="table-card">
            <div class="panel-heading">
                <div>
                    <h2>Recent Reports</h2>
                    <p>Your latest submitted tickets and current status.</p>
                </div>
                <a class="ghost-link" href="index.php?page=my-reports">View all</a>
            </div>
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr><th>Ticket ID</th><th>Issue Type</th><th>Date Submitted</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td><strong><?php echo e($report['ticket_id']); ?></strong><small><?php echo e($report['location']); ?></small></td>
                                <td><?php echo e($report['issue_type']); ?></td>
                                <td><?php echo e($report['date_submitted']); ?></td>
                                <td><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                                <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentReports): ?><tr><td colspan="5" class="empty-state">No reports submitted yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <section class="grid grid-2">
            <article class="card">
                <div class="panel-heading compact">
                    <div>
                        <h2>Announcements</h2>
                        <p>Latest barangay and system-wide notices.</p>
                    </div>
                    <a class="ghost-link" href="index.php?page=announcements">View all</a>
                </div>
                <div class="announcement-list compact">
                    <?php foreach ($latestAnnouncements as $announcement): ?>
                        <article class="announcement-item">
                            <div class="announcement-top">
                                <span class="badge badge-<?php echo e(strtolower(str_replace(' ', '-', $announcement['category']))); ?>"><?php echo e($announcement['category']); ?></span>
                                <small><?php echo e($announcement['date_posted']); ?></small>
                            </div>
                            <h4><?php echo e($announcement['title']); ?></h4>
                            <p><?php echo e($announcement['content']); ?></p>
                            <small>Posted by <?php echo e($announcement['posted_by']); ?></small>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$latestAnnouncements): ?>
                        <div class="empty-state">
                            <h4>No announcements yet</h4>
                            <p>Announcements posted by the barangay will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <article class="card">
                <h2>Community Activity</h2>
                <div class="activity-list">
                    <?php foreach (array_slice(getActivityLogsByBarangay($barangay['id']), 0, 2) as $log): ?>
                        <div><strong><?php echo e($log['message']); ?></strong><small><?php echo e($log['date']); ?></small></div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>
    </div>

    <aside class="grid">
        <div class="quick-card">
            <h2>Quick Actions</h2>
            <p>Common tasks for resident reporting.</p>
            <div class="quick-list">
                <a href="index.php?page=report-new">Create a new report</a>
                <a href="index.php?page=map-view">View barangay map</a>
                <a href="index.php?page=messages">Open feedback threads</a>
            </div>
        </div>
        <article class="card">
            <h2>Recent Updates</h2>
            <?php if ($recentReply): ?>
                <p class="muted"><?php echo e($recentReply['ticket_id']); ?></p>
                <strong><?php echo e($recentReply['official_reply']); ?></strong>
            <?php else: ?>
                <p class="muted">No official replies yet. Your updates will appear here after barangay staff responds.</p>
            <?php endif; ?>
        </article>
    </aside>
</section>
