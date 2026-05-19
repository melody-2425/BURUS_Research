<?php
$user = getCurrentUser();
$myReports = getReportsByResident($user['id']);
$reports = $myReports;
?>
<section class="panel">
    <div class="panel-heading">
        <div><h2>My Reports</h2><p>All utility and infrastructure reports submitted by your account.</p></div>
        <a class="btn btn-primary" href="index.php?page=report-new">New Issue</a>
    </div>
    <?php include __DIR__ . '/partials/report-table.php'; ?>
</section>
