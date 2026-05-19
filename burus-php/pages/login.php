<main class="auth-page">
    <section class="auth-card">
        <a class="brand centered" href="index.php?page=landing">
            <span class="brand-mark">B</span>
            <span><strong>BURUS</strong><small>Secure Access</small></span>
        </a>
        <h1>Welcome back</h1>
        <p>Sign in as a resident, official, or admin to open the correct dashboard.</p>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="flash"><?php echo e($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
        <?php endif; ?>
        <form method="post" class="stack-form">
            <label>Email Address<input type="email" name="email" placeholder="Enter your email address" required></label>
            <label>Password<input type="password" name="password" placeholder="Enter your password" required></label>
            <button class="btn btn-primary full" type="submit" name="login">Login</button>
        </form>
        <div class="demo-accounts">
            <strong>Demo accounts</strong>
            <span>resident@burus.test / 123456</span>
            <span>official@burus.test / 123456</span>
            <span>admin@burus.test / 123456</span>
            <span>labangon@burus.test / 123456</span>
        </div>
        <p class="auth-switch">No account yet? <a href="index.php?page=register">Create account</a></p>
    </section>
</main>
