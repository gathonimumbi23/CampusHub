<?php
// ========================================
// AUTH CONTROLLER - COMPLETE
// ========================================

require_once __DIR__ . '/../models/User.php';

class AuthController {
    
    // ========================================
    // REGISTRATION
    // ========================================
    
    public function register() {
        // Guest middleware
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        
        include __DIR__ . '/../views/auth/register.php';
    }
    
    public function store() {
        // Guest middleware
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        requireCsrfToken();
        
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $admissionNumber = strtoupper(trim($_POST['admission_number'] ?? ''));
        $role = $_POST['role'] ?? 'customer';
        if (!in_array($role, ['customer', 'seller'])) {
            $role = 'customer';
        }
        
        // Validate
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }
        
        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email)) {
            $errors[] = 'School admission email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif (!str_ends_with(strtolower($email), '@mku.ac.ke')) {
            $errors[] = 'Email must be a valid MKU admission email ending in @mku.ac.ke';
        }
        
        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9+\s\-]{7,15}$/', $phone)) {
            $errors[] = 'Invalid phone number format';
        }
        
        if (empty($admissionNumber)) {
            $errors[] = 'Admission number is required';
        } elseif (!preg_match('/^[A-Z]{2,6}\/[0-9]{1,6}\/[0-9]{4}$/', $admissionNumber)) {
            $errors[] = 'Admission number must be in the format PROGRAM/NUMBER/YEAR, e.g. BIT/1234/2023';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
        
        $userModel = new User();
        
        // Check if email exists
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email already registered';
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
        
        // Check if username exists
        if ($userModel->findByUsername($username)) {
            $_SESSION['error'] = 'Username already taken';
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
        
        // Create user
        $userId = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'phone' => $phone,
            'admission_number' => $admissionNumber,
            'role' => $role
        ]);
        
        if ($userId) {
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: ' . BASE_URL . 'register');
            exit;
        }
    }
    
    // ========================================
    // LOGIN
    // ========================================
    
    public function login() {
        // Guest middleware
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        
        include __DIR__ . '/../views/auth/login.php';
    }
    
    public function authenticate() {
        // Guest middleware
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        $userModel = new User();
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $user = $userModel->login($email, $password, $remember);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_role'] = $user['role'] ?? 'customer';
            $_SESSION['success_flash'] = 'Welcome back, ' . $user['username'] . '!';
            
            // Redirect to intended page, or role-based default
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            } elseif ($_SESSION['user_role'] === 'seller') {
                $redirect = BASE_URL . 'seller/dashboard';
            } else {
                $redirect = BASE_URL;
            }
            header('Location: ' . $redirect);
            exit;
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
    
    // ========================================
    // LOGOUT
    // ========================================
    
    public function logout() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::check();
        
        $userModel = new User();
        $userModel->logout($_SESSION['user_id'] ?? null);
        
        header('Location: ' . BASE_URL);
        exit;
    }
    
    // ========================================
    // PROFILE
    // ========================================
    
    public function profile() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::check();
        
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);
        $orders = $userModel->getOrders($_SESSION['user_id']);
        $addresses = $userModel->getAddresses($_SESSION['user_id']);
        
        include __DIR__ . '/../views/auth/profile.php';
    }
    
    public function updateProfile() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::check();
        requireCsrfToken();
        
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $userModel = new User();
        
        // Validate email
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Invalid email format';
                header('Location: ' . BASE_URL . 'profile');
                exit;
            }
            
            // Check if email is taken by another user
            $existing = $userModel->findByEmail($email);
            if ($existing && $existing['id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = 'Email already taken';
                header('Location: ' . BASE_URL . 'profile');
                exit;
            }
        }
        
        $data = [];
        if (!empty($fullName)) {
            $data['full_name'] = $fullName;
        }
        if (!empty($email)) {
            $data['email'] = $email;
        }
        
        if (!empty($data)) {
            $userModel->update($_SESSION['user_id'], $data);
            $_SESSION['user_name'] = $fullName;
            $_SESSION['success'] = 'Profile updated successfully';
        }
        
        header('Location: ' . BASE_URL . 'profile');
        exit;
    }
    
    public function changePassword() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::check();
        requireCsrfToken();
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }
        
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);
        
        if (!$userModel->verifyPassword($user['password'], $currentPassword)) {
            $_SESSION['error'] = 'Current password is incorrect';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }
        
        $userModel->updatePassword($_SESSION['user_id'], $newPassword);
        $_SESSION['success'] = 'Password changed successfully';
        
        header('Location: ' . BASE_URL . 'profile');
        exit;
    }
    
    // ========================================
    // PASSWORD RESET
    // ========================================
    
    public function forgotPassword() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        
        include __DIR__ . '/../views/auth/forgot-password.php';
    }
    
    public function sendResetLink() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        requireCsrfToken();
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }
        
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if (!$user) {
            $_SESSION['error'] = 'No account found with this email';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }
        
        // Create reset token
        $userModel->createPasswordReset($email);
        $_SESSION['success'] = 'Password reset link sent to your email';
        
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    public function resetPassword($token) {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        
        $userModel = new User();
        $reset = $userModel->getPasswordReset($token);
        
        if (!$reset) {
            $_SESSION['error'] = 'Invalid or expired reset token';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }
        
        $_SESSION['reset_email'] = $reset['email'];
        include __DIR__ . '/../views/auth/reset-password.php';
    }
    
    public function updateResetPassword() {
        require_once __DIR__ . '/../middleware/Auth.php';
        AuthMiddleware::guest();
        requireCsrfToken();
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: ' . BASE_URL . 'reset-password/' . $token);
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . 'reset-password/' . $token);
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters';
            header('Location: ' . BASE_URL . 'reset-password/' . $token);
            exit;
        }
        
        $userModel = new User();
        $reset = $userModel->getPasswordReset($token);
        
        if (!$reset) {
            $_SESSION['error'] = 'Invalid or expired reset token';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }
        
        $userModel->resetPassword($reset['email'], $password);
        $_SESSION['success'] = 'Password reset successfully! Please login.';
        
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
