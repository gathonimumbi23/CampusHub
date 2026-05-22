<?php
require_once 'includes/config.php';

$full_name = $student_id = $email = $password = $confirm_password = $role = $phone = "";
$full_name_err = $student_id_err = $email_err = $password_err = $confirm_password_err = $role_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Full name
    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Student ID validation
    if(empty(trim($_POST["student_id"]))){
        $student_id_err = "Please enter your student ID.";
    } else {
        $student_id = strtoupper(trim($_POST["student_id"]));
        // MKU admission format: letters/year/numbers e.g. BSCCS/2024/56764
        if(!preg_match('/^[A-Z]+\/[0-9]{4}\/[0-9]+$/', $student_id)){
            $student_id_err = "Invalid student ID format. Example: BSCCS/2024/56764";
        } else {
            // Check if student ID already registered
            $sql_check = "SELECT user_id FROM users WHERE student_id = ?";
            if($stmt = mysqli_prepare($link, $sql_check)){
                mysqli_stmt_bind_param($stmt, "s", $student_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $student_id_err = "This student ID is already registered.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Email validation - must match MKU format derived from student ID
if(empty(trim($_POST["email"]))){
    $email_err = "Please enter your MKU student email.";
} else {
    $email = strtolower(trim($_POST["email"]));

    // Generate expected email from student ID (remove slashes, lowercase, add @mku.ac.ke)
    $expected_email = "";
    if(!empty($student_id) && empty($student_id_err)){
        $expected_email = strtolower(str_replace('/', '', $student_id)) . '@mku.ac.ke';
    }

    // Check email ends with @mku.ac.ke
    if(!preg_match('/^[a-z0-9]+@mku\.ac\.ke$/', $email)){
        $email_err = "Only MKU emails accepted. Format: " . ($expected_email ?: "bsccs202456764@mku.ac.ke");
    // Check email matches the student ID
    } elseif(!empty($expected_email) && $email !== $expected_email){
        $email_err = "Your email must match your Student ID. Expected: " . $expected_email;
    } else {
        // Check if email already taken
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

    // Password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passwords did not match.";
        }
    }

    // Role
    if(empty($_POST["role"])){
        $role_err = "Please select a role.";
    } else {
        $role = $_POST["role"];
    }

    $phone = trim($_POST["phone"]);

    // Insert if no errors
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
                header("location: login.php");
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-wrapper {
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(91,43,224,0.15);
            border: none;
            width: 100%;
            max-width: 580px;
        }
        .register-card .card-body { padding: 40px; }
        .register-title {
            font-weight: 700;
            color: #5b2be0;
            margin-bottom: 5px;
        }
        .btn-register {
            background: linear-gradient(90deg, #5b2be0, #7b68ee);
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        .btn-register:hover { background: #7b68ee; color: white; }
        .form-control:focus, .form-select:focus {
            border-color: #7b68ee;
            box-shadow: 0 0 0 0.2rem rgba(91,43,224,0.2);
        }
        .role-option {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: .3s;
            text-align: center;
            display: block;
        }
        .role-option:hover { border-color: #5b2be0; background: #f5f0ff; }
        .role-option input { display: none; }
        .role-option.selected { border-color: #5b2be0; background: #f0ebff; }
        .mku-badge {
            background: #ede9fe;
            color: #5b2be0;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="register-wrapper">
    <div class="register-card card">
        <div class="card-body">
            <h2 class="register-title text-center">🎓 Join CampusHub</h2>
            <p class="text-center text-muted mb-2">Mount Kenya University Students Only</p>
            <div class="text-center">
                <span class="mku-badge">🔒 Verified MKU Campus Marketplace</span>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="full_name"
                           class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>"
                           placeholder="Your full name"
                           value="<?php echo htmlspecialchars($full_name); ?>">
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Student ID / Admission Number</label>
                    <input type="text" name="student_id"
                           class="form-control <?php echo (!empty($student_id_err)) ? 'is-invalid' : ''; ?>"
                           placeholder="e.g. BSCCS/2024/56764"
                           value="<?php echo htmlspecialchars($student_id); ?>">
                    <span class="invalid-feedback"><?php echo $student_id_err; ?></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">MKU Student Email</label>
                    <input type="email" name="email"
                           class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                           placeholder="you@students.mku.ac.ke"
                           value="<?php echo htmlspecialchars($email); ?>">
                    <div class="form-text">Only @mku.ac.ke or @students.mku.ac.ke emails accepted</div>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           placeholder="07XXXXXXXX"
                           value="<?php echo htmlspecialchars($phone); ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password"
                               class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                               placeholder="Min. 6 characters">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="confirm_password"
                               class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                               placeholder="Repeat password">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">I want to:</label>
                    <?php if(!empty($role_err)): ?>
                        <div class="text-danger small mb-2"><?php echo $role_err; ?></div>
                    <?php endif; ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="role-option <?php echo ($role == 'Customer') ? 'selected' : ''; ?>">
                                <input type="radio" name="role" value="Customer" <?php echo ($role == 'Customer') ? 'checked' : ''; ?>>
                                <div style="font-size:28px">🛒</div>
                                <div class="fw-semibold">Shop</div>
                                <small class="text-muted">Buy products and services</small>
                            </label>
                        </div>
                        <div class="col-6">
                            <label class="role-option <?php echo ($role == 'Vendor') ? 'selected' : ''; ?>">
                                <input type="radio" name="role" value="Vendor" <?php echo ($role == 'Vendor') ? 'checked' : ''; ?>>
                                <div style="font-size:28px">🏪</div>
                                <div class="fw-semibold">Sell</div>
                                <small class="text-muted">List your products and services</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn-register btn">Create Account</button>
                    <button type="reset" class="btn btn-outline-secondary" style="border-radius:30px; padding:12px 25px;">Reset</button>
                </div>

                <p class="text-center text-muted mt-3">Already have an account? <a href="login.php" style="color:#5b2be0;">Login here</a></p>

            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.role-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.role-option').forEach(el => el.classList.remove('selected'));
        this.closest('.role-option').classList.add('selected');
    });
});
</script>
</body>
</html>