<?php
global $reports, $feedback, $announcements;
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$barangayReports = isAdmin($user) ? array_map('normalizeReportRecord', $reports) : getReportsByBarangay($reports, $barangay['id']);
$barangayFeedback = isAdmin($user) ? $feedback : getFeedbackByBarangay($feedback, $barangay['id']);
$pinnedAnnouncement = getDashboardPinnedAnnouncement($announcements ?? [], $user);
$resolvedCount = countReportsByStatus($barangayReports, 'Resolved');
$resolutionRate = count($barangayReports) ? round(($resolvedCount / count($barangayReports)) * 100) : 0;
$criticalReports = array_values(array_filter($barangayReports, fn($report) => $report['priority'] === 'High' || $report['status'] !== 'Resolved'));
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Complaints</span><strong><?php echo count($barangayReports); ?></strong><small>Barangay tickets</small></article>
    <article class="stat-card success"><span>Resolution Rate</span><strong><?php echo e($resolutionRate); ?>%</strong><small>Closed reports</small></article>
    <article class="stat-card warning"><span>Pending Actions</span><strong><?php echo countReportsByStatus($barangayReports, 'Pending'); ?></strong><small>Need review</small></article>
    <article class="stat-card"><span>Feedback Threads</span><strong><?php echo count($barangayFeedback); ?></strong><small>Resident responses</small></article>
</section>

<section class="two-column">
    <article class="panel">
        <h2>Resolved vs Pending Trends</h2>
        <div class="chart-placeholder">
            <i style="height:45%"></i><i style="height:64%"></i><i style="height:38%"></i><i style="height:80%"></i><i style="height:52%"></i><i style="height:70%"></i>
        </div>
    </article>
    <article class="panel">
        <h2>Common Issue Types</h2>
        <div class="bar-list">
            <?php foreach (getCommonIssueTypes($barangayReports) as $type => $count): ?>
                <div class="bar-item"><span><?php echo e($type); ?></span><b style="width: <?php echo e(35 + ($count * 18)); ?>%"></b><em><?php echo e($count); ?></em></div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="dashboard-grid">
    <article class="table-card">
        <div class="panel-heading">
            <div>
                <h2>Critical Complaints</h2>
                <p><?php echo e(isAdmin($user) ? 'High priority and unresolved tickets across all barangays.' : 'High priority and unresolved tickets scoped to ' . $barangay['name'] . '.'); ?></p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="report-table">
                <thead><tr><th>Ticket ID</th><th>Issue Type</th><th>Resident</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($criticalReports, 0, 6) as $report): ?>
                        <?php $resident = getUserById($report['resident_id']); ?>
                        <tr>
                            <td><strong><?php echo e($report['ticket_id']); ?></strong></td>
                            <td><?php echo e($report['issue_type']); ?><small><?php echo e($report['location']); ?></small></td>
                            <td><?php echo e($resident['name'] ?? 'Resident'); ?></td>
                            <td><span class="<?php echo e(getPriorityClass($report['priority'])); ?>"><?php echo e($report['priority']); ?></span></td>
                            <td><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                            <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">Review</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="grid">
        <article class="panel announcements-card">
            <div class="panel-heading compact">
                <div>
                    <h2>Announcements</h2>
                    <p>Pinned barangay or system-wide update for today.</p>
                </div>
                <a class="btn btn-outline btn-sm" href="index.php?page=announcements">View all</a>
            </div>
            <?php if ($pinnedAnnouncement): ?>
                <article class="announcement-card pinned-announcement">
                    <div class="announcement-card-top">
                        <span class="badge badge-<?php echo e(strtolower(str_replace(' ', '-', $pinnedAnnouncement['category']))); ?>"><?php echo e($pinnedAnnouncement['category']); ?></span>
                        <small><?php echo e($pinnedAnnouncement['date_posted']); ?></small>
                    </div>
                    <h4><?php echo e($pinnedAnnouncement['title']); ?></h4>
                    <p><?php echo e($pinnedAnnouncement['content']); ?></p>
                    <small class="announcement-meta">Posted by <?php echo e($pinnedAnnouncement['posted_by']); ?></small>
                </article>
            <?php else: ?>
                <div class="empty-state compact-empty">
                    <h4>No pinned announcement today</h4>
                    <p>View all announcements for previous updates.</p>
                </div>
            <?php endif; ?>
        </article>
        <article class="panel">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php foreach (getActivityLogsByBarangay($barangay['id']) as $log): ?>
                    <div><strong><?php echo e($log['message']); ?></strong><small><?php echo e($log['date']); ?></small></div>
                <?php endforeach; ?>
            </div>
        </article>
    </aside>
</section>
