<?php
require_once __DIR__ . '/storage.php';

$barangays = loadJson('barangays.json');
$users = loadJson('users.json');
$reports = loadJson('reports.json');
$feedback = loadJson('feedback.json');
$notifications = loadJson('notifications.json');
$staff = loadJson('staff.json');
$announcements = loadJson('announcements.json');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function phTimestamp($format = 'F j, Y h:i A')
{
    $date = new DateTime('now', new DateTimeZone('Asia/Manila'));
    return $date->format($format);
}

function getCurrentUser()
{
    global $users;

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) {
            return $user;
        }
    }

    return null;
}

function getUserById($id)
{
    global $users;

    foreach ($users as $user) {
        if ($user['id'] === (int) $id) {
            return $user;
        }
    }

    return null;
}

function getUserNameById($users, $userId)
{
    if (!is_array($users)) {
        return 'Unknown Resident';
    }

    foreach ($users as $user) {
        if (($user['id'] ?? null) == $userId) {
            return $user['name'] ?? 'Unknown Resident';
        }
    }

    return 'Unknown Resident';
}

function findUserByEmailAndPassword($email, $password)
{
    global $users;

    foreach ($users as $user) {
        if ($user['email'] === $email && $user['password'] === $password) {
            return $user;
        }
    }

    return null;
}

function getCurrentBarangay()
{
    global $barangays;
    $user = getCurrentUser();

    if (!$user) {
        return $barangays[0];
    }

    foreach ($barangays as $barangay) {
        if ($barangay['id'] === $user['barangay_id']) {
            return $barangay;
        }
    }

    return $barangays[0];
}

function getBarangayById($barangayId)
{
    global $barangays;

    foreach ($barangays as $barangay) {
        if ($barangay['id'] === $barangayId) {
            return $barangay;
        }
    }

    return null;
}

function getReportsByBarangay($reports, $barangayId)
{
    if (!is_array($reports)) {
        return [];
    }

    $reports = array_map('normalizeReportRecord', $reports);

    return array_values(array_filter($reports, function ($report) use ($barangayId) {
        return ($report['barangay_id'] ?? '') === $barangayId;
    }));
}

function getReportsByResident($reportsOrResidentId, $residentId = null)
{
    global $reports;

    if ($residentId === null) {
        $residentId = $reportsOrResidentId;
        $sourceReports = $reports;
    } else {
        $sourceReports = $reportsOrResidentId;
    }

    $sourceReports = array_map('normalizeReportRecord', $sourceReports);

    return array_values(array_filter($sourceReports, fn($report) => $report['resident_id'] === (int) $residentId));
}

function getReportById($reportsOrId, $id = null)
{
    global $reports;

    if ($id === null) {
        $id = $reportsOrId;
        $sourceReports = $reports;
    } else {
        $sourceReports = $reportsOrId;
    }

    if (!is_array($sourceReports)) {
        return null;
    }

    foreach ($sourceReports as $report) {
        if ((int) ($report['id'] ?? 0) === (int) $id) {
            return normalizeReportRecord($report);
        }
    }

    return null;
}

function normalizeReportRecord($report)
{
    if (!isset($report['pin']) && isset($report['pin_x'], $report['pin_y'])) {
        $report['pin'] = ['x' => (int) $report['pin_x'], 'y' => (int) $report['pin_y']];
    }

    if (!isset($report['pin'])) {
        $report['pin'] = ['x' => 50, 'y' => 50];
    }

    $report['evidence'] = $report['evidence'] ?? [];
    $report['resolution_evidence'] = $report['resolution_evidence'] ?? [];
    $report['resident_confirmation'] = $report['resident_confirmation'] ?? null;
    $report['timeline'] = $report['timeline'] ?? ['Submitted'];
    $report['comments'] = $report['comments'] ?? [];
    $report['resident_name'] = $report['resident_name'] ?? getUserNameById($GLOBALS['users'] ?? [], $report['resident_id'] ?? null);
    $report['date_submitted'] = $report['date_submitted'] ?? 'No date';

    if (empty($report['time_submitted'])) {
        $evidenceDate = $report['evidence'][0]['date'] ?? '';
        $commentDate = $report['comments'][0]['date'] ?? '';
        $sourceDate = preg_match('/\d{1,2}:\d{2}/', $evidenceDate) ? $evidenceDate : $commentDate;
        $timestamp = $sourceDate ? strtotime($sourceDate) : false;
        $report['time_submitted'] = $timestamp ? date('h:i A', $timestamp) : 'No time';
    }

    return $report;
}

