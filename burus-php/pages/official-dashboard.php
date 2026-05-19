<?php
global $reports, $feedback;
$user = getCurrentUser();
$barangay = getCurrentBarangay();
$barangayReports = getReportsByBarangay($reports, $barangay['id']);
$barangayFeedback = getFeedbackByBarangay($feedback, $barangay['id']);
$pendingReports = countReportsByStatus($barangayReports, 'Pending');
$progressReports = countReportsByStatus($barangayReports, 'In Progress');
$reportsNeedingUpdate = array_values(array_filter($barangayReports, fn($report) => $report['status'] !== 'Resolved'));
$recentFeedback = array_slice($barangayFeedback, 0, 3);
?>
<section class="stats-row">
    <article class="stat-card warning"><span>Pending Complaints</span><strong><?php echo e($pendingReports); ?></strong><small>Need first review</small></article>
    <article class="stat-card"><span>In Progress</span><strong><?php echo e($progressReports); ?></strong><small>Currently assigned</small></article>
    <article class="stat-card"><span>Assigned Staff</span><strong><?php echo count(getStaffByBarangay($barangay['id'])); ?></strong><small>Available teams</small></article>
    <article class="stat-card success"><span>Resident Feedback</span><strong><?php echo count($barangayFeedback); ?></strong><small>Barangay threads</small></article>
</section>

<section class="dashboard-grid">
    <div class="grid">
        <article class="table-card">
            <div class="panel-heading">
                <div>
                    <h2>Reports Needing Update</h2>
                    <p>Unresolved barangay reports that may need assignment, status changes, or replies.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="report-table">
                    <thead><tr><th>Ticket ID</th><th>Issue Type</th><th>Resident</th><th>Assigned</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($reportsNeedingUpdate, 0, 6) as $report): ?>
                            <?php $resident = getUserById($report['resident_id']); ?>
                            <tr>
                                <td><strong><?php echo e($report['ticket_id']); ?></strong></td>
                                <td><?php echo e($report['issue_type']); ?><small><?php echo e($report['location']); ?></small></td>
                                <td><?php echo e($resident['name'] ?? 'Resident'); ?></td>
                                <td><?php echo e($report['assigned_to']); ?></td>
                                <td><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                                <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">Update</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$reportsNeedingUpdate): ?><tr><td colspan="6" class="empty-state">No active reports need updates.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <section class="grid grid-2">
            <article class="card">
                <h2>Issue Map Summary</h2>
                <p class="muted"><?php echo count($barangayReports); ?> mapped reports are visible for <?php echo e($barangay['name']); ?>.</p>
                <a class="ghost-link" href="index.php?page=map-view">Open Map View</a>
            </article>
            <article class="card">
                <h2>Staff Availability</h2>
                <div class="activity-list">
                    <?php foreach (getStaffByBarangay($barangay['id']) as $member): ?>
                        <div><strong><?php echo e($member['name']); ?></strong><small><?php echo e($member['department']); ?> - <?php echo e($member['status']); ?></small></div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>
    </div>

    <aside class="grid">
        <article class="quick-card">
            <h2>Official Work Queue</h2>
            <p>Complaint management tools available to barangay officials.</p>
            <div class="quick-list">
                <a href="index.php?page=issue-reports">Filter and update reports</a>
                <a href="index.php?page=messages">Reply to residents</a>
                <a href="index.php?page=map-view">Review mapped issues</a>
            </div>
        </article>
        <article class="card">
            <h2>Recent Resident Feedback</h2>
            <div class="activity-list">
                <?php foreach ($recentFeedback as $item): ?>
                    <div><strong><?php echo e($item['ticket_id']); ?></strong><small><?php echo e($item['last_message']); ?></small></div>
                <?php endforeach; ?>
                <?php if (!$recentFeedback): ?><p class="muted">No recent resident feedback.</p><?php endif; ?>
            </div>
        </article>
    </aside>
</section>
