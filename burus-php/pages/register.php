<main class="auth-page">
    <section class="auth-card wide">
        <a class="brand centered" href="index.php?page=landing">
            <span class="brand-mark">B</span>
            <span><strong>BURUS</strong><small>Resident Registration</small></span>
        </a>
        <h1>Create resident account</h1>
        <p>This prototype saves shared accounts to server-side JSON files.</p>
        <form method="post" class="stack-form two-cols">
            <label>Full Name<input type="text" name="name" placeholder="Enter your full name" required></label>
            <label>Email Address<input type="email" name="email" placeholder="Enter your email address" required></label>
            <label>Contact Number<input type="text" name="contact" placeholder="Enter your contact number" required></label>
            <label>Barangay
                <select name="barangay_id">
                    <?php global $barangays; foreach ($barangays as $barangay): ?>
                        <option value="<?php echo e($barangay['id']); ?>"><?php echo e($barangay['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="span-2">Complete Address<input type="text" name="address" placeholder="Enter your complete address" required></label>
            <label>Password<input type="password" name="password" placeholder="Create a password" required></label>
            <label>Role
                <select name="role">
                    <option>Resident</option>
                    <option>Official</option>
                    <option>Admin</option>
                </select>
            </label>
            <button class="btn btn-primary full span-2" type="submit" name="register">Register</button>
        </form>
        <p class="auth-switch">Already registered? <a href="index.php?page=login">Login</a></p>
    </section>
</main>
