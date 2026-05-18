<?php
// Small redirect wrapper so map pins can use report-details.php?id=REPORT_ID.
// The main prototype router still renders the reusable page through index.php.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
header('Location: index.php?page=report-details&id=' . $id);
exit;
?>