function countReportsByStatus($reports, $status)
{
    return count(array_filter($reports, fn($report) => $report['status'] === $status));
}

function getStatusBadgeClass($status)
{
    $map = [
        'Pending' => 'badge-pending',
        'In Progress' => 'badge-progress',
        'Resolved' => 'badge-resolved',
    ];

    return $map[$status] ?? 'badge-neutral';
}

function getPriorityClass($priority)
{
    $map = [
        'High' => 'priority-high',
        'Medium' => 'priority-medium',
        'Low' => 'priority-low',
    ];

    return $map[$priority] ?? 'priority-low';
}

function getStaffByBarangay($barangayId)
{
    global $staff;
    return array_values(array_filter($staff, fn($member) => $member['barangay_id'] === $barangayId));
}

function getUsersByBarangay($barangayId)
{
    global $users;
    return array_values(array_filter($users, fn($user) => $user['barangay_id'] === $barangayId));
}

function getResidentsByBarangay($barangayId)
{
    return array_values(array_filter(getUsersByBarangay($barangayId), fn($user) => $user['role'] === 'resident'));
}

function getNotificationsForCurrentUser()
{
    global $notifications;
    $user = getCurrentUser();

    if (!$user) {
        return [];
    }

    return array_values(array_filter($notifications, function ($notification) use ($user) {
        return $notification['barangay_id'] === $user['barangay_id'] && $notification['user_id'] === $user['id'];
    }));
}

function getFeedbackByResident($feedback, $residentId)
{
    return array_values(array_filter($feedback, function ($item) use ($residentId) {
        return $item['resident_id'] == $residentId;
    }));
}

function getFeedbackByBarangay($feedback, $barangayId)
{
    return array_values(array_filter($feedback, function ($item) use ($barangayId) {
        return $item['barangay_id'] === $barangayId;
    }));
}

function countFeedbackByStatus($feedback, $status)
{
    return count(array_filter($feedback, function ($item) use ($status) {
        return $item['feedback_status'] === $status;
    }));
}

function isResolvedReport($item)
{
    return $item['status'] === 'Resolved';
}

function canResidentRate($item)
{
    return $item['status'] === 'Resolved' && empty($item['rating']);
}

function getNotificationsByUser($userId, $barangayId)
{
    global $notifications;

    return array_values(array_filter($notifications, function ($notification) use ($userId, $barangayId) {
        return $notification['barangay_id'] === $barangayId && $notification['user_id'] === (int) $userId;
    }));
}

function normalizeAnnouncementRecord($announcement)
{
    $status = $announcement['status'] ?? 'Published';
    if ($status === 'active') {
        $status = 'Published';
    } elseif ($status === 'archived') {
        $status = 'Archived';
    }

    $announcement['status'] = $status;
    $announcement['is_system_wide'] = !empty($announcement['is_system_wide'])
        || ($announcement['scope'] ?? '') === 'system'
        || ($announcement['barangay_id'] ?? '') === 'all';
    $announcement['scope'] = $announcement['scope'] ?? ($announcement['is_system_wide'] ? 'system' : 'barangay');
    $announcement['barangay_id'] = $announcement['barangay_id'] ?? 'all';
    $announcement['category'] = $announcement['category'] ?? 'General';
    $announcement['is_pinned'] = !empty($announcement['is_pinned']);
    $announcement['created_at'] = $announcement['created_at'] ?? '';
    $announcement['updated_at'] = $announcement['updated_at'] ?? $announcement['created_at'];
    $announcement['date_posted'] = $announcement['date_posted'] ?? ($announcement['updated_at'] ?: $announcement['created_at']);
    $announcement['content'] = $announcement['content'] ?? ($announcement['body'] ?? '');
    $announcement['body'] = $announcement['body'] ?? $announcement['content'];
    $announcement['posted_by'] = $announcement['posted_by'] ?? ($announcement['created_by_name'] ?? 'BURUS');
    $announcement['created_by_name'] = $announcement['created_by_name'] ?? $announcement['posted_by'];
    $announcement['posted_by_role'] = $announcement['posted_by_role'] ?? '';
    $announcement['priority'] = $announcement['priority'] ?? 'Normal';
    $announcement['pin_date'] = $announcement['pin_date'] ?? null;
    $announcement['pinned_until'] = $announcement['pinned_until'] ?? null;

    return $announcement;
}

