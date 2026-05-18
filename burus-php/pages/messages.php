<?php
global $feedback;

$currentUser = getCurrentUser();
$isResident = $currentUser['role'] === 'resident';
$feedbackItems = $isResident
    ? getFeedbackByResident($feedback, $currentUser['id'])
    : getFeedbackByBarangay($feedback, $currentUser['barangay_id']);

$visibleFeedbackItems = $feedbackItems;
if (!$isResident && isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    $visibleFeedbackItems = array_values(array_filter($feedbackItems, function ($item) use ($filter) {
        if ($filter === 'Unread' || $filter === 'Awaiting Reply') {
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

$selectedId = isset($_GET['feedback_id']) ? (int) $_GET['feedback_id'] : ($feedbackItems[0]['id'] ?? 0);
$selectedFeedback = null;
foreach ($feedbackItems as $item) {
    if ($item['id'] === $selectedId) {
        $selectedFeedback = $item;
        break;
    }
}
$selectedFeedback = $selectedFeedback ?? ($feedbackItems[0] ?? null);

$awaitingCount = countFeedbackByStatus($feedbackItems, 'Awaiting Reply');
$ratedCount = countFeedbackByStatus($feedbackItems, 'Rated');
$readyToRateCount = count(array_filter($feedbackItems, fn($item) => canResidentRate($item)));
$resolvedWithFeedback = count(array_filter($feedbackItems, fn($item) => isResolvedReport($item) && !empty($item['last_message'])));
?>

<?php if ($isResident): ?>
    <section class="feedback-summary">
        <article class="stat-card"><span>My Feedback Threads</span><strong><?php echo count($feedbackItems); ?></strong><small>Linked to my reports</small></article>
        <article class="stat-card"><span>Awaiting Response</span><strong><?php echo e($awaitingCount); ?></strong><small>Needs official reply</small></article>
        <article class="stat-card warning"><span>Resolved Reports to Rate</span><strong><?php echo e($readyToRateCount); ?></strong><small>Ready for rating</small></article>
        <article class="stat-card success"><span>Rated Services</span><strong><?php echo e($ratedCount); ?></strong><small>Feedback completed</small></article>
    </section>

    <section class="feedback-layout">
        <aside class="panel feedback-list-panel">
            <div class="panel-heading compact-heading">
                <div>
                    <h2>My Report Threads</h2>
                    <p>Feedback connected only to your submitted reports.</p>
                </div>
            </div>
            <div class="feedback-thread-list">
                <?php foreach ($feedbackItems as $item): ?>
                    <a class="feedback-thread-card <?php echo $selectedFeedback && $selectedFeedback['id'] === $item['id'] ? 'active' : ''; ?>" href="index.php?page=messages&feedback_id=<?php echo e($item['id']); ?>">
                        <strong><?php echo e($item['ticket_id']); ?></strong>
                        <span><?php echo e($item['issue_type']); ?></span>
                        <small><?php echo e($item['feedback_status']); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <div class="panel">
            <?php if ($selectedFeedback): ?>
                <div class="feedback-detail-head">
                    <div>
                        <span class="section-kicker"><?php echo e($selectedFeedback['ticket_id']); ?></span>
                        <h2><?php echo e($selectedFeedback['issue_type']); ?> Feedback</h2>
                        <p><?php echo e($selectedFeedback['location']); ?></p>
                    </div>
                    <div class="feedback-labels">
                        <span class="badge <?php echo e(getStatusBadgeClass($selectedFeedback['status'])); ?>"><?php echo e($selectedFeedback['status']); ?></span>
                        <span class="feedback-state"><?php echo e($selectedFeedback['feedback_status']); ?></span>
                    </div>
                </div>

                <div class="official-response-box">
                    <span>Official Response / Update</span>
                    <p><?php echo e($selectedFeedback['official_reply'] ?: 'Awaiting official response from the barangay desk.'); ?></p>
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

                <form method="post" class="stack-form feedback-form">
                    <input type="hidden" name="ticket" value="<?php echo e($selectedFeedback['ticket_id']); ?>">
                    <label>Add Comment<textarea name="comment" rows="4">Please provide another update on this report.</textarea></label>
                    <?php if (canResidentRate($selectedFeedback)): ?>
                        <div class="rating-control">
                            <span>Rate Service</span>
                            <div class="star-row">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label><input type="radio" name="rating" value="<?php echo e($i); ?>"><span><?php echo e($i); ?></span></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php elseif (isResolvedReport($selectedFeedback) && !empty($selectedFeedback['rating'])): ?>
                        <div class="rating-control readonly">
                            <span>Rated</span>
                            <strong><?php echo e($selectedFeedback['rating']); ?>/5</strong>
                        </div>
                    <?php else: ?>
                        <div class="rating-control readonly">
                            <span>Rating</span>
                            <strong>Available when resolved</strong>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-primary" name="feedback" type="submit">Submit Feedback</button>
                </form>
            <?php else: ?>
                <div class="empty-state">No feedback threads are connected to your reports yet.</div>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="feedback-summary">
        <article class="stat-card"><span>Total Feedback</span><strong><?php echo count($feedbackItems); ?></strong><small><?php echo e(getCurrentBarangay()['name']); ?></small></article>
        <article class="stat-card danger"><span>Awaiting Reply</span><strong><?php echo e($awaitingCount); ?></strong><small>Resident needs response</small></article>
        <article class="stat-card warning"><span>Low Ratings</span><strong><?php echo e(countLowRatings($feedbackItems)); ?></strong><small>Rating of 3 or below</small></article>
        <article class="stat-card success"><span>Resolved With Feedback</span><strong><?php echo e($resolvedWithFeedback); ?></strong><small>Closed report threads</small></article>
    </section>

    <section class="feedback-layout official-feedback-layout">
        <div class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Resident Feedback</h2>
                    <p>Barangay-wide report conversations, filtered to your barangay only.</p>
                </div>
            </div>
            <form class="filter-bar feedback-filter" method="get">
                <input type="hidden" name="page" value="messages">
                <select name="filter">
                    <option>Unread</option>
                    <option>Awaiting Reply</option>
                    <option>Rated</option>
                    <option>Low Rating</option>
                    <option>Resolved</option>
                </select>
                <button class="btn btn-primary" type="submit">Filter</button>
            </form>
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Resident</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Last Message</th>
                            <th>Rating</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visibleFeedbackItems as $item): ?>
                            <tr>
                                <td><strong><?php echo e($item['ticket_id']); ?></strong></td>
                                <td><?php echo e($item['resident_name']); ?></td>
                                <td><?php echo e($item['issue_type']); ?></td>
                                <td><span class="badge <?php echo e(getStatusBadgeClass($item['status'])); ?>"><?php echo e($item['status']); ?></span></td>
                                <td><?php echo e($item['last_message']); ?></td>
                                <td><?php echo $item['rating'] ? e($item['rating']) . '/5' : 'Not rated'; ?></td>
                                <td><a class="table-link" href="index.php?page=messages&feedback_id=<?php echo e($item['id']); ?>">View / Reply</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$visibleFeedbackItems): ?>
                            <tr><td colspan="7" class="empty-state">No feedback found for this filter.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="panel">
            <h2>Reply / Update</h2>
            <?php if ($selectedFeedback): ?>
                <div class="official-reply-context">
                    <strong><?php echo e($selectedFeedback['ticket_id']); ?></strong>
                    <span><?php echo e($selectedFeedback['resident_name']); ?> · <?php echo e($selectedFeedback['issue_type']); ?></span>
                    <p><?php echo e($selectedFeedback['last_message']); ?></p>
                    <small>Rating: <?php echo $selectedFeedback['rating'] ? e($selectedFeedback['rating']) . '/5' : 'Not rated'; ?></small>
                </div>
                <div class="feedback-conversation compact">
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
                    <label>Official Reply<textarea name="reply" rows="4">We will update you after the next inspection.</textarea></label>
                    <label>Public Update<textarea name="public_update" rows="3">This update will be visible to the resident.</textarea></label>
                    <label>Status Update
                        <select name="status">
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
