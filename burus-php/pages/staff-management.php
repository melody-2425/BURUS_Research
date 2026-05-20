<?php
$currentUser = getCurrentUser();
$barangay = getCurrentBarangay();
$staffMembers = getStaffByBarangay($barangay['id']);
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');

if ($search !== '') {
    $staffMembers = array_values(array_filter($staffMembers, function ($member) use ($search) {
        $issueTypes = implode(' ', $member['issue_types'] ?? []);
        return stripos($member['name'] ?? '', $search) !== false
            || stripos($member['department'] ?? '', $search) !== false
            || stripos($member['status'] ?? '', $search) !== false
            || stripos($issueTypes, $search) !== false;
    }));
}
?>

<section class="filter-tabs">
    <span class="active">Response Team Roster</span>
    <span>Maintenance</span>
    <span>Issue Assignments</span>
</section>

<section class="dashboard-grid">
    <article class="panel">
        <div class="panel-heading compact">
            <div>
                <h2>Add Staff Member</h2>
                <p>Add new maintenance and issue response personnel for <?php echo e($barangay['name']); ?>.</p>
            </div>
        </div>

        <form class="stack-form two-cols" method="post" action="index.php?page=staff-management">
            <input type="hidden" name="add_staff_member" value="1">

            <label>
                Full Name
                <input class="form-control" type="text" name="name" placeholder="e.g., Mario Santos" required>
            </label>

            <label>
                Department
                <input class="form-control" type="text" name="department" placeholder="e.g., Public Works" value="Public Works" required>
            </label>

            <label>
                Initial Status
                <select class="form-control" name="status">
                    <option>Available</option>
                    <option>Assigned</option>
                    <option>On Leave</option>
                </select>
            </label>

            <label class="span-2">
                Issue Types This Staff Can Handle
                <select class="form-control" name="issue_types[]" multiple size="7">
                    <option value="Power Outage">Power Outage</option>
                    <option value="Water Leak">Water Leak</option>
                    <option value="Street Light">Street Light</option>
                    <option value="Road Damage">Road Damage</option>
                    <option value="Waste / Trash">Waste / Trash</option>
                    <option value="Drainage">Drainage</option>
                    <option value="Other">Other</option>
                </select>
            </label>

            <button class="btn btn-primary span-2" type="submit">Add to Staff Roster</button>
        </form>
    </article>

    <aside class="grid">
        <article class="card">
            <h2>Quick Notes</h2>
            <div class="activity-list">
                <div><strong>Tip</strong><small>Hold Command/Ctrl to select multiple issue types in the list.</small></div>
                <div><strong>Maintenance</strong><small>Add at least one Public Works responder for road and street-light concerns.</small></div>
                <div><strong>Coverage</strong><small>Balance staff between sanitation, utilities, and infrastructure issues.</small></div>
            </div>
        </article>
    </aside>
</section>

<section class="table-card">
    <div class="panel-heading">
        <div>
            <h2>Current Staff Assignments</h2>
            <p><?php echo e(count($staffMembers)); ?> staff member(s) listed for <?php echo e($barangay['name']); ?>.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Issue Coverage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffMembers as $member): ?>
                    <?php $issueCoverage = $member['issue_types'] ?? []; ?>
                    <tr>
                        <td><strong><?php echo e($member['name']); ?></strong></td>
                        <td><?php echo e($member['department'] ?? 'Public Works'); ?></td>
                        <td><?php echo e($member['status'] ?? 'Available'); ?></td>
                        <td><?php echo e($issueCoverage ? implode(', ', $issueCoverage) : 'General maintenance'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$staffMembers): ?>
                    <tr>
                        <td colspan="4" class="empty-state">No staff members found for this barangay yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
