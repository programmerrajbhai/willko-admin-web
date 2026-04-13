<?php 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// CSRF Session Setup
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id == 0) echo "<script>location.href='orders.php'</script>";

// --- 1. POST ACTIONS (Secure) ---
if(isset($_POST['update_status']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $st = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE bookings SET status='$st' WHERE id=$id");
    echo "<script>location.href='order_details.php?id=$id&msg=Status Updated'</script>";
}

if(isset($_POST['assign_provider']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $pid = (int)$_POST['provider_id'];
    $conn->query("UPDATE bookings SET provider_id=$pid, status='assigned' WHERE id=$id");
    echo "<script>location.href='order_details.php?id=$id&msg=Provider Assigned'</script>";
}

// --- 2. DATA FETCH (Order & User Info) ---
$sql = "SELECT b.*, 
               u.name as c_name, u.phone as c_phone, u.email as c_email, u.image as c_image, u.address as profile_address, 
               u.latitude as c_lat, u.longitude as c_lng,
               p.name as p_name, p.phone as p_phone, p.image as p_image, p.rating as p_rating, p.id as p_id
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users p ON b.provider_id = p.id
        WHERE b.id = $id";
$order = $conn->query($sql)->fetch_assoc();

if(!$order) {
    echo "<script>location.href='orders.php'</script>";
    exit;
}

// Fallback logic: If booking table has address, use it. Otherwise, use profile address.
$display_address = !empty($order['address']) ? $order['address'] : (!empty($order['profile_address']) ? $order['profile_address'] : 'Address not provided by customer');

// Service Items
$items_res = $conn->query("SELECT * FROM booking_items WHERE booking_id = $id");

// --- 3. SMART PROVIDER FETCH (Sorted by Distance) ---
$c_lat = $order['c_lat'] ?: 0;
$c_lng = $order['c_lng'] ?: 0;

$prov_sql = "SELECT *, 
    ( 6371 * acos( cos( radians($c_lat) ) * cos( radians( latitude ) ) 
    * cos( radians( longitude ) - radians($c_lng) ) + sin( radians($c_lat) ) 
    * sin( radians( latitude ) ) ) ) AS distance 
    FROM users 
    WHERE role = 'provider' AND status = 'active'
    ORDER BY distance ASC"; 

$providers_res = $conn->query($prov_sql);

// Status Badge Logic
$st = strtolower($order['status']);
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

<style>
    /* Premium Dark Glass UI */
    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
    }
    
    .glass-header {
        background: rgba(15, 23, 42, 0.6);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 15px 20px;
        border-radius: 20px 20px 0 0;
    }

    .text-bright { color: #fff !important; }
    .text-secondary-light { color: #cbd5e1 !important; }
    .text-label { color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    .form-control, .form-select {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 10px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
    }

    /* Enhanced Address Box */
    .address-box {
        background: linear-gradient(145deg, rgba(34, 197, 94, 0.1), rgba(15, 23, 42, 0.6));
        border-left: 4px solid #22c55e;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }
    .address-box::before {
        content: '\f279'; /* Map Map icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: -10px;
        bottom: -20px;
        font-size: 100px;
        opacity: 0.05;
        color: #22c55e;
    }

    /* Info Badge */
    .info-badge {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 8px 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
    }

    /* Provider List Styles */
    .provider-list-box { max-height: 350px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }
    
    .provider-card { 
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.2s; cursor: pointer; 
        border-radius: 12px;
    }
    .provider-card:hover { 
        background: rgba(56, 189, 248, 0.1); 
        border-color: #38bdf8; 
        transform: translateX(5px); 
    }
    .provider-card.selected { 
        border-color: #38bdf8; 
        background: rgba(56, 189, 248, 0.15); 
        box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
    }

    .badge-glow-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-glow-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
    .badge-glow-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
    .badge-glow-info    { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); }
    .badge-glow-primary { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); }
</style>

<div class="container-fluid">
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success bg-success bg-opacity-25 text-success border-0 rounded-pill py-2 text-center mb-3">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <a href="orders.php" class="text-secondary-light text-decoration-none small mb-2 d-inline-block"><i class="fas fa-arrow-left me-1"></i> Back to Orders</a>
            <div class="d-flex align-items-center gap-3">
                <h3 class="fw-bold m-0 text-bright">Order #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></h3>
                <span class="badge <?= $badge ?> text-uppercase px-3 py-2 rounded-pill shadow-sm" style="letter-spacing: 0.5px;">
                    <i class="fas fa-circle small me-1" style="font-size: 8px;"></i> <?= str_replace('_', ' ', ucfirst($order['status'])) ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger px-4 rounded-pill" onclick="return confirm('Are you sure you want to cancel this order?')"><i class="fas fa-times me-2"></i> Cancel Order</button>
            <button class="btn btn-success px-4 rounded-pill"><i class="fas fa-file-invoice me-2"></i> Print Invoice</button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        
        <div class="col-lg-8">
            
            <div class="card glass-card border-0 mb-4 p-4">
                <div class="row g-4">
                    <div class="col-md-6 border-end" style="border-color: rgba(255,255,255,0.1) !important;">
                        <h6 class="text-label mb-3"><i class="fas fa-user-circle text-primary me-2"></i>Customer Information</h6>
                        
                        <div class="d-flex align-items-center mb-4">
                            <?php $c_img = !empty($order['c_image']) ? "../api/uploads/".$order['c_image'] : "https://ui-avatars.com/api/?name=".urlencode($order['c_name'])."&background=0f172a&color=fff"; ?>
                            <img src="<?= $c_img ?>" class="rounded-circle border border-primary shadow-sm me-3" width="60" height="60">
                            <div>
                                <h5 class="fw-bold text-bright mb-1"><?= htmlspecialchars($order['c_name']) ?></h5>
                                <span class="badge bg-dark border border-secondary text-secondary">Customer ID: <?= $order['user_id'] ?></span>
                            </div>
                        </div>

                        <div class="info-badge mb-2">
                            <i class="fas fa-phone-alt text-success me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Phone Number</small>
                                <span class="fw-bold text-bright"><?= htmlspecialchars($order['c_phone']) ?></span>
                            </div>
                        </div>
                        <div class="info-badge">
                            <i class="fas fa-envelope text-info me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Email Address</small>
                                <span class="fw-bold text-bright"><?= htmlspecialchars($order['c_email']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-label mb-3"><i class="fas fa-map-marked-alt text-success me-2"></i>Service Location & Time</h6>
                        
                        <div class="address-box p-3 mb-3 shadow-sm">
                            <div class="d-flex align-items-start">
                                <div class="bg-success bg-opacity-25 p-2 rounded-circle me-3 mt-1">
                                    <i class="fas fa-map-marker-alt text-success fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-white mb-1">Service Address</h6>
                                    <p class="text-secondary-light mb-0 small" style="line-height: 1.6;">
                                        <?= htmlspecialchars($display_address) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="info-badge flex-fill">
                                <i class="far fa-calendar-alt text-warning me-2 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Date</small>
                                    <span class="fw-bold text-bright small"><?= date('d M, Y', strtotime($order['schedule_date'])) ?></span>
                                </div>
                            </div>
                            <div class="info-badge flex-fill">
                                <i class="far fa-clock text-warning me-2 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Time</small>
                                    <span class="fw-bold text-bright small"><?= htmlspecialchars($order['schedule_time']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card glass-card border-0 mb-4">
                <div class="glass-header">
                    <h6 class="fw-bold text-bright m-0"><i class="fas fa-tools text-primary me-2"></i>Ordered Services</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0 text-secondary-light">
                            <thead style="background: rgba(255,255,255,0.02);">
                                <tr>
                                    <th class="ps-4 text-bright py-3">Service Description</th>
                                    <th class="text-center text-bright py-3">Rate</th>
                                    <th class="text-center text-bright py-3">Quantity</th>
                                    <th class="text-end pe-4 text-bright py-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $items_res->fetch_assoc()): ?>
                                <tr class="border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-soft-primary rounded p-2 me-3 shadow-sm"><i class="fas fa-wrench"></i></div>
                                            <div>
                                                <div class="fw-bold text-bright" style="font-size: 15px;"><?= htmlspecialchars($item['service_name']) ?></div>
                                                <div class="small text-muted">Standard Package</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">SAR <?= number_format($item['total_price'] / $item['quantity'], 2) ?></td>
                                    <td class="text-center"><span class="badge bg-dark border border-secondary px-3 py-2 fs-6">x<?= $item['quantity'] ?></span></td>
                                    <td class="text-end pe-4 fw-bold text-bright">SAR <?= number_format($item['total_price'], 2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4" style="background: rgba(0,0,0,0.2); border-radius: 0 0 20px 20px;">
                        <div class="d-flex justify-content-end align-items-center">
                            <span class="text-secondary-light fs-5 me-4">Grand Total:</span>
                            <h3 class="fw-bold text-primary mb-0">SAR <?= number_format($order['final_total'], 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <?php if($order['p_name']): ?>
            <div class="card glass-card border-0 mb-4" style="background: linear-gradient(145deg, rgba(56, 189, 248, 0.15), rgba(15, 23, 42, 0.9)); border: 1px solid rgba(56, 189, 248, 0.3);">
                <div class="card-body text-center p-4">
                    <div class="badge bg-success mb-3 px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-check-circle me-1"></i> Provider Assigned</div>
                    
                    <?php $p_img = !empty($order['p_image']) ? "../api/uploads/".$order['p_image'] : "https://ui-avatars.com/api/?name=".urlencode($order['p_name'])."&background=0ea5e9&color=fff"; ?>
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?= $p_img ?>" class="rounded-circle border border-4 border-info shadow-lg" width="90" height="90">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-dark rounded-circle p-2"></span>
                    </div>
                    
                    <h4 class="fw-bold text-bright mb-1"><?= htmlspecialchars($order['p_name']) ?></h4>
                    <p class="text-secondary small mb-2">Professional Expert</p>
                    
                    <div class="mb-4 text-warning fs-5">
                        <?= str_repeat('<i class="fas fa-star"></i>', round($order['p_rating'])) ?> 
                        <span class="text-white fw-bold ms-1" style="font-size: 14px;">(<?= $order['p_rating'] ?>)</span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="tel:<?= $order['p_phone'] ?>" class="btn btn-info text-dark w-100 fw-bold rounded-pill"><i class="fas fa-phone-alt me-2"></i> Call Provider</a>
                        <button class="btn btn-sm btn-outline-light rounded-pill mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#assignBox">Re-Assign Provider</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card glass-card border-0 collapse <?= empty($order['p_name']) ? 'show' : '' ?> mb-4" id="assignBox">
                <div class="glass-header">
                    <h6 class="fw-bold m-0 text-bright"><i class="fas fa-users-cog text-info me-2"></i><?= empty($order['p_name']) ? 'Assign Provider' : 'Change Provider' ?></h6>
                </div>
                <div class="card-body p-3">
                    <div class="search-box mb-3 position-relative">
                        <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                        <input type="text" id="pSearch" class="form-control ps-5 rounded-pill" placeholder="Search provider name...">
                    </div>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="provider_id" id="selected_pid" required>
                        
                        <div class="provider-list-box mb-3 pe-1" id="providerList">
                            <?php 
                            if ($providers_res->num_rows > 0) {
                                while($prov = $providers_res->fetch_assoc()): 
                                    $p_img = !empty($prov['image']) ? "../api/uploads/".$prov['image'] : "https://ui-avatars.com/api/?name=".urlencode($prov['name'])."&background=random";
                                    $dist = round($prov['distance'], 1); // Distance in KM
                                    $loc_badge = $dist < 50 ? 'text-success' : 'text-muted';
                            ?>
                            <div class="provider-card p-3 mb-2 d-flex align-items-center" onclick="selectProvider(this, <?= $prov['id'] ?>)">
                                <img src="<?= $p_img ?>" class="rounded-circle me-3 border border-secondary" width="45" height="45">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-bright small provider-name"><?= htmlspecialchars($prov['name']) ?></h6>
                                    <small class="text-secondary-light provider-loc">
                                        <i class="fas fa-map-marker-alt me-1 <?= $loc_badge ?>"></i> <?= $dist ?> km away
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-dark border border-secondary text-warning px-2 py-1"><i class="fas fa-star"></i> <?= $prov['rating'] ?></span>
                                </div>
                            </div>
                            <?php endwhile; 
                            } else {
                                echo '<div class="text-center text-muted py-5"><i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i><p>No active providers found nearby.</p></div>';
                            }
                            ?>
                        </div>

                        <button type="submit" name="assign_provider" id="assignBtn" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow" disabled>
                            Confirm Assignment
                        </button>
                    </form>
                </div>
            </div>

            <div class="card glass-card border-0">
                <div class="glass-header">
                    <h6 class="fw-bold m-0 text-bright"><i class="fas fa-sliders-h text-warning me-2"></i>Update Order Status</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Select Status</label>
                            <select name="status" class="form-select py-2">
                                <?php 
                                $status_list = ['pending', 'assigned', 'on_way', 'started', 'completed', 'cancelled'];
                                foreach($status_list as $s) {
                                    $sel = $order['status'] == $s ? 'selected' : '';
                                    echo "<option value='$s' $sel>".ucfirst(str_replace('_', ' ', $s))."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-warning w-100 fw-bold rounded-pill text-dark shadow">
                            <i class="fas fa-save me-2"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Provider Selection Logic
    function selectProvider(element, id) {
        document.querySelectorAll('.provider-card').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('selected_pid').value = id;
        document.getElementById('assignBtn').disabled = false;
        document.getElementById('assignBtn').innerHTML = '<i class="fas fa-check-circle me-2"></i> Assign Selected Provider';
    }

    // Live Search Filter
    document.getElementById('pSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let items = document.querySelectorAll('.provider-card');

        items.forEach(function(item) {
            let name = item.querySelector('.provider-name').innerText.toLowerCase();
            let loc = item.querySelector('.provider-loc').innerText.toLowerCase();
            if (name.includes(filter) || loc.includes(filter)) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>