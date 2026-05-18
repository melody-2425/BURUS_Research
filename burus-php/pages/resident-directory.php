<?php
global $reports;
$residents = getResidentsByBarangay(getCurrentBarangay()['id']);
?>
<section class="panel">
    <div class="panel-heading">
        <div><h2>Resident Directory</h2><p>Residents registered under <?php echo e(getCurrentBarangay()['name']); ?>.</p></div>
    </div>
    <div class="table-wrap">
        <table class="report-table">
            <thead><tr><th>Name</th><th>Email</th><th>Contact</th><th>Address</th><th>Reports</th></tr></thead>
            <tbody>
                <?php foreach ($residents as $resident): ?>
                    <tr>
                        <td><strong><?php echo e($resident['name']); ?></strong></td>
                        <td><?php echo e($resident['email']); ?></td>
                        <td><?php echo e($resident['contact']); ?></td>
                        <td><?php echo e($resident['address']); ?></td>
                        <td><?php echo count(getReportsByResident($resident['id'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
