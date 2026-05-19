<?php $barangay = getCurrentBarangay(); ?>
<section class="settings-grid">
    <div class="panel">
        <h2>Barangay Settings</h2>
        <form class="stack-form two-cols">
            <label>Barangay Name<input type="text" value="<?php echo e($barangay['name']); ?>"></label>
            <label>City<input type="text" value="<?php echo e($barangay['city']); ?>"></label>
            <label>Contact<input type="text" value="<?php echo e($barangay['contact']); ?>"></label>
            <label>Barangay Captain<input type="text" value="<?php echo e($barangay['captain']); ?>"></label>
            <label class="span-2">Office Hours<input type="text" value="<?php echo e($barangay['office_hours']); ?>"></label>
            <button class="btn btn-primary span-2" type="button">Save Settings</button>
        </form>
    </div>
    <div class="panel">
        <h2>Notification Rules</h2>
        <div class="toggle-list">
            <label><input type="checkbox" checked> Report accepted notification</label>
            <label><input type="checkbox" checked> Status changed notification</label>
            <label><input type="checkbox" checked> Report resolved notification</label>
            <label><input type="checkbox"> Weekly analytics digest</label>
        </div>
    </div>
</section>
