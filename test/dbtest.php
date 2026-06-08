<?php
$host = 'localhost';
$db   = 'mku_marketplace';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $connected = true;
} catch (\PDOException $e) {
    $connected = false;
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DB Test - MKU Student Marketplace</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #fff; }
        .success { font-size: 22px; font-weight: bold; color: #000; }
        .error   { font-size: 22px; font-weight: bold; color: red; }
    </style>
</head>
<body>
    <?php if ($connected): ?>
        <p class="success">Database Connected Successfully</p>
    <?php else: ?>
        <p class="error">Database Connection Failed: <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</body>
</html>
