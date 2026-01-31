<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id == 0) echo "<script>location.href='orders.php'</script>";

// --- 1. POST ACTIONS ---
if(isset($_POST['update_status'])) {
    $st = $_POST['status'];
    $conn->query("UPDATE bookings SET status='$st' WHERE id=$id");
    echo "<script>location.href='order_details.php?id=$id&msg=Status Updated'</script>";
}

if(isset($_POST['assign_provider'])) {
    $pid = $_POST['provider_id'];
    $conn->query("UPDATE bookings SET provider_id=$pid, status='assigned' WHERE id=$id");
    echo "<script>location.href='order_details.php?id=$id&msg=Provider Assigned'</script>";
}

// --- 2. DATA FETCH (Order Info) ---
$sql = "SELECT b.*, 
               u.name as c_name, u.phone as c_phone, u.email as c_email, u.image as c_image, u.address as c_address, 
               u.latitude as c_lat, u.longitude as c_lng,
               p.name as p_name, p.phone as p_phone, p.image as p_image, p.rating as p_rating, p.id as p_id
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users p ON b.provider_id = p.id
        WHERE b.id = $id";
$order = $conn->query($sql)->fetch_assoc();

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
    }

    /* Text Colors */
    .text-bright { color: #fff !important; }
    .text-secondary-light { color: #cbd5e1 !important; }
    .text-label { color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Inputs */
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

    /* Status Badges (Glow) */
    .badge-glow { box-shadow: 0 0 10px rgba(255,255,255,0.1); }
    .bg-soft-primary { background: rgba(56, 189, 248, 0.2) !important; color: #38bdf8 !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <a href="orders.php" class="text-secondary-light text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
        <h4 class="fw-bold mt-1 text-bright">Booking #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></h4>
        <span class="badge bg-soft-primary text-uppercase px-3 py-2 mt-1">
            <i class="fas fa-circle small me-1"></i> <?= ucfirst($order['status']) ?>
        </span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this order?')"><i class="fas fa-times me-1"></i> Cancel</button>
        <button class="btn btn-success btn-sm"><i class="fas fa-print me-1"></i> Invoice</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        
        <div class="card glass-card border-0 mb-4">
            <div class="glass-header">
                <h6 class="fw-bold text-bright m-0"><i class="fas fa-layer-group text-primary me-2"></i>Ordered Services</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0 text-secondary-light">
                        <thead style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <tr>
                                <th class="ps-3 text-bright rounded-start">Service Name</th>
                                <th class="text-center text-bright">Rate</th>
                                <th class="text-center text-bright">Qty</th>
                                <th class="text-end pe-3 text-bright rounded-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = $items_res->fetch_assoc()): ?>
                            <tr class="border-bottom" style="border-color: rgba(255,255,255,0.05) !important;">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-soft-primary rounded p-2 me-3"><i class="fas fa-tools"></i></div>
                                        <div>
                                            <div class="fw-bold text-bright"><?= $item['service_name'] ?></div>
                                            <div class="small text-muted">Standard Service</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">SAR <?= number_format($item['total_price'] / $item['quantity'], 2) ?></td>
                                <td class="text-center"><span class="badge bg-dark border border-secondary">x<?= $item['quantity'] ?></span></td>
                                <td class="text-end pe-3 fw-bold text-bright">SAR <?= number_format($item['total_price'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                            <tr>
                                <td colspan="3" class="text-end pt-3 text-secondary-light">Grand Total</td>
                                <td class="text-end pt-3"><h5 class="fw-bold text-primary">SAR <?= number_format($order['final_total'], 2) ?></h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card glass-card border-0 h-100">
                    <div class="card-body">
                        <h6 class="text-label mb-3">Customer Details</h6>
                        <div class="d-flex align-items-center mb-3">
                            <?php $c_img = !empty($order['c_image']) ? "../api/uploads/".$order['c_image'] : "https://ui-avatars.com/api/?name=".$order['c_name']."&background=random&color=fff"; ?>
                            <img src="<?= $c_img ?>" class="avatar-lg rounded-circle me-3 border border-secondary">
                            <div>
                                <h6 class="fw-bold text-bright mb-0"><?= $order['c_name'] ?></h6>
                                <p class="text-secondary-light small mb-0">ID: #USER-<?= $order['user_id'] ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-dark p-2 rounded-circle me-2 border border-secondary"><i class="fas fa-envelope text-muted"></i></div>
                            <span class="small fw-bold text-secondary-light"><?= $order['c_email'] ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-dark p-2 rounded-circle me-2 border border-secondary"><i class="fas fa-phone text-muted"></i></div>
                            <span class="small fw-bold text-secondary-light"><?= $order['c_phone'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card glass-card border-0 h-100">
                    <div class="card-body">
                        <h6 class="text-label mb-3">Service Location</h6>
                        <div class="d-flex mb-3">
                            <i class="fas fa-map-marker-alt text-danger mt-1 me-2 fa-lg"></i>
                            <p class="text-bright fw-bold mb-0" style="font-size: 14px; line-height: 1.5;">
                                <?= $order['c_address'] ?? 'No address provided' ?>
                            </p>
                        </div>
                        <div class="p-3 rounded" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Schedule Date</span>
                                <span class="fw-bold text-bright small"><?= date('d M, Y', strtotime($order['schedule_date'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Time Slot</span>
                                <span class="fw-bold text-bright small"><?= $order['schedule_time'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        
        <?php if($order['p_name']): ?>
        <div class="card glass-card border-0 mb-4" style="background: linear-gradient(145deg, rgba(56, 189, 248, 0.1), rgba(30, 41, 59, 0.8)); border-color: rgba(56, 189, 248, 0.2);">
            <div class="card-body text-center">
                <h6 class="text-primary fw-bold mb-3"><i class="fas fa-check-circle me-2"></i>ASSIGNED PROVIDER</h6>
                <?php $p_img = !empty($order['p_image']) ? "../api/uploads/".$order['p_image'] : "https://ui-avatars.com/api/?name=".$order['p_name']."&background=0ea5e9&color=fff"; ?>
                <img src="<?= $p_img ?>" class="avatar-lg rounded-circle border border-3 border-info shadow mb-2" width="80" height="80">
                <h5 class="fw-bold text-bright mb-1"><?= $order['p_name'] ?></h5>
                <div class="mb-3 text-warning">
                    <?= str_repeat('<i class="fas fa-star"></i>', round($order['p_rating'])) ?> 
                    <span class="text-secondary-light small ms-1">(<?= $order['p_rating'] ?>)</span>
                </div>
                <div class="d-grid gap-2">
                    <a href="tel:<?= $order['p_phone'] ?>" class="btn btn-primary w-100 fw-bold"><i class="fas fa-phone me-2"></i> Call Provider</a>
                    <button class="btn btn-sm btn-outline-secondary text-light" type="button" data-bs-toggle="collapse" data-bs-target="#assignBox">Change Provider</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card glass-card border-0 collapse <?= empty($order['p_name']) ? 'show' : '' ?>" id="assignBox">
            <div class="glass-header">
                <h6 class="fw-bold m-0 text-bright">Assign Nearby Provider</h6>
            </div>
            <div class="card-body p-3">
                
                <div class="search-box mb-3 position-relative">
                    <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                    <input type="text" id="pSearch" class="form-control ps-5" placeholder="Search provider...">
                </div>

                <form method="POST">
                    <input type="hidden" name="provider_id" id="selected_pid" required>
                    
                    <div class="provider-list-box mb-3" id="providerList">
                        <?php 
                        if ($providers_res->num_rows > 0) {
                            while($prov = $providers_res->fetch_assoc()): 
                                $p_img = !empty($prov['image']) ? "../api/uploads/".$prov['image'] : "https://ui-avatars.com/api/?name=".$prov['name']."&background=random";
                                $dist = round($prov['distance'], 1); // Distance in KM
                                $loc_badge = $dist < 50 ? 'text-success' : 'text-muted';
                        ?>
                        
                        <div class="provider-card p-2 mb-2 d-flex align-items-center" onclick="selectProvider(this, <?= $prov['id'] ?>)">
                            <img src="<?= $p_img ?>" class="avatar rounded-circle me-3" width="40" height="40">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-bright small provider-name"><?= $prov['name'] ?></h6>
                                <small class="text-secondary-light provider-loc">
                                    <i class="fas fa-map-marker-alt me-1 <?= $loc_badge ?>"></i> <?= $dist ?> km away
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-dark border border-secondary text-warning"><i class="fas fa-star"></i> <?= $prov['rating'] ?></span>
                            </div>
                        </div>

                        <?php endwhile; 
                        } else {
                            echo '<div class="text-center text-muted py-4"><i class="fas fa-map-marked-alt fa-2x mb-2"></i><p>No active providers found nearby.</p></div>';
                        }
                        ?>
                    </div>

                    <button type="submit" name="assign_provider" id="assignBtn" class="btn btn-primary w-100 py-2 fw-bold" disabled>
                        Confirm Assignment
                    </button>
                </form>
            </div>
        </div>

        <div class="card glass-card border-0 mt-4">
            <div class="card-body">
                <h6 class="text-label mb-3">Update Order Status</h6>
                <form method="POST">
                    <div class="input-group mb-3">
                        <select name="status" class="form-select">
                            <?php 
                            $status_list = ['pending', 'assigned', 'on_way', 'started', 'completed', 'cancelled'];
                            foreach($status_list as $s) {
                                $sel = $order['status'] == $s ? 'selected' : '';
                                echo "<option value='$s' $sel>".ucfirst(str_replace('_', ' ', $s))."</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Provider Selection Logic
    function selectProvider(element, id) {
        // Remove active class from all
        document.querySelectorAll('.provider-card').forEach(el => el.classList.remove('selected'));
        // Add to clicked
        element.classList.add('selected');
        // Set Input Value
        document.getElementById('selected_pid').value = id;
        // Enable Button
        document.getElementById('assignBtn').disabled = false;
        document.getElementById('assignBtn').innerHTML = 'Assign Selected Provider';
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