function getAnnouncementById($id)
{
    global $announcements;

    foreach ($announcements as $announcement) {
        if ((int) $announcement['id'] === (int) $id) {
            return normalizeAnnouncementRecord($announcement);
        }
    }

    return null;
}

function getVisibleAnnouncements($announcements, $user, $includeArchived = false)
{
    $announcements = array_map('normalizeAnnouncementRecord', $announcements);

    return array_values(array_filter($announcements, function ($announcement) use ($user, $includeArchived) {
        if (!$includeArchived && $announcement['status'] === 'Archived') {
            return false;
        }

        if (isSuperAdmin($user)) {
            return true;
        }

        if (($announcement['scope'] ?? '') === 'system' || ($announcement['barangay_id'] ?? '') === 'all') {
            return true;
        }

        return ($announcement['barangay_id'] ?? '') === ($user['barangay_id'] ?? '');
    }));
}

function getAnnouncementsForUser($announcements, $currentUser)
{
    if (!is_array($announcements) || !$currentUser) {
        return [];
    }

    $announcements = array_map('normalizeAnnouncementRecord', $announcements);

    if (isSuperAdmin($currentUser)) {
        return array_values($announcements);
    }

    return array_values(array_filter($announcements, function ($announcement) use ($currentUser) {
        return $announcement['status'] !== 'Archived'
            && (
                (!empty($announcement['is_system_wide']) && $announcement['is_system_wide'] === true)
                || ($announcement['barangay_id'] ?? '') === ($currentUser['barangay_id'] ?? '')
            );
    }));
}

function getLatestAnnouncements($announcements, $limit = 3)
{
    if (!is_array($announcements)) {
        return [];
    }

    $announcements = array_map('normalizeAnnouncementRecord', $announcements);
    $announcements = array_values(array_filter($announcements, fn($announcement) => ($announcement['status'] ?? 'Published') !== 'Archived'));

    usort($announcements, function ($a, $b) {
        if (($a['is_pinned'] ?? false) !== ($b['is_pinned'] ?? false)) {
            return ($b['is_pinned'] ?? false) <=> ($a['is_pinned'] ?? false);
        }

        return strtotime($b['date_posted'] ?? '') <=> strtotime($a['date_posted'] ?? '');
    });

    return array_slice($announcements, 0, $limit);
}

function getDashboardPinnedAnnouncement($announcements, $currentUser)
{
    if (!is_array($announcements) || !$currentUser) {
        return null;
    }

    $today = date('Y-m-d');
    $announcements = array_map('normalizeAnnouncementRecord', $announcements);

    $filtered = array_filter($announcements, function ($announcement) use ($currentUser, $today) {
        $isVisibleToUser =
            isSuperAdmin($currentUser)
            || (!empty($announcement['is_system_wide']))
            || (($announcement['barangay_id'] ?? '') === ($currentUser['barangay_id'] ?? ''));

        $isPublished = ($announcement['status'] ?? '') === 'Published';

        $isPinnedToday =
            !empty($announcement['is_pinned'])
            && !empty($announcement['pin_date'])
            && !empty($announcement['pinned_until'])
            && $announcement['pin_date'] <= $today
            && $announcement['pinned_until'] >= $today;

        return $isVisibleToUser && $isPublished && $isPinnedToday;
    });

    $filtered = array_values($filtered);

    usort($filtered, function ($a, $b) {
        $priorityOrder = [
            'Emergency' => 1,
            'Important' => 2,
            'Normal' => 3,
        ];

        $aPriority = $priorityOrder[$a['priority'] ?? 'Normal'] ?? 3;
        $bPriority = $priorityOrder[$b['priority'] ?? 'Normal'] ?? 3;

        if ($aPriority === $bPriority) {
            return strtotime($b['date_posted'] ?? '') <=> strtotime($a['date_posted'] ?? '');
        }

        return $aPriority <=> $bPriority;
    });

    return $filtered[0] ?? null;
}

