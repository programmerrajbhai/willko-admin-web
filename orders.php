<?php 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Quick Stats for Header
$total_o = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pending_o = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetch_row()[0];
$revenue_o = $conn->query("SELECT SUM(final_total) FROM bookings WHERE status='completed'")->fetch_row()[0] ?? 0;
?>

<style>
    /* Premium Glass UI */
    .glass-card {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
    }

    /* Input Fields Styling */
    .form-control, .form-select {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 10px;
        padding: 10px 15px;
    }
    .form-control::placeholder { color: #94a3b8; }
    .form-control:focus, .form-select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
    }

    /* Table Styling */
    .table-responsive { overflow-x: auto; }
    .table { color: #e2e8f0; margin-bottom: 0; }
    .table thead th {
        background: rgba(15, 23, 42, 0.9);
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 15px;
    }
    .order-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }
    .order-row:hover {
        background: rgba(56, 189, 248, 0.08); 
        transform: scale(1.002);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        z-index: 10;
        position: relative;
    }
    
    /* Date Box */
    .date-box {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        width: 55px; height: 55px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        text-align: center;
    }
    
    /* Status Badges */
    .badge-glow-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-glow-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
    .badge-glow-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
    .badge-glow-info    { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); }
    .badge-glow-primary { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h3 class="fw-bold text-white mb-1">Orders & Bookings</h3>
            <p class="text-secondary small mb-0">Manage and track all service requests efficiently.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="location.reload()" class="btn btn-outline-light d-flex align-items-center gap-2 rounded-pill px-4">
                <i class="fas fa-sync-alt"></i> <span>Refresh</span>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-secondary text-uppercase fw-bold letter-spacing">Total Orders</small>
                    <h3 class="text-white mb-0 fw-bold mt-1"><?= $total_o ?></h3>
                </div>
                <div class="p-3 rounded-circle" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; font-size: 1.5rem;"><i class="fas fa-box-open"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-secondary text-uppercase fw-bold letter-spacing">Pending Requests</small>
                    <h3 class="text-warning mb-0 fw-bold mt-1"><?= $pending_o ?></h3>
                </div>
                <div class="p-3 rounded-circle" style="background: rgba(245, 158, 11, 0.1); color: #fbbf24; font-size: 1.5rem;"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-secondary text-uppercase fw-bold letter-spacing">Completed Revenue</small>
                    <h3 class="text-success mb-0 fw-bold mt-1">SAR <?= number_format($revenue_o) ?></h3>
                </div>
                <div class="p-3 rounded-circle" style="background: rgba(34, 197, 94, 0.1); color: #4ade80; font-size: 1.5rem;"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="glass-card mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-0" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-right: 0; color: #94a3b8;"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Order ID or Customer Name..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= (isset($_GET['status']) && $_GET['status']=='pending')?'selected':'' ?>>Pending</option>
                        <option value="assigned" <?= (isset($_GET['status']) && $_GET['status']=='assigned')?'selected':'' ?>>Assigned</option>
                        <option value="on_way" <?= (isset($_GET['status']) && $_GET['status']=='on_way')?'selected':'' ?>>On the Way</option>
                        <option value="started" <?= (isset($_GET['status']) && $_GET['status']=='started')?'selected':'' ?>>Started</option>
                        <option value="completed" <?= (isset($_GET['status']) && $_GET['status']=='completed')?'selected':'' ?>>Completed</option>
                        <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status']=='cancelled')?'selected':'' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Apply Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="orders.php" class="btn btn-outline-secondary w-100 rounded-pill text-white">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="glass-card overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Booking Date</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                    $status_filter = isset($_GET['status']) && $_GET['status'] != '' ? "AND b.status = '{$_GET['status']}'" : '';

                    $sql = "SELECT b.*, 
                            u.name as c_name, u.image as c_image,
                            p.name as p_name, p.image as p_image
                            FROM bookings b 
                            LEFT JOIN users u ON b.user_id = u.id 
                            LEFT JOIN users p ON b.provider_id = p.id 
                            WHERE (b.id LIKE '%$search%' OR u.name LIKE '%$search%') $status_filter 
                            ORDER BY b.created_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                            $st = strtolower($row['status']);
                            $badge = match($st) {
                                'completed' => 'badge-glow-success',
                                'pending'   => 'badge-glow-warning',
                                'cancelled' => 'badge-glow-danger',
                                'assigned'  => 'badge-glow-info',
                                'on_way'    => 'badge-glow-primary',
                                'started'   => 'badge-glow-primary',
                                default     => 'badge-glow-info'
                            };
                    ?>
                    <tr class="order-row" onclick="window.location='order_details.php?id=<?= $row['id'] ?>'" style="cursor: pointer;">
                        
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="date-box me-3">
                                    <span class="text-primary fw-bold small text-uppercase" style="font-size: 0.65rem;"><?= date('M', strtotime($row['schedule_date'])) ?></span>
                                    <span class="text-white fw-bold h5 mb-0"><?= date('d', strtotime($row['schedule_date'])) ?></span>
                                </div>
                                <div>
                                    <span class="text-white fw-bold d-block"><?= date('Y', strtotime($row['schedule_date'])) ?></span>
                                    <small class="text-secondary"><?= $row['schedule_time'] ?></small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-dark border border-secondary text-white px-2 py-1">
                                #ORD-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?>
                            </span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <?php $c_img = !empty($row['c_image']) ? "../api/uploads/".$row['c_image'] : "https://ui-avatars.com/api/?name=".urlencode($row['c_name'])."&background=0f172a&color=fff"; ?>
                                <img src="<?= $c_img ?>" class="rounded-circle border border-secondary me-2 shadow-sm" width="35" height="35">
                                <span class="text-white fw-bold small"><?= htmlspecialchars($row['c_name']) ?></span>
                            </div>
                        </td>

                        <td>
                            <?php if($row['p_name']): ?>
                                <div class="d-flex align-items-center">
                                    <?php $p_img = !empty($row['p_image']) ? "../api/uploads/".$row['p_image'] : "https://ui-avatars.com/api/?name=".urlencode($row['p_name'])."&background=38bdf8&color=fff"; ?>
                                    <img src="<?= $p_img ?>" class="rounded-circle border border-primary me-2 shadow-sm" width="35" height="35">
                                    <span class="text-info small fw-bold"><?= htmlspecialchars($row['p_name']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white small bg-opacity-25 border border-secondary px-2 py-1"><i class="fas fa-user-slash me-1"></i> Unassigned</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="text-white fw-bold">SAR <?= number_format($row['final_total'], 2) ?></span>
                        </td>

                        <td>
                            <span class="badge <?= $badge ?> rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <?= str_replace('_', ' ', $st) ?>
                            </span>
                        </td>

                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-light rounded-circle" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chevron-right small"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="p-4 rounded-circle d-inline-block mb-3" style="background: rgba(255,255,255,0.05);"><i class="fas fa-folder-open fa-3x text-secondary"></i></div>
                                <h5 class="text-white fw-bold mb-1">No Orders Found</h5>
                                <p class="text-secondary mb-0">Try adjusting your search or filters.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>