<?php
/**
 * MKU Student Marketplace - Product Creation Handler
 * Handle 'Add New Garment' form submissions securely.
 */
session_start();
require_once 'db.php';

// 1. Security Guard: Ensure user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    die("Unauthorized access. Please log in as a seller.");
}

// 2. Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and trim inputs
    $garment_name = trim($_POST['garment_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $seller_id = $_SESSION['user_id'];

    // 3. Validation
    $errors = [];

    if (empty($garment_name)) {
        $errors[] = "Garment name is required.";
    }

    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Please enter a valid positive price.";
    }

    if (empty($size)) {
        $errors[] = "Please select a size.";
    }

    // 4. File Upload Handling
    $image_url = 'assests/images/clothes-A1.jpg'; // Better local fallback from your assets

    if (isset($_FILES['garment_image']) && $_FILES['garment_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['garment_image']['tmp_name'];
        $fileName = $_FILES['garment_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg', 'webp'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            
            // Ensure the uploads directory exists and is absolute for move_uploaded_file
            $uploadFileDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_url = 'uploads/' . $newFileName;
            } else {
                // If move fails, we log it but don't block the product (or we could stay on fallback)
                error_log("Failed to move uploaded file to " . $dest_path);
            }
        }
    }

    // 5. Database Insertion (PDO Prepared Statement)
    if (empty($errors)) {
        try {
            // Note: 'category' is defaulted to 'clothes' and 'size' is appended to 'description' 
            $description = "Size: " . htmlspecialchars($size) . ". This item is part of the MKU campus thrift collection.";
            $category = 'clothes';

            $sql = "INSERT INTO products (title, description, price, category, image_url, seller_id, created_at) 
                    VALUES (:title, :description, :price, :category, :image_url, :seller_id, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'       => $garment_name,
                ':description' => $description,
                ':price'       => $price,
                ':category'    => $category,
                ':image_url'   => $image_url,
                ':seller_id'   => $seller_id
            ]);

            // Success Redirect
            $_SESSION['success_msg'] = "Success! Your garment '{$garment_name}' has been listed.";
            header("Location: seller.php#storefront");
            exit;

        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }

    // Handing Errors (if any)
    if (!empty($errors)) {
        $_SESSION['error_msg'] = implode(" ", $errors);
        header("Location: seller.php#add-garment");
        exit;
    }
} else {
    // Redirect if accessed directly without POST
    header("Location: seller.php");
    exit;
}
?>
