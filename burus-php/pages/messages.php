<?php
global $feedback, $reports, $users;

$currentUser = getCurrentUser();
$isResident = $currentUser['role'] === 'resident';
$residentReports = [];
$residentFeedbackByReportId = [];
$residentThreadReports = [];

if ($isResident) {
    $residentReports = getReportsByResident($reports ?? [], $currentUser['id']);
    $feedbackItems = getFeedbackByResident($feedback ?? [], $currentUser['id']);
    foreach ($feedbackItems as $item) {
        $residentFeedbackByReportId[(int) ($item['report_id'] ?? 0)] = $item;
    }
    $residentThreadReports = $residentReports;
} elseif (isSuperAdmin($currentUser)) {
    $feedbackItems = $feedback ?? [];
} elseif (in_array($currentUser['role'], ['admin', 'official'], true)) {
    $feedbackItems = getFeedbackByBarangay($feedback ?? [], $currentUser['barangay_id']);
} else {
    $feedbackItems = getFeedbackByResident($feedback ?? [], $currentUser['id']);
}
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
if ($search !== '') {
    $feedbackItems = array_values(array_filter($feedbackItems, function ($item) use ($search) {
        return stripos($item['ticket_id'], $search) !== false
            || stripos($item['resident_name'], $search) !== false
            || stripos($item['issue_type'], $search) !== false
            || stripos($item['location'], $search) !== false
            || stripos($item['last_message'], $search) !== false;
    }));
}
if ($isResident && $search !== '') {
    $residentThreadReports = array_values(array_filter($residentThreadReports, function ($report) use ($search, $residentFeedbackByReportId) {
        $threadFeedback = $residentFeedbackByReportId[(int) ($report['id'] ?? 0)] ?? null;

        return stripos($report['ticket_id'], $search) !== false
            || stripos($report['resident_name'] ?? '', $search) !== false
            || stripos($report['issue_type'], $search) !== false
            || stripos($report['location'], $search) !== false
            || stripos($report['title'] ?? '', $search) !== false
            || ($threadFeedback && stripos($threadFeedback['last_message'] ?? '', $search) !== false);
    }));
}

$visibleFeedbackItems = $feedbackItems;
if (!$isResident && isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    $visibleFeedbackItems = array_values(array_filter($feedbackItems, function ($item) use ($filter) {
        if ($filter === 'Awaiting Reply') {
            return $item['feedback_status'] === 'Awaiting Reply' || empty($item['official_reply']);
        }
        if ($filter === 'Rated') {
            return !empty($item['rating']);
        }
        if ($filter === 'Low Rating') {
            return !empty($item['rating']) && $item['rating'] <= 3;
        }
        if ($filter === 'Resolved') {
            return $item['status'] === 'Resolved';
        }
        return true;
    }));
}

$selectedId = isset($_GET['feedback_id']) ? (int) $_GET['feedback_id'] : ($isResident ? 0 : ($feedbackItems[0]['id'] ?? 0));
$selectedReportId = isset($_GET['report_id']) ? (int) $_GET['report_id'] : 0;
$selectedFeedback = null;
foreach ($feedbackItems as $item) {
    if ($item['id'] === $selectedId) {
        $selectedFeedback = $item;
        break;
    }
}
if ($isResident) {
    if (!$selectedReportId && $selectedFeedback) {
        $selectedReportId = (int) ($selectedFeedback['report_id'] ?? 0);
    }
    if (!$selectedReportId && !empty($residentThreadReports)) {
        $selectedReportId = (int) $residentThreadReports[0]['id'];
    }

    $selectedReport = $selectedReportId ? getReportById($reports ?? [], $selectedReportId) : null;
    if ($selectedReport && (int) ($selectedReport['resident_id'] ?? 0) !== (int) $currentUser['id']) {
        $selectedReport = null;
    }
    $selectedFeedback = $selectedReport ? ($residentFeedbackByReportId[(int) $selectedReport['id']] ?? null) : null;
} else {
    $selectedFeedback = $selectedFeedback ?? ($feedbackItems[0] ?? null);
    $selectedReport = $selectedFeedback ? getReportById($selectedFeedback['report_id']) : null;
}

