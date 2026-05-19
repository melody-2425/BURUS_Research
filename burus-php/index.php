<?php
session_start();
require_once __DIR__ . '/includes/helpers.php';

// Default to a resident account so the prototype opens directly into a working demo.
if (!isset($_SESSION['user_id']) && !in_array($_GET['page'] ?? 'landing', ['landing', 'login', 'register'], true)) {
    $_SESSION['user_id'] = 1;
}

$page = $_GET['page'] ?? 'landing';
$publicPages = ['landing', 'login', 'register'];
$allowedPages = [
    'landing',
    'login',
    'register',
    'resident-dashboard',
    'official-dashboard',
    'my-reports',
    'report-new',
    'report-details',
    'notifications',
    'messages',
    'profile',
    'admin-dashboard',
    'issue-reports',
    'resident-directory',
    'analytics',
    'system-settings',
    'map-view',
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'landing';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $user = findUserByEmailAndPassword($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php?page=' . routeForUser($user));
            exit;
        }
        $_SESSION['flash'] = 'Invalid email or password. Try resident@burus.test, official@burus.test, or admin@burus.test with 123456.';
        header('Location: index.php?page=login');
        exit;
    }

    if (isset($_POST['register'])) {
        global $users;

        $users = loadJson('users.json');
        $role = strtolower($_POST['role'] ?? 'resident');
        if (!in_array($role, ['resident', 'official', 'admin'], true)) {
            $role = 'resident';
        }

        $newUser = [
            'id' => getNextId($users),
            'name' => trim($_POST['name'] ?? 'New User'),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '123456',
            'role' => $role,
            'barangay_id' => $_POST['barangay_id'] ?? 'tejero',
            'address' => trim($_POST['address'] ?? ''),
            'contact' => trim($_POST['contact'] ?? ''),
        ];

        $users[] = $newUser;
        saveJson('users.json', $users);

        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['flash'] = 'Account created. You are signed in as ' . $newUser['name'] . '.';
        header('Location: index.php?page=' . routeForUser($newUser));
        exit;
    }

    if (isset($_POST['new_report'])) {
        global $reports, $feedback, $notifications;

        $currentUser = getCurrentUser();
        $reports = loadJson('reports.json');
        $feedback = loadJson('feedback.json');
        $notifications = loadJson('notifications.json');
        $ticketId = generateTicketId();
        $issueType = $_POST['issue_type'] ?? 'Other';
        $pinX = random_int(20, 80);
        $pinY = random_int(25, 75);

        $newReport = [
            'id' => getNextId($reports),
            'ticket_id' => $ticketId,
            'barangay_id' => $currentUser['barangay_id'],
            'resident_id' => (int) $currentUser['id'],
            'issue_type' => $issueType,
            'title' => trim($_POST['title'] ?? 'New barangay report'),
            'description' => trim($_POST['description'] ?? ''),
            'location' => trim($_POST['location'] ?? getCurrentBarangay()['name']),
            'status' => 'Pending',
            'assigned_to' => 'Unassigned',
            'date_submitted' => date('F j, Y'),
            'priority' => $_POST['priority'] ?? 'Medium',
            'timeline' => ['Submitted'],
            'comments' => [
                [
                    'sender' => $currentUser['name'],
                    'message' => 'Report submitted with initial evidence.',
                    'date' => date('F j, Y h:i A'),
                ],
            ],
            'rating' => null,
            'pin' => ['x' => $pinX, 'y' => $pinY],
            'pin_x' => $pinX,
            'pin_y' => $pinY,
            'evidence' => [
                [
                    'type' => 'resident_upload',
                    'title' => 'Initial Report Photo',
                    'description' => 'Photo submitted by resident showing the reported issue.',
                    'image' => 'assets/images/sample-water-leak-before.jpg',
                    'uploaded_by' => $currentUser['name'],
                    'uploaded_role' => 'resident',
                    'date' => date('F j, Y h:i A'),
                ],
            ],
            'resolution_evidence' => [],
            'resident_confirmation' => null,
        ];

        $reports[] = $newReport;
        saveJson('reports.json', $reports);

        $feedback[] = [
            'id' => getNextId($feedback),
            'report_id' => $newReport['id'],
            'ticket_id' => $ticketId,
            'barangay_id' => $currentUser['barangay_id'],
            'resident_id' => (int) $currentUser['id'],
            'resident_name' => $currentUser['name'],
            'issue_type' => $issueType,
            'location' => $newReport['location'],
            'status' => 'Pending',
            'last_message' => 'Report submitted with initial evidence.',
            'official_reply' => '',
            'rating' => null,
            'feedback_status' => 'Awaiting Reply',
            'messages' => [
                [
                    'sender_role' => 'resident',
                    'sender_name' => $currentUser['name'],
                    'message' => 'Report submitted with initial evidence.',
                    'date' => date('F j, Y h:i A'),
                ],
            ],
        ];
        saveJson('feedback.json', $feedback);

        $notifications[] = [
            'id' => getNextId($notifications),
            'barangay_id' => $currentUser['barangay_id'],
            'user_id' => (int) $currentUser['id'],
            'title' => 'Report Submitted',
            'message' => 'Your report ' . $ticketId . ' has been submitted.',
            'type' => 'info',
            'is_read' => false,
            'date' => date('F j, Y'),
        ];
        saveJson('notifications.json', $notifications);

        $_SESSION['created_ticket'] = $ticketId;
        $_SESSION['flash'] = 'New report ' . $ticketId . ' was saved and is now visible to barangay officials.';
        header('Location: index.php?page=my-reports');
        exit;
    }

    if (isset($_POST['update_report'])) {
        $currentUser = getCurrentUser();
        $reportId = (int) ($_POST['report_id'] ?? 1);

        if (!canManageReports($currentUser)) {
            $_SESSION['flash'] = 'Residents are not allowed to update ticket status.';
            header('Location: index.php?page=report-details&id=' . $reportId);
            exit;
        }

        global $reports, $feedback;

        $reports = loadJson('reports.json');
        $feedback = loadJson('feedback.json');
        $updatedTicket = '';
        $updatedStatus = '';
        foreach ($reports as &$report) {
            if ((int) $report['id'] === $reportId) {
                $updatedTicket = $report['ticket_id'];
                $report['status'] = $_POST['status'] ?? $report['status'];
                $updatedStatus = $report['status'];
                $report['assigned_to'] = $_POST['assigned_to'] ?? $report['assigned_to'];
                $report['timeline'] = $report['timeline'] ?? ['Submitted'];
                if (!in_array('Reviewed', $report['timeline'], true)) {
                    $report['timeline'][] = 'Reviewed';
                }
                if (($report['assigned_to'] ?? 'Unassigned') !== 'Unassigned' && !in_array('Assigned', $report['timeline'], true)) {
                    $report['timeline'][] = 'Assigned';
                }
                if ($report['status'] === 'Resolved') {
                    if (!in_array('Fixed', $report['timeline'], true)) {
                        $report['timeline'][] = 'Fixed';
                    }
                    if (empty($report['resolution_evidence'])) {
                        $report['resolution_evidence'][] = [
                            'type' => 'official_resolution',
                            'title' => 'Resolution Photo',
                            'description' => 'Photo uploaded by official showing that the issue has been fixed.',
                            'image' => 'assets/images/sample-water-leak-after.jpg',
                            'uploaded_by' => $currentUser['name'],
                            'uploaded_role' => $currentUser['role'],
                            'date' => date('F j, Y h:i A'),
                        ];
                    }
                }

                $officialMessage = trim($_POST['official_update'] ?? $_POST['reply'] ?? 'Report status updated.');
                $report['comments'][] = [
                    'sender' => $currentUser['name'],
                    'message' => $officialMessage,
                    'date' => date('F j, Y h:i A'),
                ];
                break;
            }
        }
        unset($report);
        saveJson('reports.json', $reports);

        foreach ($feedback as &$item) {
            if ($item['ticket_id'] === $updatedTicket) {
                $item['status'] = $updatedStatus ?: $item['status'];
                $item['official_reply'] = trim($_POST['official_update'] ?? $_POST['reply'] ?? $item['official_reply']);
                $item['feedback_status'] = 'Official Replied';
                $item['messages'][] = [
                    'sender_role' => 'official',
                    'sender_name' => $currentUser['name'],
                    'message' => $item['official_reply'],
                    'date' => date('F j, Y h:i A'),
                ];
                break;
            }
        }
        unset($item);
        saveJson('feedback.json', $feedback);

        $_SESSION['flash'] = 'Status update saved to reports.json.';
        header('Location: index.php?page=report-details&id=' . $reportId);
        exit;
    }

    if (($_POST['action'] ?? '') === 'confirm_resolution') {
        global $reports, $feedback;

        $currentUser = getCurrentUser();
        $reports = loadJson('reports.json');
        $feedback = loadJson('feedback.json');
        $ticket = $_POST['ticket'] ?? '';
        $confirmationStatus = $_POST['confirmation_status'] ?? 'Confirmed Resolved';
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['feedback_comment'] ?? '');
        $reportId = 0;

        foreach ($reports as &$report) {
            if ($report['ticket_id'] === $ticket && (int) $report['resident_id'] === (int) $currentUser['id']) {
                $reportId = (int) $report['id'];
                $report['resident_confirmation'] = [
                    'confirmed' => $confirmationStatus === 'Confirmed Resolved',
                    'status' => $confirmationStatus,
                    'comment' => $comment,
                    'rating' => $rating,
                    'date' => date('F j, Y h:i A'),
                ];
                $report['rating'] = $rating ?: null;
                $report['comments'][] = [
                    'sender' => $currentUser['name'],
                    'message' => $confirmationStatus . ': ' . $comment,
                    'date' => date('F j, Y h:i A'),
                ];
                break;
            }
        }
        unset($report);

        foreach ($feedback as &$item) {
            if ($item['ticket_id'] === $ticket) {
                $item['last_message'] = $comment;
                $item['rating'] = $rating ?: null;
                $item['feedback_status'] = $confirmationStatus === 'Still Needs Attention' ? 'Awaiting Reply' : 'Rated';
                $item['messages'][] = [
                    'sender_role' => 'resident',
                    'sender_name' => $currentUser['name'],
                    'message' => $confirmationStatus . ': ' . $comment,
                    'date' => date('F j, Y h:i A'),
                ];
                break;
            }
        }
        unset($item);

        saveJson('reports.json', $reports);
        saveJson('feedback.json', $feedback);

        $_SESSION['flash'] = 'Resident confirmation saved to JSON storage.';
        header('Location: index.php?page=report-details&id=' . $reportId);
        exit;
    }

    if (isset($_POST['feedback'])) {
        global $feedback;

        $currentUser = getCurrentUser();
        $feedback = loadJson('feedback.json');
        $ticket = $_POST['ticket'] ?? '';
        foreach ($feedback as &$item) {
            if ($item['ticket_id'] === $ticket) {
                if (isResident($currentUser)) {
                    $message = trim($_POST['comment'] ?? 'Resident submitted feedback.');
                    $item['last_message'] = $message;
                    $item['messages'][] = [
                        'sender_role' => 'resident',
                        'sender_name' => $currentUser['name'],
                        'message' => $message,
                        'date' => date('F j, Y h:i A'),
                    ];
                } elseif (canManageReports($currentUser)) {
                    $message = trim($_POST['reply'] ?? $_POST['public_update'] ?? 'Official replied to resident.');
                    $item['official_reply'] = $message;
                    $item['feedback_status'] = 'Official Replied';
                    $item['messages'][] = [
                        'sender_role' => 'official',
                        'sender_name' => $currentUser['name'],
                        'message' => $message,
                        'date' => date('F j, Y h:i A'),
                    ];
                }
                break;
            }
        }
        unset($item);
        saveJson('feedback.json', $feedback);

        $_SESSION['flash'] = 'Your feedback was saved to feedback.json.';
        header('Location: index.php?page=messages');
        exit;
    }
}

if (!in_array($page, $publicPages, true)) {
    requireLogin();

    $currentUser = getCurrentUser();
    if (!canAccessPage($currentUser, $page)) {
        if (isOfficial($currentUser) && in_array($page, ['system-settings', 'analytics', 'resident-directory'], true)) {
            $_SESSION['flash'] = 'Access denied. This page is only available to administrators.';
            header('Location: index.php?page=official-dashboard');
            exit;
        }

        $_SESSION['flash'] = 'Access denied. Your account does not have permission to open that page.';
        header('Location: index.php?page=' . routeForUser($currentUser));
        exit;
    }
}

include __DIR__ . '/includes/header.php';

if (in_array($page, $publicPages, true)) {
    include __DIR__ . '/pages/' . $page . '.php';
} else {
    echo '<div class="app-shell">';
    include __DIR__ . '/includes/sidebar.php';
    echo '<main class="main-content">';
    include __DIR__ . '/includes/topbar.php';
    echo '<div class="page-content">';
    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
    include __DIR__ . '/pages/' . $page . '.php';
    echo '</div></main></div>';
}

include __DIR__ . '/includes/footer.php';
?>
