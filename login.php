<?php
require_once 'includes/config.php';

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT user_id, full_name, email, password, role FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $full_name, $email, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"]       = $id;
                            $_SESSION["email"]    = $email;
                            $_SESSION["role"]     = $role;
                            $_SESSION["name"]     = $full_name;
                            if ($role === 'Admin') {
                                header("location: dashboard.php");
                            } else {
                                header("location: index.php");
                            }
                            exit;
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth-wrapper">

    <!-- ===== LEFT SIDE ===== -->
    <div class="auth-left">

        <!-- Brand -->
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="bi bi-bag-heart-fill"></i>
            </div>
            <span class="auth-brand-name">CampusHub</span>
        </div>

        <!-- Headline -->
        <h1 class="auth-headline">
            Buy, Sell and Support<br>
            <span>Student Businesses</span>
        </h1>

        <!-- Illustration box -->
        <div class="auth-illustration">

            <!-- Floating badge top -->
            <div class="float-badge float-badge-top">
                <div class="badge-icon purple">
                    <i class="bi bi-tag-fill" style="color:#5b2be0;font-size:12px;"></i>
                </div>
                <div>
                    <div class="badge-value">1,240+</div>
                    <div class="badge-label">Active Listings</div>
                </div>
            </div>

            <!-- Illustration placeholder with emoji scene -->
            <div class="illus-placeholder">
                <div style="text-align:center;">
                    <div style="font-size:70px;">🛍️</div>
                    <div style="font-size:30px;margin-top:5px;">👩🏽‍💼 🧑🏽‍💻 👩🏾‍🎨</div>
                </div>
            </div>

            <!-- Floating badge bottom -->
            <div class="float-badge float-badge-bottom">
                <div class="badge-icon green">
                    <i class="bi bi-people-fill" style="color:#4caf50;font-size:12px;"></i>
                </div>
                <div>
                    <div class="badge-value">5k+</div>
                    <div class="badge-label">MKU Students</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===== RIGHT SIDE ===== -->
    <div class="auth-right">
        <div class="auth-form-box">

            <h2 class="auth-form-title">Welcome Back!</h2>
            <p class="auth-form-subtitle">Login to manage your listings and messages.</p>

            <!-- Error alert -->
            <?php if (!empty($login_err)): ?>
            <div class="auth-alert">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo $login_err; ?>
            </div>
            <?php endif; ?>

            

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <!-- Email -->
                <div class="auth-field">
                    <label>Email Address</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email"
                               placeholder="bit202012345@mku.ac.ke"
                               class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <?php if(!empty($email_err)): ?>
                    <span class="invalid-msg"><?php echo $email_err; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="auth-field">
                    <label>Password</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password"
                               placeholder="••••••••"
                               class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    </div>
                    <?php if(!empty($password_err)): ?>
                    <span class="invalid-msg"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Forgot password -->
                <div class="auth-link" style="text-align:right; margin-top:-10px; margin-bottom:18px;">
                    <a href="#">Forgot Password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-auth">Login</button>

            </form>

            <!-- Divider -->
            <div style="text-align:center; color:#ddd; margin: 5px 0; font-size:13px;">— or —</div>

            <!-- Sign up -->
            <a href="register.php" class="btn-auth-outline">New here? Sign Up</a>

            <!-- Footer links -->
            <div class="auth-footer-links">
                <a href="#">Safety Tips</a>
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">Support</a>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>