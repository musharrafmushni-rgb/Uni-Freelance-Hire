<?php
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container" style="max-width: 1000px;">
        <h1>Connecting Sri Lankan IT Talent with Projects</h1>
        <p>The premier freelance marketplace for undergraduate IT students and clients. Build your portfolio while delivering high-quality solutions with elite student talent.</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="hero-btns">
                <a href="register.php?role=student" class="btn btn-primary" style="padding: 16px 32px; font-size: 1.1rem;">Join as a Student</a>
                <a href="register.php?role=client" class="btn btn-secondary" style="padding: 16px 32px; font-size: 1.1rem; background: var(--white); color: var(--primary-color); border: 1px solid var(--primary-color);">Hire Student Talent</a>
            </div>
        <?php else: ?>
            <div class="card glass-panel" style="display: inline-block; padding: 20px 40px; margin-top: 20px;">
                <p style="margin-bottom: 20px; font-size: 1.1rem;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>!</p>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <h2>500+</h2>
                <p>IT Students</p>
            </div>
            <div class="stat-item">
                <h2>150+</h2>
                <p>Active Projects</p>
            </div>
            <div class="stat-item">
                <h2>98%</h2>
                <p>Satisfaction Rate</p>
            </div>
            <div class="stat-item">
                <h2>LKR 2M+</h2>
                <p>Payments Processed</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="text-center">Built for the Future of Tech</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Premium Projects</h3>
                <p>Access hand-picked development and design opportunities from leading local startups and enterprises.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3>Elite Talent</h3>
                <p>Every student is verified by their university credentials and maintained through a transparent rating system.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Secure Escrow</h3>
                <p>Our simulated escrow system ensures funds are protected until milestones are successfully completed.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Growth Tracking</h3>
                <p>Students build a verifiable technical portfolio and reputation score that matters to future employers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer class="landing-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 style="color: var(--white); margin-bottom: 20px;">UniFreelance Hire</h3>
                <p>Elevating the Sri Lankan undergraduate ecosystem by bridging the gap between academia and the freelance industry.</p>
            </div>
            <div class="footer-col">
                <h4>Solutions</h4>
                <ul>
                    <li><a href="jobs.php">Find Work</a></li>
                    <li><a href="post_job.php">Post a Project</a></li>
                    <li><a href="wallet.php">Secure Wallet</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Cookie Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Connect</h4>
                <ul>
                    <li><a href="#">Support Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">LinkedIn</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> UniFreelance Hire. All Rights Reserved. Designed for Excellence.</p>
        </div>
    </div>
</footer>

<?php include 'includes/footer.php'; ?>
