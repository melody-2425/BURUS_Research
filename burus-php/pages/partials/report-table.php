<?php
// This partial expects a $reports array and renders a reusable ticket table.
$reports = $reports ?? [];
?>
<div class="table-wrap">
    <table class="report-table">
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Issue</th>
                <th>Location</th>
                <th>Status</th>
                <th>Assigned</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><strong><?php echo e($report['ticket_id']); ?></strong></td>
                    <td>
                        <?php echo e($report['title']); ?>
                        <small><?php echo e($report['issue_type']); ?> · <span class="<?php echo e(getPriorityClass($report['priority'])); ?>"><?php echo e($report['priority']); ?></span></small>
                    </td>
                    <td><?php echo e($report['location']); ?></td>
                    <td><span class="badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></td>
                    <td><?php echo e($report['assigned_to']); ?></td>
                    <td><?php echo e($report['date_submitted']); ?></td>
                    <td><a class="table-link" href="index.php?page=report-details&id=<?php echo e($report['id']); ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reports): ?>
                <tr><td colspan="7" class="empty-state">No reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