$awaitingCount = $isResident
    ? count(array_filter($residentReports, fn($report) => ($residentFeedbackByReportId[(int) $report['id']]['feedback_status'] ?? 'Awaiting Reply') === 'Awaiting Reply'))
    : countFeedbackByStatus($feedbackItems, 'Awaiting Reply');
$ratedCount = countFeedbackByStatus($feedbackItems, 'Rated');
$readyToRateCount = $isResident
    ? count(array_filter($residentReports, fn($report) => ($report['status'] ?? '') === 'Resolved' && empty($residentFeedbackByReportId[(int) $report['id']]['rating'])))
    : count(array_filter($feedbackItems, fn($item) => canResidentRate($item)));
$resolvedWithFeedback = $isResident
    ? count(array_filter($residentReports, fn($report) => ($report['status'] ?? '') === 'Resolved' && !empty($residentFeedbackByReportId[(int) $report['id']]['last_message'])))
    : count(array_filter($feedbackItems, fn($item) => isResolvedReport($item) && !empty($item['last_message'])));
?>
<section class="stats-row">
    <article class="stat-card"><span><?php echo $isResident ? 'My Threads' : 'Total Feedback'; ?></span><strong><?php echo $isResident ? count($residentReports) : count($feedbackItems); ?></strong><small>Report conversations</small></article>
    <article class="stat-card warning"><span>Awaiting Reply</span><strong><?php echo e($awaitingCount); ?></strong><small>Needs response</small></article>
    <article class="stat-card"><span><?php echo $isResident ? 'Ready to Rate' : 'Rated'; ?></span><strong><?php echo e($isResident ? $readyToRateCount : $ratedCount); ?></strong><small>Service feedback</small></article>
    <article class="stat-card success"><span>Resolved With Feedback</span><strong><?php echo e($resolvedWithFeedback); ?></strong><small>Closed threads</small></article>
</section>

