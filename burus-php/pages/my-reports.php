<?php
$user = getCurrentUser();
$myReports = getReportsByResident($reports, $user['id']);
$statusFilter = $_GET['status'] ?? 'All';
$search = trim($_GET['q'] ?? '');

if ($statusFilter !== 'All') {
    $myReports = array_values(array_filter($myReports, fn($report) => $report['status'] === $statusFilter));
}
if ($search !== '') {
    $myReports = array_values(array_filter($myReports, function ($report) use ($search) {
        return stripos($report['ticket_id'], $search) !== false
            || stripos($report['issue_type'], $search) !== false
            || stripos($report['title'], $search) !== false
            || stripos($report['location'], $search) !== false;
    }));
}
$allReports = getReportsByResident($reports, $user['id']);
?>
<section class="page-header">
    <div>
        <h2>My Reports</h2>
        <p>All utility and infrastructure reports submitted by your account.</p>
    </div>
    <div class="button-row">
        <button class="btn btn-outline" type="button">Export PDF</button>
        <a class="btn btn-primary" href="index.php?page=report-new">New Issue</a>
    </div>
</section>

<section class="stats-row">
    <article class="stat-card"><span>Total Reports</span><strong><?php echo count($allReports); ?></strong><small>All submitted</small></article>
    <article class="stat-card warning"><span>Pending</span><strong><?php echo countReportsByStatus($allReports, 'Pending'); ?></strong><small>Waiting review</small></article>
    <article class="stat-card"><span>In Progress</span><strong><?php echo countReportsByStatus($allReports, 'In Progress'); ?></strong><small>Being handled</small></article>
    <article class="stat-card success"><span>Resolved</span><strong><?php echo countReportsByStatus($allReports, 'Resolved'); ?></strong><small>Completed</small></article>
</section>

<section class="table-card">
    <div class="filter-tabs">
        <?php foreach (['All', 'Pending', 'In Progress', 'Resolved'] as $status): ?>
            <a class="<?php echo $statusFilter === $status ? 'active' : ''; ?>" href="index.php?page=my-reports&status=<?php echo e(urlencode($status)); ?>"><?php echo e($status); ?></a>
        <?php endforeach; ?>
    </div>
    <form method="get" class="filter-bar" style="grid-template-columns: 1fr auto;">
        <input type="hidden" name="page" value="my-reports">
        <input type="hidden" name="status" value="<?php echo e($statusFilter); ?>">
        <input class="form-control" type="search" name="q" value="<?php echo e($search); ?>" placeholder="Search by Ticket ID or Issue Type...">
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
    <div class="table-wrap">
        <table class="report-table">
            <thead>
                <tr><th>Ticket ID</th><th>Issue Type</th><th>Date Submitted</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($myReports as $report): ?>
                    <tr>
                        <td><strong><?php echo e($report['ticket_id']); ?></strong></td>
                        <td><?php echo e($report['issue_type']); ?><small><?php echo e($report['title']); ?></small></td>
                        <td><?php echo e($report['date_submitted']); ?></td>
                        <td><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                        <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$myReports): ?><tr><td colspan="5" class="empty-state">No reports found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><span>Prev</span><span class="active">1</span><span>Next</span></div>
</section>
