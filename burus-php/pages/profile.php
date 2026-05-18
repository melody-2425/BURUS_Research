<?php $user = getCurrentUser(); $barangay = getCurrentBarangay(); ?>
<section class="profile-layout">
    <div class="panel profile-card">
        <span class="avatar large"><?php echo e(substr($user['name'], 0, 1)); ?></span>
        <h2><?php echo e($user['name']); ?></h2>
        <p><?php echo e(ucfirst($user['role'])); ?> of <?php echo e($barangay['name']); ?></p>
        <a class="btn btn-light" href="logout.php">Logout</a>
    </div>
    <div class="panel">
        <h2>Account Settings</h2>
        <form class="stack-form two-cols">
            <label>Full Name<input type="text" value="<?php echo e($user['name']); ?>"></label>
            <label>Email<input type="email" value="<?php echo e($user['email']); ?>"></label>
            <label>Contact<input type="text" value="<?php echo e($user['contact']); ?>"></label>
            <label>Barangay<input type="text" value="<?php echo e($barangay['name']); ?>"></label>
            <label class="span-2">Address<input type="text" value="<?php echo e($user['address']); ?>"></label>
            <button class="btn btn-primary span-2" type="button">Save Profile</button>
        </form>
    </div>
</section>
