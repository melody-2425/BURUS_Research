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
        $_SESSION['flash'] = 'Invalid email or password. Try resident@burus.test or admin@burus.test with 123456.';
        header('Location: index.php?page=login');
        exit;
    }

    if (isset($_POST['register'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['flash'] = 'Prototype account created. You are signed in as Maria Santos.';
        header('Location: index.php?page=resident-dashboard');
        exit;
    }

    if (isset($_POST['new_report'])) {
        $_SESSION['created_ticket'] = generateTicketId();
        $_SESSION['flash'] = 'New report ' . $_SESSION['created_ticket'] . ' was simulated successfully.';
        header('Location: index.php?page=my-reports');
        exit;
    }

    if (isset($_POST['update_report'])) {
        $currentUser = getCurrentUser();
        $reportId = (int) ($_POST['report_id'] ?? 1);

        if (!isAdminLike($currentUser)) {
            $_SESSION['flash'] = 'Residents are not allowed to update ticket status.';
            header('Location: index.php?page=report-details&id=' . $reportId);
            exit;
        }

        $_SESSION['flash'] = 'Status update simulated. Data resets when the PHP session ends.';
        header('Location: index.php?page=report-details&id=' . $reportId);
        exit;
    }

    if (isset($_POST['feedback'])) {
        $_SESSION['flash'] = 'Your feedback was recorded for this prototype session.';
        header('Location: index.php?page=messages');
        exit;
    }
}

if (!in_array($page, $publicPages, true)) {
    requireLogin();
}

include __DIR__ . '/includes/header.php';

if (in_array($page, $publicPages, true)) {
    include __DIR__ . '/pages/' . $page . '.php';
} else {
    echo '<div class="app-shell">';
    include __DIR__ . '/includes/sidebar.php';
    echo '<main class="main-panel">';
    include __DIR__ . '/includes/topbar.php';
    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
    include __DIR__ . '/pages/' . $page . '.php';
    echo '</main></div>';
}

include __DIR__ . '/includes/footer.php';
?>
