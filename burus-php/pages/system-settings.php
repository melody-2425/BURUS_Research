<?php $barangay = getCurrentBarangay(); ?>
<section class="filter-tabs">
    <span class="active">General Settings</span>
    <span>Roles & Permissions</span>
    <span>Notifications</span>
    <span>System Logs</span>
    <span>Security</span>
</section>

<section class="settings-grid">
    <div class="grid">
        <article class="panel">
            <h2>General Configuration</h2>
            <form class="stack-form two-cols">
                <label>Barangay Name<input class="form-control" type="text" value="<?php echo e($barangay['name']); ?>"></label>
                <label>City<input class="form-control" type="text" value="<?php echo e($barangay['city']); ?>"></label>
                <label>Contact<input class="form-control" type="text" value="<?php echo e($barangay['contact']); ?>"></label>
                <label>Barangay Captain<input class="form-control" type="text" value="<?php echo e($barangay['captain']); ?>"></label>
                <label class="span-2">Office Hours<input class="form-control" type="text" value="<?php echo e($barangay['office_hours']); ?>"></label>
                <button class="btn btn-primary span-2" type="button">Save Settings</button>
            </form>
        </article>

        <article class="panel">
            <h2>Roles & Permissions</h2>
            <div class="table-wrap">
                <table class="report-table">
                    <thead><tr><th>Role</th><th>Reports</th><th>Feedback</th><th>Settings</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Resident</strong></td><td>Read own reports</td><td>Comment and rate</td><td>No access</td></tr>
                        <tr><td><strong>Official</strong></td><td>Update assigned tickets</td><td>Reply to residents</td><td>No access</td></tr>
                        <tr><td><strong>Admin</strong></td><td>Full barangay access</td><td>Full barangay access</td><td>Allowed</td></tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel">
            <h2>Recent Audit Trail</h2>
            <div class="activity-list">
                <div><strong>System settings opened</strong><small>Administrator reviewed configuration.</small></div>
                <div><strong>Notification rule checked</strong><small>Status changed alerts remain active.</small></div>
                <div><strong>Role matrix reviewed</strong><small>Resident controls remain read-only.</small></div>
            </div>
        </article>
    </div>

    <aside class="grid">
        <article class="card">
            <h2>Notification Channels</h2>
            <div class="toggle-list">
                <label><input type="checkbox" checked> Report accepted notification</label>
                <label><input type="checkbox" checked> Status changed notification</label>
                <label><input type="checkbox" checked> Report resolved notification</label>
                <label><input type="checkbox"> Weekly analytics digest</label>
            </div>
        </article>
        <article class="card">
            <h2>Security Controls</h2>
            <div class="toggle-list">
                <label><input type="checkbox" checked> Require staff session login</label>
                <label><input type="checkbox" checked> Restrict residents to read-only ticket details</label>
                <label><input type="checkbox"> Enable two-step verification</label>
            </div>
        </article>
        <article class="card alert-card">
            <h2>Critical Actions</h2>
            <p class="muted">Export backups, rotate credentials, or review suspicious login events before making major changes.</p>
            <button class="btn btn-outline" type="button">Review Controls</button>
        </article>
    </aside>
</section>
