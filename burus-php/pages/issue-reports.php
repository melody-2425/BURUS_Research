<?php
global $reports;
$currentUser = getCurrentUser();
$barangay = getCurrentBarangay();
$allReports = $reports;
$reports = getReportsVisibleToUser($allReports ?? [], $currentUser);
$statusFilter = $_GET['status'] ?? 'All';
$typeFilter = $_GET['type'] ?? 'All';
$priorityFilter = $_GET['priority'] ?? 'All';
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');

if ($statusFilter !== 'All') {
    $reports = array_values(array_filter($reports, fn($report) => $report['status'] === $statusFilter));
}
if ($typeFilter !== 'All') {
    $reports = array_values(array_filter($reports, fn($report) => $report['issue_type'] === $typeFilter));
}
if ($priorityFilter !== 'All') {
    $reports = array_values(array_filter($reports, fn($report) => $report['priority'] === $priorityFilter));
}
if ($search !== '') {
    $reports = array_values(array_filter($reports, function ($report) use ($search) {
        $resident = getUserById($report['resident_id']);
        return stripos($report['ticket_id'], $search) !== false
            || stripos($report['issue_type'], $search) !== false
            || stripos($report['title'], $search) !== false
            || stripos($report['location'], $search) !== false
            || stripos($resident['name'] ?? '', $search) !== false;
    }));
}
$allBarangayReports = getReportsVisibleToUser($allReports ?? [], $currentUser);
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Tickets</span><strong><?php echo count($allBarangayReports); ?></strong><small>Barangay reports</small></article>
    <article class="stat-card warning"><span>Pending</span><strong><?php echo countReportsByStatus($allBarangayReports, 'Pending'); ?></strong><small>Needs review</small></article>
    <article class="stat-card"><span>In Progress</span><strong><?php echo countReportsByStatus($allBarangayReports, 'In Progress'); ?></strong><small>Assigned</small></article>
    <article class="stat-card success"><span>Resolved</span><strong><?php echo countReportsByStatus($allBarangayReports, 'Resolved'); ?></strong><small>Closed</small></article>
</section>

<section class="dashboard-grid">
    <article class="table-card">
        <form class="filter-bar" method="get">
            <input type="hidden" name="page" value="issue-reports">
            <select class="form-control" name="status">
                <?php foreach (['All', 'Pending', 'In Progress', 'Resolved'] as $status): ?>
                    <option <?php echo $statusFilter === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="form-control" name="type">
                <?php foreach (['All', 'Water Leak', 'Street Light', 'Road Damage', 'Waste / Trash'] as $type): ?>
                    <option <?php echo $typeFilter === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="form-control" name="priority">
                <?php foreach (['All', 'High', 'Medium', 'Low'] as $priority): ?>
                    <option <?php echo $priorityFilter === $priority ? 'selected' : ''; ?>><?php echo e($priority); ?></option>
                <?php endforeach; ?>
            </select>
            <input class="form-control" type="text" name="date" placeholder="Date range">
            <button class="btn btn-primary" type="submit">Apply</button>
        </form>
        <div class="table-wrap">
            <table class="report-table">
                <thead><tr><th>Ticket ID</th><th>Issue Type</th><th>Resident</th><th>Date</th><th>Evidence</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <?php $resident = getUserById($report['resident_id']); ?>
                        <tr>
                            <td><strong><?php echo e($report['ticket_id']); ?></strong></td>
                            <td><?php echo e($report['issue_type']); ?><small><?php echo e($report['location']); ?></small></td>
                            <td><?php echo e($resident['name'] ?? 'Resident'); ?></td>
                            <td><?php echo e($report['date_submitted']); ?></td>
                            <td>
                                <span class="status-badge status-neutral"><?php echo count($report['evidence'] ?? []); ?> before</span>
                                <small><?php echo count($report['resolution_evidence'] ?? []); ?> resolution proof</small>
                            </td>
                            <td><span class="<?php echo e(getPriorityClass($report['priority'])); ?>"><?php echo e($report['priority']); ?></span></td>
                            <td><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                            <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">Manage</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$reports): ?><tr><td colspan="8" class="empty-state">No reports found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="grid">
        <article class="card">
            <h2>Recent Activity Timeline</h2>
            <div class="activity-list">
                <?php foreach (getActivityLogsByBarangay($barangay['id']) as $log): ?>
                    <div><strong><?php echo e($log['message']); ?></strong><small><?php echo e($log['date']); ?></small></div>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="card">
            <h2>Issue Hotspots</h2>
            <div class="bar-list">
                <?php foreach (getCommonIssueTypes($allBarangayReports) as $type => $count): ?>
                    <div class="bar-item"><span><?php echo e($type); ?></span><b style="width: <?php echo e(35 + ($count * 18)); ?>%"></b><em><?php echo e($count); ?></em></div>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="card">
            <h2>Staff Availability</h2>
            <div class="activity-list">
                <?php foreach (getStaffByBarangay($barangay['id']) as $member): ?>
                    <div><strong><?php echo e($member['name']); ?></strong><small><?php echo e($member['department']); ?> - <?php echo e($member['status']); ?></small></div>
                <?php endforeach; ?>
            </div>
        </article>
    </aside>
</section>