<?php if ($isResident): ?>
    <section class="feedback-layout">
        <aside class="panel feedback-list-panel">
            <div class="panel-heading">
                <div>
                    <h2>My Report Threads</h2>
                    <p>Only feedback connected to your reports appears here.</p>
                </div>
            </div>
            <div class="feedback-thread-list">
                <?php foreach ($residentThreadReports as $report): ?>
                    <?php
                    $threadFeedback = $residentFeedbackByReportId[(int) $report['id']] ?? null;
                    $feedbackStatus = $threadFeedback['feedback_status'] ?? 'Awaiting Reply';
                    $residentName = $report['resident_name'] ?? getUserNameById($users ?? [], $report['resident_id'] ?? null);
                    $threadHref = $threadFeedback
                        ? 'index.php?page=messages&feedback_id=' . urlencode((string) $threadFeedback['id'])
                        : 'index.php?page=messages&report_id=' . urlencode((string) $report['id']);
                    $isActiveThread = $selectedReport && (int) $selectedReport['id'] === (int) $report['id'];
                    ?>
                    <a class="feedback-thread-card report-thread-card <?php echo $isActiveThread ? 'active' : ''; ?>" href="<?php echo e($threadHref); ?>">
                        <div class="thread-top">
                            <strong><?php echo e($report['ticket_id']); ?></strong>
                            <span class="status-badge <?php echo e(getStatusBadgeClass($report['status'])); ?>"><?php echo e($report['status']); ?></span>
                        </div>
                        <h4><?php echo e($report['title'] ?? $report['issue_type']); ?></h4>
                        <p><?php echo e($report['issue_type']); ?></p>
                        <div class="thread-meta">
                            <span>Submitted by <?php echo e($residentName); ?></span>
                            <span><?php echo e($report['date_submitted'] ?? 'No date'); ?> <?php echo e($report['time_submitted'] ?? 'No time'); ?></span>
                        </div>
                        <div class="thread-status-row">
                            <span>Report Status</span>
                            <strong><?php echo e($report['status']); ?></strong>
                        </div>
                        <div class="thread-status-row">
                            <span>Feedback Status</span>
                            <strong><?php echo e($feedbackStatus); ?></strong>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (!$residentThreadReports): ?><p class="empty-state">No submitted reports found.</p><?php endif; ?>
            </div>
        </aside>

        <div class="panel">
            <?php if ($selectedReport): ?>
                <div class="feedback-detail-head">
                    <div>
                        <span class="section-kicker"><?php echo e($selectedReport['ticket_id']); ?></span>
                        <h2><?php echo e($selectedReport['issue_type']); ?> Feedback</h2>
                        <p class="muted"><?php echo e($selectedReport['location']); ?></p>
                    </div>
                    <span class="status-badge <?php echo e(getTransparentStatusClass($selectedReport)); ?>"><?php echo e(getTransparentStatusLabel($selectedReport)); ?></span>
                </div>

                <div class="official-response-box">
                    <strong>Official Response / Update</strong>
                    <p><?php echo e(($selectedFeedback['official_reply'] ?? '') ?: 'Awaiting official response from the barangay desk.'); ?></p>
                </div>
                <?php if ($selectedReport && needsFurtherAttention($selectedReport)): ?>
                    <div class="confirmation-card needs-attention">
                        <span class="status-badge status-followup">Resident reported unresolved issue</span>
                        <p><?php echo e($selectedReport['resident_confirmation']['comment']); ?></p>
                        <small>Rating: <?php echo e($selectedReport['resident_confirmation']['rating']); ?>/5 - <?php echo e($selectedReport['resident_confirmation']['date']); ?></small>
                    </div>
                <?php elseif ($selectedReport && isConfirmedResolved($selectedReport)): ?>
                    <div class="confirmation-card confirmed">
                        <span class="status-badge status-resolved">Confirmed Resolved</span>
                        <p><?php echo e($selectedReport['resident_confirmation']['comment']); ?></p>
                        <small>Rating: <?php echo e($selectedReport['resident_confirmation']['rating']); ?>/5 - <?php echo e($selectedReport['resident_confirmation']['date']); ?></small>
                    </div>
                <?php endif; ?>

                <div class="feedback-conversation">
                    <?php foreach (($selectedFeedback['messages'] ?? []) as $message): ?>
                        <div class="feedback-message <?php echo e($message['sender_role']); ?>">
                            <strong><?php echo e($message['sender_name']); ?></strong>
                            <p><?php echo e($message['message']); ?></p>
                            <small><?php echo e($message['date']); ?></small>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($selectedFeedback['messages'])): ?><p class="muted">No feedback messages yet for this report.</p><?php endif; ?>
                </div>

                <form method="post" class="stack-form">
                    <input type="hidden" name="ticket" value="<?php echo e($selectedReport['ticket_id']); ?>">
                    <label>Add Comment<textarea class="form-control" name="comment" rows="4" placeholder="Write your comment or follow-up message"></textarea></label>
                    <?php if ($selectedFeedback && canResidentRate($selectedFeedback)): ?>
                        <div class="rating-control">
                            <span>Rate Service</span>
                            <div class="star-row">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label><input type="radio" name="rating" value="<?php echo e($i); ?>"><span><?php echo e($i); ?></span></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php elseif ($selectedFeedback && isResolvedReport($selectedFeedback) && !empty($selectedFeedback['rating'])): ?>
                        <div class="rating-control"><span>Rated</span><strong><?php echo e($selectedFeedback['rating']); ?>/5</strong></div>
                    <?php else: ?>
                        <div class="rating-control"><span>Rating</span><strong>Available when resolved</strong></div>
                    <?php endif; ?>
                    <button class="btn btn-primary" name="feedback" type="submit">Submit Feedback</button>
                </form>
            <?php else: ?>
                <div class="empty-state">No feedback threads are connected to your reports yet.</div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="feedback-layout">
        <div class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Resident Feedback</h2>
                    <p>Barangay-wide report conversations filtered to <?php echo e(getCurrentBarangay()['name']); ?>.</p>
                </div>
            </div>
            <form class="filter-bar" method="get" style="grid-template-columns: minmax(200px,260px) auto;">
                <input type="hidden" name="page" value="messages">
                <select class="form-control" name="filter">
                    <?php foreach (['Awaiting Reply', 'Rated', 'Low Rating', 'Resolved'] as $filterOption): ?>
                        <option <?php echo ($_GET['filter'] ?? '') === $filterOption ? 'selected' : ''; ?>><?php echo e($filterOption); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Filter</button>
            </form>
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr><th>Ticket</th><th>Resident</th><th>Issue</th><th>Status</th><th>Last Message</th><th>Rating</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visibleFeedbackItems as $item): ?>
                            <tr>
                                <td><strong><?php echo e($item['ticket_id']); ?></strong></td>
                                <td><?php echo e($item['resident_name']); ?></td>
                                <td><?php echo e($item['issue_type']); ?></td>
                                <?php $itemReport = getReportById($item['report_id']); ?>
                                <td>
                                    <?php if ($itemReport): ?>
                                        <span class="status-badge <?php echo e(getTransparentStatusClass($itemReport)); ?>"><?php echo e(getTransparentStatusLabel($itemReport)); ?></span>
                                    <?php else: ?>
                                        <span class="status-badge <?php echo e(getStatusBadgeClass($item['status'])); ?>"><?php echo e($item['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($item['last_message']); ?></td>
                                <td><?php echo $item['rating'] ? e($item['rating']) . '/5' : 'Not rated'; ?></td>
                                <td><a class="table-link" href="index.php?page=messages&feedback_id=<?php echo e($item['id']); ?>">View / Reply</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$visibleFeedbackItems): ?><tr><td colspan="7" class="empty-state">No feedback found for this filter.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="panel">
            <h2>Reply / Update</h2>
            <?php if ($selectedFeedback): ?>
                <div class="official-reply-context">
                    <strong><?php echo e($selectedFeedback['ticket_id']); ?></strong>
                    <span><?php echo e($selectedFeedback['resident_name']); ?> - <?php echo e($selectedFeedback['issue_type']); ?></span>
                    <p><?php echo e($selectedFeedback['last_message']); ?></p>
                    <small>Rating: <?php echo $selectedFeedback['rating'] ? e($selectedFeedback['rating']) . '/5' : 'Not rated'; ?></small>
                </div>
                <div class="feedback-conversation">
                    <?php foreach ($selectedFeedback['messages'] as $message): ?>
                        <div class="feedback-message <?php echo e($message['sender_role']); ?>">
                            <strong><?php echo e($message['sender_name']); ?></strong>
                            <p><?php echo e($message['message']); ?></p>
                            <small><?php echo e($message['date']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="stack-form">
                    <input type="hidden" name="ticket" value="<?php echo e($selectedFeedback['ticket_id']); ?>">
                    <label>Official Reply<textarea class="form-control" name="reply" rows="4" placeholder="Write your official reply"></textarea></label>
                    <label>Public Update<textarea class="form-control" name="public_update" rows="3" placeholder="Write a public update visible to the resident"></textarea></label>
                    <label>Status Update
                        <select class="form-control" name="status">
                            <?php foreach (['Pending', 'In Progress', 'Resolved'] as $status): ?>
                                <option <?php echo $selectedFeedback['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn btn-primary full" name="feedback" type="submit">Save Reply / Update</button>
                </form>
            <?php else: ?>
                <div class="empty-state">No barangay feedback threads found.</div>
            <?php endif; ?>
        </aside>
    </section>
<?php endif; ?>
