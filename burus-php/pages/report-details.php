<?php
$report = getReportById($_GET['id'] ?? 1);
$user = getCurrentUser();
$isStaff = canManageReports($user);
$isResident = isResident($user);

if (!$report || $report['barangay_id'] !== $user['barangay_id']) {
    echo '<section class="panel"><h2>Report not found</h2><p>This ticket is not available for your barangay.</p></section>';
    return;
}

$resident = getUserById($report['resident_id']);
$timelineSteps = ['Submitted', 'Reviewed', 'Assigned', 'Fixed'];
$isSolved = $report['status'] === 'Resolved';
$reportEvidence = $report['evidence'] ?? [];
$resolutionEvidence = $report['resolution_evidence'] ?? [];
$residentConfirmation = $report['resident_confirmation'] ?? null;
$showResolutionEvidence = $isSolved || !empty($resolutionEvidence);
$latestOfficialUpdate = 'The report is waiting for official review.';

foreach (array_reverse($report['comments']) as $comment) {
    if (($comment['sender'] ?? '') !== ($resident['name'] ?? 'Resident')) {
        $latestOfficialUpdate = $comment['message'];
        break;
    }
}

$activityTimeline = [
    ['title' => 'Resident submitted issue with evidence', 'done' => true, 'detail' => $report['date_submitted']],
    ['title' => 'Official reviewed report', 'done' => in_array('Reviewed', $report['timeline'], true), 'detail' => 'Barangay desk review'],
    ['title' => 'Official assigned staff', 'done' => in_array('Assigned', $report['timeline'], true), 'detail' => $report['assigned_to']],
    ['title' => 'Official uploaded resolution proof', 'done' => !empty($resolutionEvidence), 'detail' => $resolutionEvidence[0]['date'] ?? 'Awaiting proof'],
    ['title' => 'Official marked as resolved', 'done' => $isSolved, 'detail' => $isSolved ? 'Resolved' : 'Not yet resolved'],
    ['title' => needsFurtherAttention($report) ? 'Resident requested follow-up' : 'Resident confirmed resolved', 'done' => !empty($residentConfirmation), 'detail' => $residentConfirmation['date'] ?? 'Awaiting resident confirmation'],
];
?>
<section class="page-header">
    <div>
        <span class="section-kicker"><?php echo e($report['ticket_id']); ?></span>
        <h2>Ticket Details</h2>
        <p>Review issue information, evidence, official proof, and resident confirmation.</p>
    </div>
    <span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span>
</section>

