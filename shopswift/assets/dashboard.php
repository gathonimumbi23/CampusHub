<?php
$page_title = 'Seller Dashboard';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<section class="seller-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Hello, Alex Merchant</h1>
            <p>You've had a strong start today. Your sales are up by <strong>12%</strong> compared to yesterday.</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Revenue</span>
                    <span class="stat-value">$12,450.80</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Orders Today</span>
                    <span class="stat-value">42</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-value">07</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Fulfillment Rate</span>
                    <span class="stat-value">86%</span>
                    <span class="stat-sub">Needs immediate attention</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <div class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Add Product</h3>
                <p>List a new item in your inventory</p>
                <span class="action-badge">+12.5%</span>
            </div>
            <div class="action-card">
                <i class="fas fa-edit"></i>
                <h3>Manage Inventory</h3>
                <p>Update stock levels and pricing</p>
                <span class="action-badge">+8</span>
            </div>
            <div class="action-card">
                <i class="fas fa-chart-bar"></i>
                <h3>View Reports</h3>
                <p>Deep dive into your performance</p>
                <span class="action-badge">+8</span>
            </div>
        </div>
    </div>
</section>

<style>
.dashboard-header {
    padding: var(--space-6) 0;
}
.dashboard-header h1 {
    font-family: var(--font-family-secondary);
    margin-bottom: var(--space-2);
}
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
    margin: var(--space-6) 0;
}
.stat-card {
    background: var(--bg-card);
    padding: var(--space-5);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: var(--space-4);
}
.stat-icon {
    font-size: var(--font-3xl);
    color: var(--color-secondary);
}
.stat-label {
    display: block;
    color: var(--text-muted);
    font-size: var(--font-sm);
}
.stat-value {
    display: block;
    font-size: var(--font-2xl);
    font-weight: var(--weight-bold);
    color: var(--text-primary);
}
.stat-sub {
    font-size: var(--font-xs);
    color: var(--color-error);
}
.dashboard-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
    margin: var(--space-6) 0;
}
.action-card {
    background: var(--bg-card);
    padding: var(--space-5);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    text-align: center;
    transition: all var(--transition-normal);
    cursor: pointer;
}
.action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.action-card i {
    font-size: var(--font-4xl);
    color: var(--color-secondary);
}
.action-card h3 {
    margin: var(--space-2) 0;
}
.action-card p {
    font-size: var(--font-sm);
    color: var(--text-muted);
}
.action-badge {
    display: inline-block;
    margin-top: var(--space-2);
    padding: var(--space-1) var(--space-3);
    background: var(--color-success);
    color: white;
    border-radius: var(--radius-full);
    font-size: var(--font-xs);
}
</style>

<?php include '../includes/footer.php'; ?>