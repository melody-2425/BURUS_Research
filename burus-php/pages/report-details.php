<?php
$report = getReportById($_GET['id'] ?? 1);
$user = getCurrentUser();
$isStaff = in_array($user['role'], ['official', 'admin'], true);
$isResident = $user['role'] === 'resident';

if (!$report || $report['barangay_id'] !== $user['barangay_id']) {
    echo '<section class="panel"><h2>Report not found</h2><p>This ticket is not available for your barangay.</p></section>';
    return;
}
$resident = getUserById($report['resident_id']);
$timelineSteps = ['Submitted', 'Reviewed', 'Assigned', 'Fixed'];
$isSolved = $report['status'] === 'Resolved';
$latestOfficialUpdate = 'The report is waiting for official review.';
foreach (array_reverse($report['comments']) as $comment) {
    if (($comment['sender'] ?? '') !== ($resident['name'] ?? 'Resident')) {
        $latestOfficialUpdate = $comment['message'];
        break;
    }
}
?>
<section class="details-layout">
    <div class="panel">
        <div class="ticket-head">
            <div>
                <span class="section-kicker"><?php echo e($report['ticket_id']); ?></span>
                <h2><?php echo e($report['title']); ?></h2>
                <p><?php echo e($report['description']); ?></p>
            </div>
            <span class="badge <?php echo e(getStatusBadgeClass($report['status'])); ?>"><?php echo e($report['status']); ?></span>
        </div>
        <div class="info-grid">
            <div><span>Issue Type</span><strong><?php echo e($report['issue_type']); ?></strong></div>
            <div><span>Location</span><strong><?php echo e($report['location']); ?></strong></div>
            <div><span>Submitted By</span><strong><?php echo e($resident['name'] ?? 'Resident'); ?></strong></div>
            <div><span>Assigned Staff</span><strong><?php echo e($report['assigned_to']); ?></strong></div>
        </div>
        <h3>Timeline</h3>
        <div class="timeline">
            <?php foreach ($timelineSteps as $step): ?>
                <div class="<?php echo in_array($step, $report['timeline'], true) ? 'done' : ''; ?>">
                    <span></span>
                    <strong><?php echo e($step); ?></strong>
                    <small><?php echo in_array($step, $report['timeline'], true) ? 'Completed' : 'Pending'; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <h3>Comments</h3>
        <div class="comment-list">
            <?php foreach ($report['comments'] as $comment): ?>
                <div class="comment"><strong><?php echo e($comment['sender']); ?></strong><p><?php echo e($comment['message']); ?></p><small><?php echo e($comment['date'] ?? $report['date_submitted']); ?></small></div>
            <?php endforeach; ?>
            <?php if (!$report['comments']): ?><p class="muted">No comments yet.</p><?php endif; ?>
        </div>
    </div>
    <?php if ($isStaff): ?>
        <aside class="panel action-panel">
            <h2>Status Update</h2>
            <form method="post" class="stack-form">
                <input type="hidden" name="report_id" value="<?php echo e($report['id']); ?>">
                <label>Status
                    <select name="status">
                        <?php foreach (['Pending', 'In Progress', 'Resolved'] as $status): ?>
                            <option <?php echo $report['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Assign Staff
                    <select name="assigned_to">
                        <?php foreach (getStaffByBarangay($user['barangay_id']) as $member): ?>
                            <option><?php echo e($member['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Reply<textarea name="reply" rows="4">We are reviewing this report.</textarea></label>
                <button class="btn btn-primary full" type="submit" name="update_report">Save Update</button>
            </form>
            <div class="rating-box">
                <span>Resident Rating</span>
                <strong><?php echo $report['rating'] ? e($report['rating']) . '/5' : 'Not rated'; ?></strong>
            </div>
        </aside>
    <?php else: ?>
        <aside class="panel action-panel readonly-ticket-panel">
            <h2>Ticket Status</h2>
            <div class="ticket-summary-list">
                <div>
                    <span>Current Status</span>
                    <strong><span class="badge <?php echo e(getStatusBadgeClass($report['status'])); ?>"><?php echo e($report['status']); ?></span></strong>
                </div>
                <div>
                    <span>Assigned Staff</span>
                    <strong><?php echo e($report['assigned_to']); ?></strong>
                </div>
                <div>
                    <span>Solved Status</span>
                    <strong class="<?php echo $isSolved ? 'solved-text' : 'unsolved-text'; ?>"><?php echo e($isSolved ? 'Solved' : 'Not Solved'); ?></strong>
                </div>
                <div>
                    <span>Latest Official Update</span>
                    <p><?php echo e($latestOfficialUpdate); ?></p>
                </div>
            </div>

            <?php if ($isSolved): ?>
                <div class="rating-box">
                    <span>Resident Rating</span>
                    <strong><?php echo $report['rating'] ? e($report['rating']) . '/5' : 'Ready for rating'; ?></strong>
                </div>
                <?php if (empty($report['rating'])): ?>
                    <form method="post" class="stack-form resident-rating-form">
                        <input type="hidden" name="ticket" value="<?php echo e($report['ticket_id']); ?>">
                        <label>Feedback<textarea name="comment" rows="4">Thank you for resolving this report.</textarea></label>
                        <div class="rating-control">
                            <span>Rate Service</span>
                            <div class="star-row">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label><input type="radio" name="rating" value="<?php echo e($i); ?>"><span><?php echo e($i); ?></span></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <button class="btn btn-primary full" name="feedback" type="submit">Submit Rating</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="rating-box">
                    <span>Resident Rating</span>
                    <strong>Available when resolved</strong>
                </div>
            <?php endif; ?>
        </aside>
    <?php endif; ?>
</section>
