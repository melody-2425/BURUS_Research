<?php
global $announcements, $barangays;

$currentUser = getCurrentUser();
$canManageAnnouncements = canManageAnnouncement($currentUser);
$showArchived = $canManageAnnouncements && ($_GET['status'] ?? '') === 'archived';
$announcementList = getVisibleAnnouncements($announcements, $currentUser, $showArchived);
$search = trim($_GET['search'] ?? $_GET['q'] ?? '');
$editId = (int) ($_GET['edit'] ?? 0);
$editingAnnouncement = $editId ? getAnnouncementById($editId) : null;

if ($editingAnnouncement && !canManageAnnouncement($currentUser, $editingAnnouncement)) {
    $editingAnnouncement = null;
}

if ($showArchived) {
    $announcementList = array_values(array_filter($announcementList, fn($announcement) => $announcement['status'] === 'archived'));
}

if ($search !== '') {
    $announcementList = array_values(array_filter($announcementList, function ($announcement) use ($search) {
        return stripos($announcement['title'], $search) !== false
            || stripos($announcement['body'], $search) !== false
            || stripos($announcement['category'], $search) !== false
            || stripos($announcement['created_by_name'] ?? '', $search) !== false;
    }));
}

usort($announcementList, function ($a, $b) {
    if (($a['is_pinned'] ?? false) !== ($b['is_pinned'] ?? false)) {
        return ($b['is_pinned'] ?? false) <=> ($a['is_pinned'] ?? false);
    }

    return strtotime($b['updated_at'] ?? $b['created_at'] ?? '') <=> strtotime($a['updated_at'] ?? $a['created_at'] ?? '');
});
?>

<?php if ($canManageAnnouncements): ?>
    <section class="filter-tabs">
        <a class="<?php echo !$showArchived ? 'active' : ''; ?>" href="index.php?page=announcements">Active</a>
        <a class="<?php echo $showArchived ? 'active' : ''; ?>" href="index.php?page=announcements&status=archived">Archived</a>
    </section>
<?php endif; ?>

<section class="announcements-layout">
    <div class="announcement-feed">
        <?php if (empty($announcementList)): ?>
            <article class="panel">
                <h2>No announcements found</h2>
                <p class="muted">There are no announcements matching the current view.</p>
            </article>
        <?php endif; ?>

        <?php foreach ($announcementList as $announcement): ?>
            <?php
            $barangay = getBarangayById($announcement['barangay_id']);
            $scopeLabel = $announcement['scope'] === 'system' ? 'System-wide' : ($barangay['name'] ?? 'Barangay');
            $categoryClass = 'announcement-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $announcement['category']));
            ?>
            <article class="announcement-card <?php echo e($categoryClass); ?> <?php echo !empty($announcement['is_pinned']) ? 'pinned' : ''; ?>">
                <div class="announcement-head">
                    <div>
                        <span class="mini-label"><?php echo e($scopeLabel); ?></span>
                        <h2><?php echo e($announcement['title']); ?></h2>
                    </div>
                    <span class="status-badge <?php echo $announcement['status'] === 'archived' ? 'badge-neutral' : 'badge-resolved'; ?>">
                        <?php echo e(ucfirst($announcement['status'])); ?>
                    </span>
                </div>
                <p><?php echo e($announcement['body']); ?></p>
                <div class="announcement-meta">
                    <span><?php echo e($announcement['category']); ?></span>
                    <?php if (!empty($announcement['is_pinned'])): ?><span>Pinned</span><?php endif; ?>
                    <span>By <?php echo e($announcement['created_by_name'] ?? 'BURUS'); ?></span>
                    <span><?php echo e($announcement['updated_at'] ?: $announcement['created_at']); ?></span>
                </div>

                <?php if ($canManageAnnouncements && canManageAnnouncement($currentUser, $announcement)): ?>
                    <div class="button-row announcement-actions">
                        <a class="btn btn-outline" href="index.php?page=announcements&edit=<?php echo e($announcement['id']); ?>">Edit</a>
                        <?php if ($announcement['status'] !== 'archived'): ?>
                            <form method="post">
                                <input type="hidden" name="announcement_id" value="<?php echo e($announcement['id']); ?>">
                                <button class="btn btn-outline" type="submit" name="archive_announcement">Archive</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="announcement-side">
        <?php if ($canManageAnnouncements): ?>
            <article class="panel">
                <h2><?php echo $editingAnnouncement ? 'Edit Announcement' : 'New Announcement'; ?></h2>
                <form class="stack-form" method="post">
                    <?php if ($editingAnnouncement): ?>
                        <input type="hidden" name="announcement_id" value="<?php echo e($editingAnnouncement['id']); ?>">
                    <?php endif; ?>

                    <label>Title
                        <input class="form-control" type="text" name="title" placeholder="Enter announcement title" value="<?php echo e($editingAnnouncement['title'] ?? ''); ?>" required>
                    </label>

                    <label>Category
                        <input class="form-control" type="text" name="category" placeholder="Maintenance, Community, Advisory" value="<?php echo e($editingAnnouncement['category'] ?? ''); ?>" required>
                    </label>

                    <?php if (isAdmin($currentUser)): ?>
                        <label>Scope
                            <select class="form-control" name="scope">
                                <?php $selectedScope = $editingAnnouncement['scope'] ?? 'barangay'; ?>
                                <option value="barangay" <?php echo $selectedScope === 'barangay' ? 'selected' : ''; ?>>Barangay</option>
                                <option value="system" <?php echo $selectedScope === 'system' ? 'selected' : ''; ?>>System-wide</option>
                            </select>
                        </label>

                        <label>Barangay
                            <select class="form-control" name="barangay_id">
                                <?php $selectedBarangay = $editingAnnouncement['barangay_id'] ?? $currentUser['barangay_id']; ?>
                                <?php foreach ($barangays as $barangay): ?>
                                    <option value="<?php echo e($barangay['id']); ?>" <?php echo $selectedBarangay === $barangay['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($barangay['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php else: ?>
                        <input type="hidden" name="scope" value="barangay">
                        <input type="hidden" name="barangay_id" value="<?php echo e($currentUser['barangay_id']); ?>">
                    <?php endif; ?>

                    <label>Status
                        <select class="form-control" name="status">
                            <?php $selectedStatus = $editingAnnouncement['status'] ?? 'active'; ?>
                            <option value="active" <?php echo $selectedStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="archived" <?php echo $selectedStatus === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </label>

                    <label class="toggle-row">
                        <input type="checkbox" name="is_pinned" <?php echo !empty($editingAnnouncement['is_pinned']) ? 'checked' : ''; ?>>
                        Pin announcement
                    </label>

                    <label>Announcement Details
                        <textarea class="form-control" name="body" rows="5" placeholder="Write the announcement details..." required><?php echo e($editingAnnouncement['body'] ?? ''); ?></textarea>
                    </label>

                    <button class="btn btn-primary full" type="submit" name="save_announcement">
                        <?php echo $editingAnnouncement ? 'Save Changes' : 'Publish Announcement'; ?>
                    </button>
                    <?php if ($editingAnnouncement): ?>
                        <a class="btn btn-outline full" href="index.php?page=announcements">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </article>
        <?php else: ?>
            <article class="quick-card">
                <h2>Resident View</h2>
                <p>Announcements are read-only for residents. Barangay officials publish local updates, while administrators may publish system-wide notices.</p>
            </article>
        <?php endif; ?>
    </aside>
</section>
