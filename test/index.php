<?php
$currentDateTime = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hello World - MKU Student Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #fff;
        }
        .container { text-align: center; }
        .checkmark {
            background-color: #28a745;
            border-radius: 8px;
            display: inline-block;
            padding: 10px 16px;
            margin-bottom: 12px;
        }
        .checkmark span { color: white; font-size: 28px; font-weight: bold; }
        h1 { color: #28a745; font-size: 36px; margin: 0 0 16px 0; }
        h2 { font-size: 20px; font-weight: bold; color: #000; margin: 0 0 12px 0; }
        p { color: #333; font-size: 15px; margin: 6px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark"><span>&#10003;</span></div>
        <h1>Hello World</h1>
        <h2>Student Marketplace Local Test Successful</h2>
        <p>PHP and Localhost are working correctly.</p>
        <p>Current Date and Time: <?php echo $currentDateTime; ?></p>
    </div>
</body>
</html>
