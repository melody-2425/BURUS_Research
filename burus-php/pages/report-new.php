<?php
$categories = ['Water Leak', 'Power Outage', 'Road Repair', 'Public Space', 'Waste / Trash', 'Street Light', 'Public Safety', 'Other'];
$barangay = getCurrentBarangay();
global $reports;
$nearbyReports = array_slice(getReportsByBarangay($reports, $barangay['id']), 0, 3);
?>
<section class="page-header">
    <div>
        <h2>Report New Issue</h2>
        <p>Submit a barangay infrastructure or utility concern with clear details, location, and optional evidence.</p>
    </div>
</section>

<section class="form-layout">
    <div class="panel">
        <div class="stepper">
            <span class="active">1 Category</span>
            <span class="active">2 Details</span>
            <span class="active">3 Location</span>
            <span>4 Evidence</span>
        </div>

        <form method="post" class="report-form">
            <section class="card">
                <h2>Select Issue Category</h2>
                <div class="category-grid">
                    <?php foreach ($categories as $index => $category): ?>
                        <label class="category-card">
                            <input type="radio" name="issue_type" value="<?php echo e($category); ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                            <span class="category-icon"></span>
                            <strong><?php echo e($category); ?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card">
                <h2>Incident Details</h2>
                <div class="two-cols-fields">
                    <label>Report Title<input class="form-control" type="text" name="title" value="Broken utility line near main road"></label>
                    <label>Priority
                        <select class="form-control" name="priority"><option>Medium</option><option>High</option><option>Low</option></select>
                    </label>
                </div>
                <label>Description<textarea class="form-control" name="description" rows="5">Describe what happened, when it started, and who may be affected.</textarea></label>
            </section>

            <section class="card">
                <h2>Location Information</h2>
                <div class="location-box">
                    <div>
                        <h3>Map Preview</h3>
                        <p>Use the nearest street, landmark, or purok for faster response.</p>
                    </div>
                    <div class="mini-map"></div>
                </div>
                <label>Nearest Location<input class="form-control" type="text" name="location" value="<?php echo e($barangay['name']); ?> main road"></label>
            </section>

            <section class="card">
                <h2>Photo Evidence</h2>
                <label class="upload-box">
                    <span class="upload-icon"></span>
                    <strong>Upload supporting photo</strong>
                    <small>Prototype only. No file is saved.</small>
                    <input type="file" name="photo">
                </label>
            </section>

            <div class="button-row">
                <button class="btn btn-outline" type="button">Save Draft</button>
                <a class="btn btn-outline" href="index.php?page=resident-dashboard">Cancel</a>
                <button class="btn btn-primary" type="submit" name="new_report">Review & Submit</button>
            </div>
        </form>
    </div>

    <aside class="grid">
        <article class="quick-card">
            <h2>Service Guarantees</h2>
            <p>Reports are logged with a ticket ID and routed to barangay staff for review.</p>
            <div class="quick-list">
                <a>Barangay scoped</a>
                <a>Resident linked</a>
                <a>Status timeline ready</a>
            </div>
        </article>
        <article class="card">
            <h2>Tips for Reporting</h2>
            <div class="soft-list">
                <span>Include a precise landmark.</span>
                <span>Describe visible damage or risk.</span>
                <span>Upload a clear photo when available.</span>
            </div>
        </article>
        <article class="card">
            <h2>Nearby Active Issues</h2>
            <div class="activity-list">
                <?php foreach ($nearbyReports as $report): ?>
                    <div><strong><?php echo e($report['issue_type']); ?></strong><small><?php echo e($report['location']); ?></small></div>
                <?php endforeach; ?>
            </div>
        </article>
    </aside>
</section>
