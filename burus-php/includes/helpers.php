<?php
require_once __DIR__ . '/../data/sample-data.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
    return array_values(array_filter($reports, function ($report) use ($barangayId) {
        return $report['barangay_id'] === $barangayId;
    }));
}

function getReportsByResident($residentId)
{
    global $reports;
    return array_values(array_filter($reports, fn($report) => $report['resident_id'] === (int) $residentId));
}

function getReportById($id)
{
    global $reports;

    foreach ($reports as $report) {
        if ($report['id'] === (int) $id) {
            return $report;
        }
    }

    return null;
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

function countLowRatings($feedback)
{
    return count(array_filter($feedback, function ($item) {
        return !empty($item['rating']) && $item['rating'] <= 3;
    }));
}

function getActivityLogsByBarangay($barangayId)
{
    global $activityLogs;
    return array_values(array_filter($activityLogs, fn($log) => $log['barangay_id'] === $barangayId));
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

function isAdminLike($user)
{
    return $user && in_array($user['role'], ['admin', 'official'], true);
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
    return isAdminLike($user) ? 'admin-dashboard' : 'resident-dashboard';
}

function pageTitle($page)
{
    $titles = [
        'landing' => 'Transparent Barangay Reporting',
        'login' => 'Sign In',
        'register' => 'Create Account',
        'resident-dashboard' => 'Resident Dashboard',
        'my-reports' => 'My Reports',
        'report-new' => 'Report New Issue',
        'report-details' => 'Ticket Details',
        'notifications' => 'Notifications',
        'messages' => 'Feedback & Response',
        'profile' => 'Profile Settings',
        'admin-dashboard' => 'Admin Dashboard',
        'issue-reports' => 'Issue Reports',
        'resident-directory' => 'Resident Directory',
        'analytics' => 'Analytics',
        'system-settings' => 'System Settings',
        'map-view' => 'Map View',
    ];

    return $titles[$page] ?? 'BURUS';
}
?>
