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
    'announcements',
    'messages',
    'profile',
    'admin-dashboard',
    'issue-reports',
    'resident-directory',
    'analytics',
    'system-settings',
    'staff-management',
    'map-view',
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'landing';
}

$pageMeta = [
    'resident-dashboard' => [
        'title' => 'Dashboard',
        'subtitle' => 'Here is a summary of your community activities and reports.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'my-reports' => [
        'title' => 'My Reports',
        'subtitle' => 'Track and manage your submitted civic service requests and reports.',
        'show_search' => true,
        'search_placeholder' => 'Search by Ticket ID or Issue Type...',
    ],
    'report-new' => [
        'title' => 'Report New Issue',
        'subtitle' => 'Submit a service request for your community.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'map-view' => [
        'title' => 'Map View',
        'subtitle' => 'View barangay-wide reported issues by location.',
        'show_search' => true,
        'search_placeholder' => 'Search for addresses or specific issues...',
    ],
    'report-details' => [
        'title' => 'Ticket Details',
        'subtitle' => 'Review issue information, evidence, official proof, and resident confirmation.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'notifications' => [
        'title' => 'Notifications',
        'subtitle' => 'Stay updated on the status of your reports, community announcements, and official responses.',
        'show_search' => true,
        'search_placeholder' => 'Search notifications...',
    ],
    'announcements' => [
        'title' => 'Announcements',
        'subtitle' => 'View barangay advisories, community notices, and system-wide updates.',
        'show_search' => true,
        'search_placeholder' => 'Search announcements...',
    ],
    'messages' => [
        'title' => 'Feedback & Response',
        'subtitle' => 'View report-based comments, official replies, and service ratings.',
        'show_search' => true,
        'search_placeholder' => 'Search feedback threads...',
    ],
    'profile' => [
        'title' => 'Account Settings',
        'subtitle' => 'Manage your profile, security, and notification preferences.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'official-dashboard' => [
        'title' => 'Official Dashboard',
        'subtitle' => 'Monitor and manage active barangay complaints.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'admin-dashboard' => [
        'title' => 'Admin Dashboard',
        'subtitle' => 'View system-wide performance and barangay operations.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'issue-reports' => [
        'title' => 'Issue Reports Management',
        'subtitle' => 'Monitor, assign, and resolve community civic complaints.',
        'show_search' => true,
        'search_placeholder' => 'Search reports, residents, or ticket IDs...',
    ],
    'resident-directory' => [
        'title' => 'Resident Directory',
        'subtitle' => 'Manage and verify registered community members.',
        'show_search' => true,
        'search_placeholder' => 'Search residents...',
    ],
    'analytics' => [
        'title' => 'Analytics',
        'subtitle' => 'Review barangay issue trends and system performance.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'system-settings' => [
        'title' => 'System Settings',
        'subtitle' => 'Manage barangay configuration, roles, security, and system logs.',
        'show_search' => false,
        'search_placeholder' => '',
    ],
    'staff-management' => [
        'title' => 'Staff Management',
        'subtitle' => 'Add and track barangay staff assignments for maintenance and issue response teams.',
        'show_search' => true,
        'search_placeholder' => 'Search staff member or issue specialty...',
    ],
];

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

    if (isset($_POST['add_staff_member'])) {
        $currentUser = getCurrentUser();

        if (!$currentUser || !isAdmin($currentUser)) {
            $_SESSION['flash'] = 'Access denied. Only administrators can add staff members.';
            header('Location: index.php?page=' . ($currentUser ? routeForUser($currentUser) : 'login'));
            exit;
        }

        global $staff;

        $staff = loadJson('staff.json');
        $name = trim($_POST['name'] ?? '');
        $department = trim($_POST['department'] ?? 'Public Works');
        $status = trim($_POST['status'] ?? 'Available');
        $allowedStatuses = ['Available', 'Assigned', 'On Leave'];
        $issueTypes = array_values(array_filter(array_map('trim', $_POST['issue_types'] ?? [])));

        if ($name === '') {
            $_SESSION['flash'] = 'Staff name is required.';
            header('Location: index.php?page=staff-management');
            exit;
        }

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'Available';
        }

        $staff[] = [
            'id' => getNextId($staff),
            'barangay_id' => $currentUser['barangay_id'],
            'name' => $name,
            'department' => $department !== '' ? $department : 'Public Works',
            'status' => $status,
            'issue_types' => $issueTypes,
        ];

        saveJson('staff.json', $staff);
        $_SESSION['flash'] = 'Staff member added to response roster.';
        header('Location: index.php?page=staff-management');
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
        $updateTimestamp = phTimestamp();
        foreach ($reports as &$report) {
            if ((int) $report['id'] === $reportId) {
                if (!isSuperAdmin($currentUser) && (($report['barangay_id'] ?? '') !== ($currentUser['barangay_id'] ?? ''))) {
                    unset($report);
                    $_SESSION['flash'] = 'Access denied. This report belongs to another barangay.';
                    header('Location: index.php?page=issue-reports');
                    exit;
                }

                $updatedTicket = $report['ticket_id'];
                $previousStatus = $report['status'] ?? 'Pending';
                $previousAssignee = $report['assigned_to'] ?? 'Unassigned';
                $newStatus = $_POST['status'] ?? $previousStatus;
                $newAssignee = $_POST['assigned_to'] ?? $previousAssignee;
                $report['status'] = $newStatus;
                $updatedStatus = $report['status'];
                $report['assigned_to'] = $newAssignee;
                $report['last_updated_at'] = $updateTimestamp;
                $report['last_updated_by'] = $currentUser['name'];
                $report['last_updated_role'] = $currentUser['role'];
                if ($newStatus !== $previousStatus) {
                    $report['status_updated_at'] = $updateTimestamp;
                    $report['status_updated_by'] = $currentUser['name'];
                }
                if ($newAssignee !== $previousAssignee) {
                    $report['assigned_at'] = $updateTimestamp;
                    $report['assigned_by'] = $currentUser['name'];
                }
                if ($newAssignee !== 'Unassigned' && empty($report['assigned_at'])) {
                    $report['assigned_at'] = $updateTimestamp;
                    $report['assigned_by'] = $currentUser['name'];
                }
                $report['timeline'] = $report['timeline'] ?? ['Submitted'];
                if (!in_array('Reviewed', $report['timeline'], true)) {
                    $report['timeline'][] = 'Reviewed';
                    $report['reviewed_at'] = $report['reviewed_at'] ?? $updateTimestamp;
                }
                if (($report['assigned_to'] ?? 'Unassigned') !== 'Unassigned' && !in_array('Assigned', $report['timeline'], true)) {
                    $report['timeline'][] = 'Assigned';
                    $report['assigned_at'] = $report['assigned_at'] ?? $updateTimestamp;
                }
                if ($report['status'] === 'Resolved') {
                    if (!in_array('Fixed', $report['timeline'], true)) {
                        $report['timeline'][] = 'Fixed';
                    }
                    $report['resolved_at'] = $report['resolved_at'] ?? $updateTimestamp;
                    $report['resolved_by'] = $currentUser['name'];
                    if (empty($report['resolution_evidence'])) {
                        $report['resolution_evidence'][] = [
                            'type' => 'official_resolution',
                            'title' => 'Resolution Photo',
                            'description' => 'Photo uploaded by official showing that the issue has been fixed.',
                            'image' => 'assets/images/sample-water-leak-after.jpg',
                            'uploaded_by' => $currentUser['name'],
                            'uploaded_role' => $currentUser['role'],
                            'date' => $updateTimestamp,
                        ];
                    }
                }

                $officialMessage = trim($_POST['official_update'] ?? $_POST['reply'] ?? 'Report status updated.');
                $report['official_updates'] = $report['official_updates'] ?? [];
                $report['official_updates'][] = [
                    'sender' => $currentUser['name'],
                    'status' => $report['status'],
                    'assigned_to' => $report['assigned_to'],
                    'message' => $officialMessage,
                    'date' => $updateTimestamp,
                ];
                $report['comments'][] = [
                    'sender' => $currentUser['name'],
                    'message' => $officialMessage,
                    'date' => $updateTimestamp,
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
                    'date' => $updateTimestamp,
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
                    if ((int) ($item['resident_id'] ?? 0) !== (int) $currentUser['id']) {
                        unset($item);
                        $_SESSION['flash'] = 'Access denied. This feedback thread belongs to another resident.';
                        header('Location: index.php?page=messages');
                        exit;
                    }

                    $message = trim($_POST['comment'] ?? 'Resident submitted feedback.');
                    $item['last_message'] = $message;
                    $item['messages'][] = [
                        'sender_role' => 'resident',
                        'sender_name' => $currentUser['name'],
                        'message' => $message,
                        'date' => date('F j, Y h:i A'),
                    ];
                } elseif (canManageReports($currentUser)) {
                    if (!isSuperAdmin($currentUser) && (($item['barangay_id'] ?? '') !== ($currentUser['barangay_id'] ?? ''))) {
                        unset($item);
                        $_SESSION['flash'] = 'Access denied. This feedback thread belongs to another barangay.';
                        header('Location: index.php?page=messages');
                        exit;
                    }

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

    if (isset($_POST['save_announcement'])) {
        global $announcements;

        $currentUser = getCurrentUser();
        if (!canManageAnnouncement($currentUser)) {
            $_SESSION['flash'] = 'Access denied. Residents can only view announcements.';
            header('Location: index.php?page=announcements');
            exit;
        }

        $announcements = loadJson('announcements.json');
        $announcementId = (int) ($_POST['announcement_id'] ?? 0);
        $scope = isSuperAdmin($currentUser) ? ($_POST['scope'] ?? 'barangay') : 'barangay';
        $barangayId = isSuperAdmin($currentUser)
            ? ($_POST['barangay_id'] ?? $currentUser['barangay_id'])
            : $currentUser['barangay_id'];

        if ($scope === 'system') {
            $barangayId = 'all';
        } else {
            $scope = 'barangay';
        }

        $payload = [
            'title' => trim($_POST['title'] ?? 'Barangay Announcement'),
            'body' => trim($_POST['body'] ?? ''),
            'content' => trim($_POST['body'] ?? ''),
            'category' => trim($_POST['category'] ?? 'General'),
            'priority' => $_POST['priority'] ?? 'Normal',
            'barangay_id' => $barangayId,
            'scope' => $scope,
            'status' => $_POST['status'] ?? 'Published',
            'is_system_wide' => $scope === 'system',
            'is_pinned' => isset($_POST['is_pinned']),
            'pin_date' => isset($_POST['is_pinned']) ? ($_POST['pin_date'] ?: date('Y-m-d')) : null,
            'pinned_until' => isset($_POST['is_pinned']) ? ($_POST['pinned_until'] ?: date('Y-m-d')) : null,
            'date_posted' => $_POST['date_posted'] ?? date('Y-m-d h:i A'),
            'posted_by' => $currentUser['name'],
            'posted_by_role' => $currentUser['role'],
            'updated_at' => date('F j, Y h:i A'),
        ];

        if ($announcementId > 0) {
            foreach ($announcements as &$announcement) {
                if ((int) $announcement['id'] === $announcementId) {
                    if (!canManageAnnouncement($currentUser, $announcement)) {
                        unset($announcement);
                        $_SESSION['flash'] = 'Access denied. Officials can only manage announcements for their own barangay.';
                        header('Location: index.php?page=announcements');
                        exit;
                    }

                    $announcement = array_merge($announcement, $payload);
                    break;
                }
            }
            unset($announcement);
            $_SESSION['flash'] = 'Announcement updated.';
        } else {
            $announcements[] = array_merge([
                'id' => getNextId($announcements),
                'created_by' => (int) $currentUser['id'],
                'created_by_name' => $currentUser['name'],
                'created_at' => date('F j, Y h:i A'),
            ], $payload);
            $_SESSION['flash'] = 'Announcement published.';
        }

        saveJson('announcements.json', $announcements);
        header('Location: index.php?page=announcements');
        exit;
    }

    if (isset($_POST['archive_announcement'])) {
        global $announcements;

        $currentUser = getCurrentUser();
        $announcementId = (int) ($_POST['announcement_id'] ?? 0);
        $announcements = loadJson('announcements.json');

        foreach ($announcements as &$announcement) {
            if ((int) $announcement['id'] === $announcementId) {
                if (!canManageAnnouncement($currentUser, $announcement)) {
                    unset($announcement);
                    $_SESSION['flash'] = 'Access denied. You cannot archive that announcement.';
                    header('Location: index.php?page=announcements');
                    exit;
                }

                $announcement['status'] = 'Archived';
                $announcement['updated_at'] = date('F j, Y h:i A');
                break;
            }
        }
        unset($announcement);

        saveJson('announcements.json', $announcements);
        $_SESSION['flash'] = 'Announcement archived.';
        header('Location: index.php?page=announcements');
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

    if ($page === 'report-details') {
        $requestedReport = getReportById($reports ?? [], (int) ($_GET['id'] ?? 0));

        if (!$requestedReport) {
            $_SESSION['flash'] = 'Report not found.';
            header('Location: index.php?page=' . (canManageReports($currentUser) ? 'issue-reports' : 'map-view'));
            exit;
        }

        if (!isSuperAdmin($currentUser) && (($requestedReport['barangay_id'] ?? '') !== ($currentUser['barangay_id'] ?? ''))) {
            $_SESSION['flash'] = 'Access denied. This report belongs to another barangay.';
            header('Location: index.php?page=' . (canManageReports($currentUser) ? 'issue-reports' : 'map-view'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';

if (in_array($page, $publicPages, true)) {
    include __DIR__ . '/pages/' . $page . '.php';
} else {
    $currentPage = $page;
    $currentPageMeta = $pageMeta[$currentPage] ?? [
        'title' => 'BURUS',
        'subtitle' => '',
        'show_search' => false,
        'search_placeholder' => '',
    ];
    if ($currentPage === 'admin-dashboard' && !isSuperAdmin($currentUser)) {
        $currentPageMeta['title'] = getCurrentBarangay()['name'] . ' Admin Dashboard';
        $currentPageMeta['subtitle'] = 'Manage complaints and barangay operations for your assigned barangay.';
    }

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
