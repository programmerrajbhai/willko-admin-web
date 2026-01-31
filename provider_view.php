<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

$id = $_GET['id'];

// --- UPDATE LOGIC (Fixed) ---

// 1. Update Personal Info
if(isset($_POST['update_personal'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $status = $_POST['status'];

    $conn->query("UPDATE users SET name='$name', phone='$phone', status='$status' WHERE id=$id");
    echo "<script>location.href='provider_view.php?id=$id&msg=Profile Updated';</script>";
}

// 2. Update Bank & NID Info
if(isset($_POST['update_bank'])) {
    $nid = $conn->real_escape_string($_POST['nid']);
    $bank = $conn->real_escape_string($_POST['bank']);
    $acc = $conn->real_escape_string($_POST['acc']);

    $conn->query("UPDATE users SET nid_number='$nid', bank_name='$bank', account_number='$acc' WHERE id=$id");
    echo "<script>location.href='provider_view.php?id=$id&msg=Bank Details Updated';</script>";
}

// Data Fetch
$p = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
$total_earnings = $conn->query("SELECT SUM(final_total) FROM bookings WHERE provider_id=$id AND status='completed'")->fetch_row()[0] ?? 0;
$jobs = $conn->query("SELECT * FROM bookings WHERE provider_id=$id ORDER BY id DESC LIMIT 10");
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

    /* Inputs */
    .form-control, .form-select {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 10px;
        padding: 12px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
    }
    .form-label { color: #cbd5e1; font-size: 0.9rem; margin-bottom: 8px; }

    /* Custom Tabs */
    .nav-tabs { border-bottom: 1px solid rgba(255,255,255,0.1); }
    .nav-tabs .nav-link {
        color: #94a3b8;
        border: none;
        padding: 12px 20px;
        font-weight: 500;
        transition: 0.3s;
    }
    .nav-tabs .nav-link:hover { color: #fff; }
    .nav-tabs .nav-link.active {
        background: transparent;
        color: #38bdf8;
        border-bottom: 2px solid #38bdf8;
    }

    /* Table */
    .table { color: #cbd5e1; }
    .table thead th {
        background: rgba(15, 23, 42, 0.8);
        color: #94a3b8;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        text-transform: uppercase; font-size: 0.75rem;
    }
    .table-hover tbody tr:hover { background: rgba(255,255,255,0.03); }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <a href="providers.php" class="text-decoration-none small text-info"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            <h4 class="fw-bold mt-1 text-white">Provider Profile</h4>
        </div>
        <?php if(isset($_GET['msg'])): ?>
            <span class="badge bg-soft-success text-success py-2 px-3 border border-success"><i class="fas fa-check me-1"></i> <?= $_GET['msg'] ?></span>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-md-4 col-lg-3">
            <div class="glass-card text-center p-4">
                <?php $img = !empty($p['image']) ? "../api/uploads/".$p['image'] : "https://ui-avatars.com/api/?name=".$p['name']."&background=0f172a&color=fff"; ?>
                <div class="position-relative d-inline-block mb-3">
                    <img src="<?= $img ?>" class="avatar-lg rounded-circle shadow border border-2 border-secondary" style="width:110px; height:110px; object-fit: cover;">
                    <span class="position-absolute bottom-0 end-0 p-2 bg-<?= $p['status']=='active'?'success':'danger' ?> border border-dark rounded-circle"></span>
                </div>
                
                <h5 class="fw-bold mb-1 text-white"><?= $p['name'] ?></h5>
                <p class="text-secondary small mb-3"><?= $p['email'] ?></p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-soft-warning text-warning border border-warning px-3"><i class="fas fa-star me-1"></i> <?= $p['rating'] ?></span>
                    <span class="badge bg-soft-info text-info border border-info px-3">ID: #<?= $p['id'] ?></span>
                </div>

                <div class="border-top border-secondary pt-3 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Total Earned</span>
                        <span class="fw-bold text-success">SAR <?= number_format($total_earnings) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Joined Date</span>
                        <span class="fw-bold text-white small"><?= date('d M, Y', strtotime($p['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-lg-9">
            <div class="glass-card">
                <div class="card-header bg-transparent border-bottom border-secondary pb-0">
                    <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#info" role="tab"><i class="fas fa-user me-2"></i>Personal Info</a></li>
                        <li class="nav-item"><a class="nav-link" id="bank-tab" data-bs-toggle="tab" href="#bank" role="tab"><i class="fas fa-university me-2"></i>Bank & NID</a></li>
                        <li class="nav-item"><a class="nav-link" id="jobs-tab" data-bs-toggle="tab" href="#jobs" role="tab"><i class="fas fa-history me-2"></i>Job History</a></li>
                    </ul>
                </div>
                
                <div class="card-body p-4">
                    <div class="tab-content" id="myTabContent">
                        
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="<?= $p['name'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" value="<?= $p['phone'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?= $p['status']=='active'?'selected':'' ?>>Active</option>
                                            <option value="inactive" <?= $p['status']=='inactive'?'selected':'' ?>>Inactive</option>
                                            <option value="banned" <?= $p['status']=='banned'?'selected':'' ?>>Banned</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" name="update_personal" class="btn btn-primary fw-bold px-4 shadow-glow">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="bank" role="tabpanel">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">NID / Passport Number</label>
                                        <input type="text" name="nid" class="form-control" value="<?= $p['nid_number'] ?>" placeholder="Ex: 1234567890">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bank" class="form-control" value="<?= $p['bank_name'] ?>" placeholder="Ex: Al Rajhi Bank">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="acc" class="form-control font-monospace" value="<?= $p['account_number'] ?>">
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" name="update_bank" class="btn btn-primary fw-bold px-4 shadow-glow">Update Bank Info</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="jobs" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Job ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($j = $jobs->fetch_assoc()): 
                                            $badge = $j['status']=='completed'?'success':($j['status']=='cancelled'?'danger':'warning');
                                        ?>
                                        <tr>
                                            <td><a href="order_details.php?id=<?= $j['id'] ?>" class="text-decoration-none fw-bold text-info">#ORD-<?= str_pad($j['id'], 4, '0', STR_PAD_LEFT) ?></a></td>
                                            <td class="small text-secondary"><?= date('d M, Y', strtotime($j['schedule_date'])) ?></td>
                                            <td><span class="badge bg-soft-<?= $badge ?> text-<?= $badge ?>"><?= ucfirst($j['status']) ?></span></td>
                                            <td class="text-end fw-bold text-white">SAR <?= number_format($j['final_total']) ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>