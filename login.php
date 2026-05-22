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

                            // Redirect based on role
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(91,43,224,0.15);
            border: none;
            padding: 10px;
            width: 100%;
            max-width: 450px;
        }
        .login-card .card-body { padding: 40px; }
        .login-title {
            font-weight: 700;
            color: #5b2be0;
            margin-bottom: 25px;
        }
        .btn-login {
            background: linear-gradient(90deg, #5b2be0, #7b68ee);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
        }
        .btn-login:hover { background: #7b68ee; color: white; }
        .form-control:focus { border-color: #7b68ee; box-shadow: 0 0 0 0.2rem rgba(91,43,224,0.2); }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="login-wrapper">
    <div class="login-card card">
        <div class="card-body">
            <h2 class="login-title text-center">Welcome Back</h2>
            <p class="text-center text-muted mb-4">Login to your CampusHub account</p>

            <?php if (!empty($login_err)): ?>
                <div class="alert alert-danger"><?php echo $login_err; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label class="form-label fw-500">Email Address</label>
                    <input type="email" name="email"
                           class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($email); ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password"
                           class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                           placeholder="••••••••">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn-login btn">Login</button>
                </div>
                <p class="text-center text-muted">Don't have an account? <a href="register.php" style="color:#5b2be0;">Sign up here</a></p>
            </form>

            <!-- Demo hint for lecturer -->
            <div class="alert alert-info mt-3" style="font-size:13px;">
                <strong>Demo accounts:</strong><br>
                Admin: admin@campushub.com<br>
                Customer: john.smith@example.com
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>