function canManageAnnouncement($user, $announcement = null)
{
    if (isSuperAdmin($user)) {
        return true;
    }

    if (!isOfficial($user) && !isAdmin($user)) {
        return false;
    }

    if (!$announcement) {
        return true;
    }

    $announcement = normalizeAnnouncementRecord($announcement);
    return $announcement['scope'] === 'barangay'
        && $announcement['barangay_id'] === $user['barangay_id'];
}

function isConfirmedResolved($report)
{
    return isset($report['resident_confirmation'])
        && ($report['resident_confirmation']['status'] ?? '') === 'Confirmed Resolved';
}

function needsFurtherAttention($report)
{
    return isset($report['resident_confirmation'])
        && ($report['resident_confirmation']['status'] ?? '') === 'Still Needs Attention';
}

function getTransparentStatusLabel($report)
{
    if (needsFurtherAttention($report)) {
        return 'Follow-up Needed';
    }

    if (($report['status'] ?? '') === 'Resolved' && isConfirmedResolved($report)) {
        return 'Confirmed Resolved';
    }

    if (($report['status'] ?? '') === 'Resolved') {
        return 'Awaiting Resident Confirmation';
    }

    if (($report['status'] ?? '') === 'In Progress') {
        return 'Being Worked On';
    }

    return 'Not Solved';
}

function getTransparentStatusClass($report)
{
    if (needsFurtherAttention($report)) {
        return 'status-followup';
    }

    if (($report['status'] ?? '') === 'Resolved' && isConfirmedResolved($report)) {
        return 'status-resolved';
    }

    if (($report['status'] ?? '') === 'Resolved') {
        return 'status-awaiting';
    }

    if (($report['status'] ?? '') === 'In Progress') {
        return 'status-progress';
    }

    return 'status-pending';
}

function countLowRatings($feedback)
{
    return count(array_filter($feedback, function ($item) {
        return !empty($item['rating']) && $item['rating'] <= 3;
    }));
}

function getActivityLogsByBarangay($barangayId) {
    global $activityLogs;

    if (!isset($activityLogs) || !is_array($activityLogs)) {
        return [];
    }

    return array_values(array_filter($activityLogs, function($log) use ($barangayId) {
        return isset($log['barangay_id']) && $log['barangay_id'] === $barangayId;
    }));
}

function getCommonIssueTypes($reports)
{
    $counts = [];

    foreach ($reports as $report) {
        $type = $report['issue_type'];
        $counts[$type] = ($counts[$type] ?? 0) + 1;
    }

    arsort($counts);
    return $counts;
}

function generateTicketId()
{
    return '#BRGY-' . date('Y') . '-' . str_pad((string) random_int(120, 999), 5, '0', STR_PAD_LEFT);
}

function isResident($user)
{
    return $user && $user['role'] === 'resident';
}

function isOfficial($user)
{
    return $user && $user['role'] === 'official';
}

function isAdmin($user)
{
    return $user && $user['role'] === 'admin';
}

function isSuperAdmin($user)
{
    return isset($user['role']) && $user['role'] === 'super_admin';
}

function canManageReports($user)
{
    return $user && in_array($user['role'], ['official', 'admin', 'super_admin'], true);
}

