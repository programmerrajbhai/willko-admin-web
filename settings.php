<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

// --- Handle Update ---
if(isset($_POST['update_settings'])) {
    $app_name = $conn->real_escape_string($_POST['app_name']);
    $currency = $_POST['currency'];
    $delivery = $_POST['delivery_charge'];
    $phone = $_POST['support_phone'];
    $email = $_POST['support_email'];
    $privacy = $conn->real_escape_string($_POST['privacy_policy']);
    $terms = $conn->real_escape_string($_POST['terms_condition']);

    // Check if row exists, else insert
    $check = $conn->query("SELECT * FROM app_settings WHERE id=1");
    if($check->num_rows > 0) {
        $sql = "UPDATE app_settings SET app_name='$app_name', currency='$currency', delivery_charge='$delivery', support_phone='$phone', support_email='$email', privacy_policy='$privacy', terms_condition='$terms' WHERE id=1";
    } else {
        $sql = "INSERT INTO app_settings (id, app_name) VALUES (1, '$app_name')";
    }
    
    if($conn->query($sql)) {
        echo "<script>location.href='settings.php?msg=Settings Updated Successfully';</script>";
    }
}

// Fetch Data
$s = $conn->query("SELECT * FROM app_settings WHERE id=1")->fetch_assoc();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-light mb-1">App Settings</h4>
        <p class="text-muted mb-0 small">Configure your application globals.</p>
    </div>
    <?php if(isset($_GET['msg'])): ?>
        <div class="badge bg-soft-success text-success py-2 px-3"><i class="fas fa-check-circle me-2"></i> Saved!</div>
    <?php endif; ?>
</div>

<form method="POST">
    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card border-0 p-4 h-100" style="background: #1e293b;">
                <h6 class="text-primary fw-bold mb-4"><i class="fas fa-sliders-h me-2"></i>General Config</h6>
                
                <div class="mb-3">
                    <label class="text-muted small fw-bold mb-1">App Name</label>
                    <input type="text" name="app_name" class="form-control" value="<?= $s['app_name'] ?? 'Wilko' ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small fw-bold mb-1">Currency Symbol</label>
                        <input type="text" name="currency" class="form-control" value="<?= $s['currency'] ?? 'SAR' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small fw-bold mb-1">Base Delivery Charge</label>
                        <input type="number" step="0.01" name="delivery_charge" class="form-control" value="<?= $s['delivery_charge'] ?? '0.00' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold mb-1">Support Phone</label>
                    <input type="text" name="support_phone" class="form-control" value="<?= $s['support_phone'] ?? '' ?>">
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small fw-bold mb-1">Support Email</label>
                    <input type="email" name="support_email" class="form-control" value="<?= $s['support_email'] ?? '' ?>">
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 p-4 h-100" style="background: #1e293b;">
                <h6 class="text-info fw-bold mb-4"><i class="fas fa-link me-2"></i>Links & Policies</h6>
                
                <div class="mb-3">
                    <label class="text-muted small fw-bold mb-1">Privacy Policy URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-lock"></i></span>
                        <input type="text" name="privacy_policy" class="form-control" value="<?= $s['privacy_policy'] ?? '' ?>" placeholder="https://...">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-bold mb-1">Terms & Conditions URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-file-alt"></i></span>
                        <input type="text" name="terms_condition" class="form-control" value="<?= $s['terms_condition'] ?? '' ?>" placeholder="https://...">
                    </div>
                </div>

                <div class="mt-5 text-end">
                    <button type="submit" name="update_settings" class="btn btn-primary px-5 py-2 fw-bold shadow-lg">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

<?php include 'includes/footer.php'; ?>