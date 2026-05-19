<?php
$categories = ['Water Leak', 'Drainage Blockage', 'Power Issue', 'Road Damage', 'Street Light', 'Waste / Trash', 'Public Safety', 'Other'];
?>
<section class="form-layout">
    <div class="panel">
        <div class="stepper">
            <span class="active">1 Category</span>
            <span class="active">2 Details</span>
            <span>3 Submit</span>
        </div>
        <form method="post" class="report-form">
            <h2>Select Issue Type</h2>
            <div class="category-grid">
                <?php foreach ($categories as $index => $category): ?>
                    <label class="category-card">
                        <input type="radio" name="issue_type" value="<?php echo e($category); ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                        <span class="category-icon"></span>
                        <strong><?php echo e($category); ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="two-cols-fields">
                <label>Report Title<input type="text" name="title" value="Broken utility line near main road"></label>
                <label>Priority
                    <select name="priority"><option>Medium</option><option>High</option><option>Low</option></select>
                </label>
            </div>
            <label>Description<textarea name="description" rows="5">Describe what happened, when it started, and who may be affected.</textarea></label>
            <div class="location-box">
                <div>
                    <h3>Location</h3>
                    <p>Enter the nearest landmark or street so barangay staff can inspect quickly.</p>
                </div>
                <input type="text" name="location" value="<?php echo e(getCurrentBarangay()['name']); ?> main road">
            </div>
            <label class="upload-box">
                <span class="upload-icon"></span>
                <strong>Upload supporting photo</strong>
                <small>Simulated only, no file is saved.</small>
                <input type="file" name="photo">
            </label>
            <button class="btn btn-primary" type="submit" name="new_report">Submit Complaint</button>
        </form>
    </div>
    <aside class="panel help-panel">
        <h2>Generated Ticket</h2>
        <p>On submit, BURUS simulates a ticket ID like <strong>#BRGY-2026-00123</strong> and shows it in the session flash message.</p>
        <div class="soft-list">
            <span>Barangay scoped</span>
            <span>Resident linked</span>
            <span>Timeline ready</span>
        </div>
    </aside>
</section>