<section class="details-layout">
    <div class="grid">
        <article class="panel">
            <div class="ticket-head">
                <div>
                    <h2><?php echo e($report['title']); ?></h2>
                    <p class="muted"><?php echo e($report['description']); ?></p>
                </div>
            </div>

            <div class="info-grid">
                <div><span>Issue Type</span><strong><?php echo e($report['issue_type']); ?></strong></div>
                <div><span>Location</span><strong><?php echo e($report['location']); ?></strong></div>
                <div><span>Submitted By</span><strong><?php echo e($resident['name'] ?? 'Resident'); ?></strong></div>
                <div><span>Assigned Staff / Team</span><strong><?php echo e($report['assigned_to']); ?></strong></div>
                <div><span>Date Submitted</span><strong><?php echo e($report['date_submitted']); ?></strong></div>
                <div><span>Transparency Status</span><strong><?php echo e(getTransparentStatusLabel($report)); ?></strong></div>
            </div>
        </article>

        <article class="panel evidence-panel">
            <div class="panel-heading">
                <div>
                    <h2>Evidence & Documentation</h2>
                    <p>Before-and-after proof helps residents and officials verify the work.</p>
                </div>
            </div>

            <div class="evidence-grid">
                <section>
                    <span class="evidence-label">Resident Evidence</span>
                    <?php foreach ($reportEvidence as $evidence): ?>
                        <article class="evidence-card">
                            <img src="<?php echo e($evidence['image']); ?>" alt="<?php echo e($evidence['title']); ?>">
                            <div>
                                <h3><?php echo e($evidence['title']); ?></h3>
                                <p><?php echo e($evidence['description']); ?></p>
                                <small>Uploaded by <?php echo e($evidence['uploaded_by']); ?> on <?php echo e($evidence['date']); ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$reportEvidence): ?><p class="muted">No resident evidence is attached to this prototype ticket.</p><?php endif; ?>
                </section>

                <section>
                    <span class="evidence-label">Official Resolution Proof</span>
                    <?php if ($showResolutionEvidence && $resolutionEvidence): ?>
                        <?php foreach ($resolutionEvidence as $evidence): ?>
                            <article class="evidence-card">
                                <img src="<?php echo e($evidence['image']); ?>" alt="<?php echo e($evidence['title']); ?>">
                                <div>
                                    <h3><?php echo e($evidence['title']); ?></h3>
                                    <p><?php echo e($evidence['description']); ?></p>
                                    <small>Uploaded by <?php echo e($evidence['uploaded_by']); ?> on <?php echo e($evidence['date']); ?></small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="evidence-empty">
                            <strong>No resolution proof yet</strong>
                            <p>Resolution evidence appears here once an official uploads proof of completion.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </article>

        <article class="panel">
            <h2>Resident Confirmation</h2>
            <?php if ($residentConfirmation): ?>
                <div class="confirmation-card <?php echo needsFurtherAttention($report) ? 'needs-attention' : 'confirmed'; ?>">
                    <span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e($residentConfirmation['status']); ?></span>
                    <strong>Rating: <?php echo e($residentConfirmation['rating']); ?>/5</strong>
                    <p><?php echo e($residentConfirmation['comment']); ?></p>
                    <small>Submitted on <?php echo e($residentConfirmation['date']); ?></small>
                </div>
            <?php elseif ($isSolved): ?>
                <div class="confirmation-card awaiting">
                    <strong>Awaiting Resident Confirmation</strong>
                    <p>Please confirm if this issue has been resolved after reviewing the official proof.</p>
                </div>
            <?php else: ?>
                <p class="muted">Resident confirmation becomes available after the official marks this ticket as resolved.</p>
            <?php endif; ?>
        </article>

        <article class="panel">
            <h2>Activity & Dialogue</h2>
            <div class="activity-documentation">
                <?php foreach ($activityTimeline as $item): ?>
                    <div class="<?php echo $item['done'] ? 'done' : ''; ?>">
                        <span></span>
                        <strong><?php echo e($item['title']); ?></strong>
                        <small><?php echo e($item['detail']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="comment-list">
                <?php foreach ($report['comments'] as $comment): ?>
                    <div class="comment-card">
                        <strong><?php echo e($comment['sender']); ?></strong>
                        <p><?php echo e($comment['message']); ?></p>
                        <small><?php echo e($comment['date'] ?? $report['date_submitted']); ?></small>
                    </div>
                <?php endforeach; ?>
                <?php if (!$report['comments']): ?><p class="muted">No comments yet.</p><?php endif; ?>
            </div>
        </article>
    </div>

    <?php if ($isStaff): ?>
        <aside class="panel action-panel">
            <h2><?php echo $isSolved ? 'Resolution Update' : 'Status Update'; ?></h2>
            <p class="muted"><?php echo isAdmin($user) ? 'Administrator report controls and audit view.' : 'Barangay official complaint management controls.'; ?></p>
            <form method="post" class="stack-form">
                <input type="hidden" name="report_id" value="<?php echo e($report['id']); ?>">
                <label>Status
                    <select class="form-control" name="status">
                        <?php foreach (['Pending', 'In Progress', 'Resolved'] as $status): ?>
                            <option <?php echo $report['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Assign Staff
                    <select class="form-control" name="assigned_to">
                        <?php foreach (getStaffByBarangay($user['barangay_id']) as $member): ?>
                            <option><?php echo e($member['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Resolution Evidence</label>
                <label class="upload-box resolution-upload">
                    <span class="upload-icon"></span>
                    <strong>Upload proof that the issue has been fixed</strong>
                    <small>Prototype only. A sample resolution image is displayed after resolution.</small>
                    <input type="file" name="resolution_photo">
                </label>
                <label>Public Update to Resident<textarea class="form-control" name="official_update" rows="3">This update will be visible to the resident.</textarea></label>
                <label>Reply<textarea class="form-control" name="reply" rows="4">We are reviewing this report.</textarea></label>
                <button class="btn btn-primary full" type="submit" name="update_report"><?php echo $isSolved ? 'Save Resolution Update' : 'Save Update'; ?></button>
            </form>
            <div class="rating-box">
                <span>Resident Confirmation</span>
                <strong><?php echo $residentConfirmation['status'] ?? 'Not submitted'; ?></strong>
            </div>
        </aside>
    <?php else: ?>
        <aside class="panel action-panel readonly-ticket-panel">
            <h2>Ticket Summary</h2>
            <div class="ticket-summary-list">
                <div><span>Current Status</span><strong><span class="status-badge <?php echo e(getTransparentStatusClass($report)); ?>"><?php echo e(getTransparentStatusLabel($report)); ?></span></strong></div>
                <div><span>Assigned Staff</span><strong><?php echo e($report['assigned_to']); ?></strong></div>
                <div><span>Solved Status</span><strong class="<?php echo $isSolved ? 'solved-text' : 'unsolved-text'; ?>"><?php echo e($isSolved ? 'Official marked resolved' : 'Not solved'); ?></strong></div>
                <div><span>Official Reply</span><p><?php echo e($latestOfficialUpdate); ?></p></div>
            </div>

            <?php if ($isSolved && !$residentConfirmation): ?>
                <form method="post" class="stack-form resident-rating-form">
                    <input type="hidden" name="action" value="confirm_resolution">
                    <input type="hidden" name="ticket" value="<?php echo e($report['ticket_id']); ?>">

                    <label>Resolution Confirmation
                        <select class="form-control" name="confirmation_status">
                            <option value="Confirmed Resolved">Confirmed Resolved</option>
                            <option value="Still Needs Attention">Still Needs Attention</option>
                        </select>
                    </label>

                    <label>Rate Service</label>
                    <div class="rating-control">
                        <div class="star-row rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label><input type="radio" name="rating" value="<?php echo e($i); ?>"><span><?php echo e($i); ?></span></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <label>Feedback<textarea class="form-control" name="feedback_comment" rows="4">Please share whether the issue is fully fixed.</textarea></label>

                    <button class="btn btn-primary full" name="feedback" type="submit">Submit Feedback</button>
                </form>
            <?php elseif ($residentConfirmation): ?>
                <div class="rating-box">
                    <span>Resident Rating</span>
                    <strong><?php echo e($residentConfirmation['rating']); ?>/5</strong>
                </div>
            <?php else: ?>
                <div class="rating-box">
                    <span>Resident Feedback</span>
                    <strong>Available when resolved</strong>
                </div>
            <?php endif; ?>
        </aside>
    <?php endif; ?>
</section>