function getReportsVisibleToUser($reports, $currentUser)
{
    if (!is_array($reports) || !$currentUser) {
        return [];
    }

    $reports = array_map('normalizeReportRecord', $reports);

    if (isSuperAdmin($currentUser)) {
        return array_values($reports);
    }

    if (in_array($currentUser['role'], ['admin', 'official'], true)) {
        return array_values(array_filter($reports, function ($report) use ($currentUser) {
            return ($report['barangay_id'] ?? '') === ($currentUser['barangay_id'] ?? '');
        }));
    }

    if ($currentUser['role'] === 'resident') {
        return array_values(array_filter($reports, function ($report) use ($currentUser) {
            return ($report['resident_id'] ?? null) == ($currentUser['id'] ?? null);
        }));
    }

    return [];
}

function getResidentsVisibleToUser($users, $currentUser)
{
    if (!is_array($users) || !$currentUser) {
        return [];
    }

    if (isSuperAdmin($currentUser)) {
        return array_values(array_filter($users, fn($user) => ($user['role'] ?? '') === 'resident'));
    }

    if (in_array($currentUser['role'], ['admin', 'official'], true)) {
        return array_values(array_filter($users, function ($user) use ($currentUser) {
            return ($user['role'] ?? '') === 'resident'
                && ($user['barangay_id'] ?? '') === ($currentUser['barangay_id'] ?? '');
        }));
    }

    return [];
}

function canAccessSystemSettings($user)
{
    return isAdmin($user) || isSuperAdmin($user);
}

function canAccessAnalytics($user)
{
    return isAdmin($user) || isSuperAdmin($user);
}

function canAccessResidentDirectory($user)
{
    return isAdmin($user) || isSuperAdmin($user);
}

function isAdminLike($user)
{
    return canManageReports($user);
}

function roleLabel($user)
{
    if (isSuperAdmin($user)) {
        return 'Super Admin';
    }

    if (isAdmin($user)) {
        return 'Barangay Admin';
    }

    if (isOfficial($user)) {
        return 'Barangay Official';
    }

    if (isResident($user)) {
        return 'Resident';
    }

    return 'Guest';
}

function allowedPagesForRole($user)
{
    if (isResident($user)) {
        return [
            'resident-dashboard',
            'my-reports',
            'report-new',
            'map-view',
            'report-details',
            'messages',
            'notifications',
            'announcements',
            'profile',
        ];
    }

    if (isOfficial($user)) {
        return [
            'official-dashboard',
            'issue-reports',
            'map-view',
            'report-details',
            'messages',
            'notifications',
            'announcements',
            'profile',
        ];
    }

    if (isAdmin($user) || isSuperAdmin($user)) {
        return [
            'admin-dashboard',
            'issue-reports',
            'resident-directory',
            'analytics',
            'system-settings',
            'staff-management',
            'map-view',
            'report-details',
            'messages',
            'notifications',
            'announcements',
            'profile',
        ];
    }

    return [];
}

function canAccessPage($user, $page)
{
    return in_array($page, allowedPagesForRole($user), true);
}

function requireLogin()
{
    if (!getCurrentUser()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function routeForUser($user)
{
    if (isSuperAdmin($user) || isAdmin($user)) {
        return 'admin-dashboard';
    }

    if (isOfficial($user)) {
        return 'official-dashboard';
    }

    return 'resident-dashboard';
}

function pageTitle($page)
{
    $titles = [
        'landing' => 'Transparent Barangay Reporting',
        'login' => 'Sign In',
        'register' => 'Create Account',
        'resident-dashboard' => 'Resident Dashboard',
        'official-dashboard' => 'Official Dashboard',
        'my-reports' => 'My Reports',
        'report-new' => 'Report New Issue',
        'report-details' => 'Ticket Details',
        'notifications' => 'Notifications',
        'announcements' => 'Announcements',
        'messages' => 'Feedback & Response',
        'profile' => 'Account Settings',
        'admin-dashboard' => 'Admin Dashboard',
        'issue-reports' => 'Issue Reports',
        'resident-directory' => 'Resident Directory',
        'analytics' => 'Analytics',
        'system-settings' => 'System Settings',
        'staff-management' => 'Staff Management',
        'map-view' => 'Map View',
    ];

    return $titles[$page] ?? 'BURUS';
}
?>
