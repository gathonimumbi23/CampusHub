<?php
require_once 'includes/config.php';

$full_name = $student_id = $email = $password = $confirm_password = $role = $phone = "";
$full_name_err = $student_id_err = $email_err = $password_err = $confirm_password_err = $role_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    if(empty(trim($_POST["student_id"]))){
        $student_id_err = "Please enter your admission number.";
    } else {
        $student_id = strtoupper(trim($_POST["student_id"]));
        if(!preg_match('/^[A-Z]+\/[0-9]{4}\/[0-9]+$/', $student_id)){
            $student_id_err = "Invalid format. Example: BIT/2020/12345";
        } else {
            $sql_check = "SELECT user_id FROM users WHERE student_id = ?";
            if($stmt = mysqli_prepare($link, $sql_check)){
                mysqli_stmt_bind_param($stmt, "s", $student_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $student_id_err = "This admission number is already registered.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your MKU email.";
    } else {
        $email = strtolower(trim($_POST["email"]));
        $expected_email = "";
        if(!empty($student_id) && empty($student_id_err)){
            $expected_email = strtolower(str_replace('/', '', $student_id)) . '@mku.ac.ke';
        }
        if(!preg_match('/^[a-z0-9]+@mku\.ac\.ke$/', $email)){
            $email_err = "Only @mku.ac.ke emails accepted. Expected: " . ($expected_email ?: "bit202012345@mku.ac.ke");
        } elseif(!empty($expected_email) && $email !== $expected_email){
            $email_err = "Email must match your admission number. Expected: " . $expected_email;
        } else {
            $sql = "SELECT user_id FROM users WHERE email = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if(empty(trim($_POST["phone"]))){
        $phone = "";
    } else {
        $phone = trim($_POST["phone"]);
    }

    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passwords do not match.";
        }
    }

    if(empty($_POST["role"])){
        $role_err = "Please select a role.";
    } else {
        $role = $_POST["role"];
    }

    if(empty($full_name_err) && empty($student_id_err) && empty($email_err) &&
       empty($password_err) && empty($confirm_password_err) && empty($role_err)){
        $sql = "INSERT INTO users (full_name, student_id, email, password, role, phone) VALUES (?, ?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssssss", $p_name, $p_sid, $p_email, $p_pass, $p_role, $p_phone);
            $p_name  = $full_name;
            $p_sid   = $student_id;
            $p_email = $email;
            $p_pass  = password_hash($password, PASSWORD_DEFAULT);
            $p_role  = $role;
            $p_phone = $phone;
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php?registered=1");
                exit;
            } else {
                echo "Something went wrong. Please try again.";
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
    <title>Register - CampusHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        /* Scrollable right side for longer form */
       .auth-right {
    overflow-y: auto;
    padding: 40px 50px;
}

body {
    overflow-y: auto;
    height: auto;
}

.auth-wrapper {
    min-height: 100vh;
    height: auto;
}
        /* Password strength bar */
        .pwd-strength-wrap {
            margin-top: 6px;
            height: 4px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
        }
        .pwd-strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 10px;
            transition: 0.4s;
        }
        .pwd-strength-label {
            font-size: 11px;
            color: gray;
            margin-top: 3px;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <!-- ===== LEFT SIDE ===== -->
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="bi bi-bag-heart-fill"></i>
            </div>
            <span class="auth-brand-name">CampusHub</span>
        </div>

        <h1 class="auth-headline">
            Join the MKU<br>
            <span>Student Marketplace</span>
        </h1>

        <div class="auth-illustration">

            <div class="float-badge float-badge-top">
                <div class="badge-icon purple">
                    <i class="bi bi-shield-check" style="color:#5b2be0;font-size:12px;"></i>
                </div>
                <div>
                    <div class="badge-value">MKU Verified</div>
                    <div class="badge-label">Students Only</div>
                </div>
            </div>

            <div class="illus-placeholder">
                <div style="text-align:center;">
                    <div style="font-size:65px;">🎓</div>
                    <div style="font-size:28px;margin-top:8px;">🛒 🏪 💼</div>
                    <div style="font-size:13px;color:#5b2be0;font-weight:600;margin-top:10px;">Buy • Sell • Grow</div>
                </div>
            </div>

            <div class="float-badge float-badge-bottom">
                <div class="badge-icon green">
                    <i class="bi bi-people-fill" style="color:#4caf50;font-size:12px;"></i>
                </div>
                <div>
                    <div class="badge-value">Free</div>
                    <div class="badge-label">Always free to join</div>
                </div>
            </div>

        </div>

        <!-- Steps -->
        <div style="margin-top:30px;width:100%;max-width:380px;">
            <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;">
                <div style="width:28px;height:28px;background:#5b2be0;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">1</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#1a1a2e;">Use your MKU admission number</div>
                    <div style="font-size:12px;color:gray;">e.g. BIT/2020/12345</div>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;">
                <div style="width:28px;height:28px;background:#5b2be0;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">2</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#1a1a2e;">Your MKU email is auto-generated</div>
                    <div style="font-size:12px;color:gray;">bit202012345@mku.ac.ke</div>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <div style="width:28px;height:28px;background:#5b2be0;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">3</div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#1a1a2e;">Start buying or selling!</div>
                    <div style="font-size:12px;color:gray;">Campus marketplace at your fingertips</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT SIDE ===== -->
    <div class="auth-right">
        <div class="auth-form-box">

            <h2 class="auth-form-title">Create Account</h2>
            <p class="auth-form-subtitle">Join thousands of MKU students already on CampusHub.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registerForm">

                <!-- Full Name -->
                 <div id="name-preview" style="font-size:13px;color:#5b2be0;font-weight:600;margin-top:4px;min-height:18px;"></div>
                <div class="auth-field">
                    <label>Full Name</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-person"></i>
                        <input type="text" name="full_name"
                               id="full_name"
                               placeholder="e.g. Rose Brenda Gathoni"
                               class="<?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($full_name); ?>">
                    </div>
                    <?php if(!empty($full_name_err)): ?>
                    <span class="invalid-msg"><?php echo $full_name_err; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Admission Number -->
                <div class="auth-field">
                    <label>Admission Number</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-credit-card"></i>
                        <input type="text" name="student_id"
                               id="admission_number"
                               placeholder="e.g. BIT/2020/12345"
                               class="<?php echo (!empty($student_id_err)) ? 'is-invalid' : ''; ?>"
                               value="<?php echo htmlspecialchars($student_id); ?>">
                    </div>
                    <?php if(!empty($student_id_err)): ?>
                    <span class="invalid-msg"><?php echo $student_id_err; ?></span>
                    <?php endif; ?>
                    
                </div>

                <!-- MKU Email -->
<div class="auth-field">
    <label>MKU Student Email</label>
    <div class="auth-input-wrap">
        <i class="bi bi-envelope"></i>
        <input type="email" name="email"
               id="mku_email"
               placeholder="Auto-fills when you type admission number"
               class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
               value="<?php echo htmlspecialchars($email); ?>">
    </div>
    <span id="email-status" style="font-size:12px;margin-top:4px;display:block;"></span>
    <?php if(!empty($email_err)): ?>
    <span class="invalid-msg"><?php echo $email_err; ?></span>
    <?php endif; ?>
</div>

                <!-- Phone -->
                <div class="auth-field">
                    <label>Phone Number</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="phone"
                               placeholder="07XXXXXXXX"
                               value="<?php echo htmlspecialchars($phone); ?>">
                    </div>
                </div>

                <!-- Password -->
<div class="auth-field">
    <label>Password</label>
    <div class="auth-input-wrap">
        <i class="bi bi-lock"></i>
        <input type="password" name="password"
               id="password"
               placeholder="Min. 6 characters"
               class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
        <button type="button" class="pwd-toggle"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#aaa;cursor:pointer;padding:0;">
            <i class="bi bi-eye"></i>
        </button>
    </div>
    <?php if(!empty($password_err)): ?>
    <span class="invalid-msg"><?php echo $password_err; ?></span>
    <?php endif; ?>
    <div class="pwd-strength-wrap" style="margin-top:6px;">
        <div class="pwd-strength-bar" id="pwd-strength"></div>
    </div>
    <span class="pwd-strength-label" id="pwd-label"></span>
    <!-- Requirements checklist -->
    <div id="pwd-requirements" style="margin-top:8px;display:flex;flex-direction:column;gap:4px;font-size:12px;"></div>
</div>

                <!-- Confirm Password -->
                <div class="auth-field">
                    <label>Confirm Password</label>
                    <div class="auth-input-wrap">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="confirm_password"
                               placeholder="Repeat your password"
                               class="<?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    </div>
                    <?php if(!empty($confirm_password_err)): ?>
                    <span class="invalid-msg"><?php echo $confirm_password_err; ?></span>
                    <?php endif; ?>
                </div>

                <!-- Role Selection -->
                <div class="auth-field">
                    <label>I want to:</label>
                    <?php if(!empty($role_err)): ?>
                    <span class="invalid-msg"><?php echo $role_err; ?></span>
                    <?php endif; ?>
                    <div class="role-grid">
                        <div class="role-option <?php echo ($role=='Customer')?'selected':''; ?>">
                            <input type="radio" name="role" value="Customer" <?php echo ($role=='Customer')?'checked':''; ?>>
                            <div class="role-icon">🛒</div>
                            <div class="role-name">Shop</div>
                            <div class="role-desc">Buy products & services</div>
                        </div>
                        <div class="role-option <?php echo ($role=='Vendor')?'selected':''; ?>">
                            <input type="radio" name="role" value="Vendor" <?php echo ($role=='Vendor')?'checked':''; ?>>
                            <div class="role-icon">🏪</div>
                            <div class="role-name">Sell</div>
                            <div class="role-desc">List your products & services</div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-auth">
                    Create My Account
                </button>

            </form>

            <!-- Divider -->
            <div style="text-align:center;color:#ddd;margin:8px 0;font-size:13px;">— or —</div>

            <!-- Login link -->
            <a href="login.php" class="btn-auth-outline">Already have an account? Login</a>

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