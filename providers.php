<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

// Quick Stats
$total_prov = $conn->query("SELECT COUNT(*) FROM users WHERE role='provider'")->fetch_row()[0];
$active_prov = $conn->query("SELECT COUNT(*) FROM users WHERE role='provider' AND status='active'")->fetch_row()[0];
$total_jobs = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='completed'")->fetch_row()[0]; // Total System Jobs
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

    /* Input Fields */
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
    .provider-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }
    .provider-row:hover {
        background: rgba(56, 189, 248, 0.08);
        transform: scale(1.005);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        z-index: 10;
        position: relative;
    }

    /* Badges */
    .badge-glow-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.2); }
    .badge-glow-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
    .badge-glow-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h3 class="fw-bold text-white mb-1">Service Providers</h3>
        <p class="text-secondary small mb-0">Manage your expert professionals.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="location.reload()" class="btn btn-outline-light"><i class="fas fa-sync-alt"></i></button>
        <a href="provider_add.php" class="btn btn-primary fw-bold shadow-glow">
            <i class="fas fa-plus me-2"></i> Add Provider
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-secondary text-uppercase fw-bold">Total Providers</small>
                <h4 class="text-white mb-0 fw-bold"><?= $total_prov ?></h4>
            </div>
            <div class="p-3 rounded-circle" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-secondary text-uppercase fw-bold">Active Now</small>
                <h4 class="text-success mb-0 fw-bold"><?= $active_prov ?></h4>
            </div>
            <div class="p-3 rounded-circle" style="background: rgba(34, 197, 94, 0.1); color: #4ade80;"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-secondary text-uppercase fw-bold">Total Jobs Done</small>
                <h4 class="text-info mb-0 fw-bold"><?= $total_jobs ?></h4>
            </div>
            <div class="p-3 rounded-circle" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9;"><i class="fas fa-briefcase"></i></div>
        </div>
    </div>
</div>

<div class="glass-card mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text border-0" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-right: 0; color: #94a3b8;"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search by name, phone or email...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-secondary fw-bold">Showing all providers</small>
            </div>
        </div>
    </div>
</div>

<div class="glass-card overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="providerTable">
            <thead>
                <tr>
                    <th class="ps-4">Provider Profile</th>
                    <th>Contact Info</th>
                    <th>Jobs & Wallet</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query with Earnings Calculation
                $sql = "SELECT u.*, 
                        (SELECT COUNT(*) FROM bookings WHERE provider_id = u.id AND status='completed') as jobs_count
                        FROM users u 
                        WHERE u.role='provider' 
                        ORDER BY u.id DESC";
                $res = $conn->query($sql);

                while($row = $res->fetch_assoc()):
                    $img = !empty($row['image']) ? "../api/uploads/".$row['image'] : "https://ui-avatars.com/api/?name=".$row['name']."&background=random&color=fff";
                    $status_badge = ($row['status'] == 'active') ? 'badge-glow-success' : 'badge-glow-danger';
                    $status_icon = ($row['status'] == 'active') ? 'fa-check' : 'fa-ban';
                ?>
                <tr class="provider-row" onclick="window.location='provider_view.php?id=<?= $row['id'] ?>'" style="cursor:pointer;">
                    
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="position-relative">
                                <img src="<?= $img ?>" class="rounded-circle me-3 border border-secondary" width="45" height="45" style="object-fit: cover;">
                                <span class="position-absolute bottom-0 end-0 p-1 bg-<?= ($row['status']=='active')?'success':'danger' ?> border border-dark rounded-circle"></span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-white"><?= $row['name'] ?></h6>
                                <span class="small text-secondary">ID: #PRO-<?= $row['id'] ?></span>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-column">
                            <span class="small fw-bold text-light"><i class="fas fa-phone-alt text-secondary me-2"></i> <?= $row['phone'] ?></span>
                            <span class="small text-secondary mt-1"><i class="fas fa-envelope text-secondary me-2"></i> <?= $row['email'] ?></span>
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-column">
                            <span class="text-info fw-bold"><i class="fas fa-briefcase me-1"></i> <?= $row['jobs_count'] ?> Jobs</span>
                            <span class="text-success small fw-bold mt-1">SAR <?= number_format($row['balance'], 2) ?></span>
                        </div>
                    </td>

                    <td>
                        <div class="badge badge-glow-warning rounded-pill px-3">
                            <i class="fas fa-star me-1"></i> <?= $row['rating'] ?>
                        </div>
                    </td>

                    <td>
                        <span class="badge <?= $status_badge ?> rounded-pill px-3 py-2">
                            <i class="fas <?= $status_icon ?> me-1 small"></i> <?= ucfirst($row['status']) ?>
                        </span>
                    </td>

                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-light rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chevron-right small"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#providerTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

document.getElementById('statusFilter').addEventListener('change', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#providerTable tbody tr');
    rows.forEach(row => {
        if(filter === "") {
            row.style.display = '';
        } else {
            let status = row.querySelector('td:nth-child(5)').innerText.toLowerCase();
            row.style.display = status.includes(filter) ? '' : 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>