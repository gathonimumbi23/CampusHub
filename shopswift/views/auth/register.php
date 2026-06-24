<?php
$page_title = 'Register';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="container" style="max-width:480px;">
        <div class="auth-card">
        <div style="text-align:center;"><span class="eyebrow-badge">MKU PLATFORM HUB</span></div>
        <h1 style="text-align:center;margin-bottom:var(--space-4);color:var(--text-light);">Create Account</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background:#fee;color:#c62828;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['errors'])): ?>
            <div style="background:#fee;color:#c62828;padding:var(--space-3);border-radius:var(--radius-md);margin-bottom:var(--space-4);">
                <ul style="margin-left:20px;">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <form action="<?php echo BASE_URL; ?>register" method="POST">
            <?php echo csrfField(); ?>
            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="username">Username</label>
                <input type="text" id="username" name="username" class="input" required 
                       value="<?php echo htmlspecialchars($_SESSION['old']['username'] ?? ''); ?>">
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="input" required
                       value="<?php echo htmlspecialchars($_SESSION['old']['full_name'] ?? ''); ?>">
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="admission_number">Admission Number</label>
                <input type="text" id="admission_number" name="admission_number" class="input" required
                       placeholder="BIT/1234/2023"
                       value="<?php echo htmlspecialchars($_SESSION['old']['admission_number'] ?? ''); ?>">
                <p style="font-size:var(--font-xs);color:rgba(255,255,255,0.75);margin-top:var(--space-1);">Format: PROGRAM/NUMBER/YEAR, e.g. BIT/1234/2023</p>
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="email">School Admission Email</label>
                <input type="email" id="email" name="email" class="input" required 
                       placeholder="yourname@mku.ac.ke"
                       value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>">
                <p style="font-size:var(--font-xs);color:rgba(255,255,255,0.75);margin-top:var(--space-1);">Must be your official @mku.ac.ke email</p>
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="input" required
                       placeholder="07XXXXXXXX"
                       value="<?php echo htmlspecialchars($_SESSION['old']['phone'] ?? ''); ?>">
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label">I am registering as a:</label>
                <div style="display:flex;gap:var(--space-3);margin-top:var(--space-2);">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:var(--radius-md);padding:var(--space-3);cursor:pointer;color:white;">
                        <input type="radio" name="role" value="customer" checked style="width:auto;">
                        Buyer
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:var(--radius-md);padding:var(--space-3);cursor:pointer;color:white;">
                        <input type="radio" name="role" value="seller" style="width:auto;">
                        Seller
                    </label>
                </div>
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="password">Password</label>
                <input type="password" id="password" name="password" class="input" required>
                <p style="font-size:var(--font-xs);color:rgba(255,255,255,0.75);margin-top:var(--space-1);">Minimum 6 characters</p>
            </div>

            <div style="margin-bottom:var(--space-4);">
                <label class="label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
        </form>
        
        <p style="text-align:center;margin-top:var(--space-4);color:rgba(255,255,255,0.85);font-size:var(--font-sm);">
            Already have an account? <a href="<?php echo BASE_URL; ?>login" style="color:white;font-weight:600;text-decoration:underline;">Login here</a>
        </p>
        
        <?php unset($_SESSION['old']); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>