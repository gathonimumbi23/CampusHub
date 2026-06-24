<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ShopSwift - Your premier fashion destination for modern style">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ShopSwift' : 'ShopSwift - Fashion Marketplace'; ?></title>
    <script>
        window.ShopSwift = {
            baseUrl: <?php echo json_encode(BASE_URL); ?>,
            csrfToken: <?php echo json_encode(csrfToken()); ?>
        };
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Design System & Main Styles -->
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/design-system.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/components.css">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>css/responsive.css">
</head>
<body>
