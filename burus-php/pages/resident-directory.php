<?php
$barangay = getCurrentBarangay();
global $users;
$residents = isAdmin(getCurrentUser())
    ? array_values(array_filter($users, fn($user) => $user['role'] === 'resident'))
    : getResidentsByBarangay($barangay['id']);
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
if ($search !== '') {
    $residents = array_values(array_filter($residents, function ($resident) use ($search) {
        return stripos($resident['name'], $search) !== false
            || stripos($resident['email'], $search) !== false
            || stripos($resident['address'], $search) !== false
            || stripos($resident['contact'], $search) !== false;
    }));
}
?>
<section class="stats-row">
    <article class="stat-card"><span>Total Residents</span><strong><?php echo count($residents); ?></strong><small>Registered profiles</small></article>
    <article class="stat-card success"><span>Verified</span><strong><?php echo count($residents); ?></strong><small>Prototype accounts</small></article>
    <article class="stat-card warning"><span>Pending</span><strong>0</strong><small>For review</small></article>
    <article class="stat-card"><span>New This Month</span><strong><?php echo count($residents); ?></strong><small>Sample data</small></article>
</section>

<section class="dashboard-grid">
    <article class="table-card">
        <div class="table-wrap">
            <table class="report-table">
                <thead><tr><th>Resident Name</th><th>Household Address</th><th>Contact Number</th><th>Status</th><th>Date Joined</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($residents as $resident): ?>
                        <tr>
                            <td><strong><?php echo e($resident['name']); ?></strong><small><?php echo e($resident['email']); ?></small></td>
                            <td><?php echo e($resident['address']); ?></td>
                            <td><?php echo e($resident['contact']); ?></td>
                            <td><span class="status-badge status-resolved">Verified</span></td>
                            <td>May 2026</td>
                            <td><a class="table-link" href="index.php?page=resident-directory">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <aside class="card">
        <h2>Verification Guidelines</h2>
        <div class="soft-list">
            <span>Confirm barangay residency details.</span>
            <span>Validate contact number and email.</span>
            <span>Review duplicate household records.</span>
            <span>Restrict sensitive actions to officials/admins.</span>
        </div>
    </aside>
</section>
