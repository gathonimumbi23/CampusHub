<?php
$page_title = 'My Profile';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container" style="padding: var(--space-6) 0;">
    <div style="display:grid;grid-template-columns:250px 1fr;gap:var(--space-5);">
        <!-- Sidebar -->
        <div style="background:var(--bg-card);border-radius:var(--radius-lg);border:1px solid var(--border-color);padding:var(--space-4);">
            <div style="text-align:center;padding:var(--space-3);">
                <div style="width:80px;height:80px;border-radius:50%;background:var(--color-secondary);margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:32px;color:white;">
                    <i class="fas fa-user"></i>
                </div>
                <h4 style="margin-top:var(--space-2);"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h4>
                <p style="font-size:var(--font-sm);color:var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <hr style="border-color:var(--border-color);margin:var(--space-3) 0;">
            
            <nav style="display:flex;flex-direction:column;gap:var(--space-1);">
                <a href="<?php echo BASE_URL; ?>profile" style="padding:var(--space-2) var(--space-3);border-radius:var(--radius-md);color:var(--text-primary);text-decoration:none;background:var(--bg-hover);">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="<?php echo BASE_URL; ?>profile/orders" style="padding:var(--space-2) var(--space-3);border-radius:var(--radius-md);color:var(--text-secondary);text-decoration:none;">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
                <a href="<?php echo BASE_URL; ?>profile/addresses" style="padding:var(--space-2) var(--space-3);border-radius:var(--radius-md);color:var(--text-secondary);text-decoration:none;">
                    <i class="fas fa-map-marker-alt"></i> Addresses
                </a>
                <a href="<?php echo BASE_URL; ?>logout" style="padding:var(--space-2) var(--space-3);border-radius:var(--radius-md);color:#e74c3c;text-decoration:none;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <!-- Profile Information -->
            <div style="background:var(--bg-card);border-radius:var(--radius-lg);border:1px solid var(--border-color);padding:var(--space-5);margin-bottom:var(--space-5);">
                <h3 style="margin-bottom:var(--space-4);">Profile Information</h3>
                
                <form action="<?php echo BASE_URL; ?>profile/update" method="POST">
                    <?php echo csrfField(); ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div>
                            <label class="label" for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="input" 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="label" for="username">Username</label>
                            <input type="text" id="username" class="input" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label class="label" for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="input" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top:var(--space-4);">
                        Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div style="background:var(--bg-card);border-radius:var(--radius-lg);border:1px solid var(--border-color);padding:var(--space-5);">
                <h3 style="margin-bottom:var(--space-4);">Change Password</h3>
                
                <form action="<?php echo BASE_URL; ?>profile/change-password" method="POST">
                    <?php echo csrfField(); ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div style="grid-column:1/-1;">
                            <label class="label" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="input" required>
                        </div>
                        <div>
                            <label class="label" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="input" required>
                        </div>
                        <div>
                            <label class="label" for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="input" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary" style="margin-top:var(--space-4);">
                        Change Password
                    </button>
                </form>
            </div>
            
            <!-- Recent Orders -->
            <?php if (!empty($orders)): ?>
                <div style="background:var(--bg-card);border-radius:var(--radius-lg);border:1px solid var(--border-color);padding:var(--space-5);margin-top:var(--space-5);">
                    <h3 style="margin-bottom:var(--space-4);">Recent Orders</h3>
                    
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <th style="padding:var(--space-2);text-align:left;">Order #</th>
                                <th style="padding:var(--space-2);text-align:left;">Date</th>
                                <th style="padding:var(--space-2);text-align:left;">Total</th>
                                <th style="padding:var(--space-2);text-align:left;">Status</th>
                                <th style="padding:var(--space-2);text-align:left;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr style="border-bottom:1px solid var(--border-color);">
                                    <td style="padding:var(--space-2);">#<?php echo $order['order_number']; ?></td>
                                    <td style="padding:var(--space-2);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td style="padding:var(--space-2);font-weight:bold;">KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td style="padding:var(--space-2);">
                                        <span style="padding:2px 10px;border-radius:var(--radius-full);font-size:var(--font-xs);background:<?php echo $order['status'] === 'delivered' ? '#e8f5e9' : ($order['status'] === 'pending' ? '#fff3e0' : '#e3f2fd'); ?>;color:<?php echo $order['status'] === 'delivered' ? '#2e7d32' : ($order['status'] === 'pending' ? '#e65100' : '#0d47a1'); ?>;">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding:var(--space-2);">
                                        <a href="<?php echo BASE_URL; ?>order/<?php echo $order['id']; ?>" style="color:var(--color-secondary);">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (count($orders) >= 10): ?>
                        <a href="<?php echo BASE_URL; ?>orders" style="display:block;text-align:center;margin-top:var(--space-3);">View All Orders</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
