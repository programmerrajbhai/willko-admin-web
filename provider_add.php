<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

$msg = "";
$err = "";

if(isset($_POST['add_provider'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    
    // NID & Bank Info
    $nid = $_POST['nid'];
    $bank = $_POST['bank_name'];
    $acc_num = $_POST['account_number'];

    // Check if email/phone exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email' OR phone='$phone'");
    if($check->num_rows > 0) {
        $err = "Email or Phone already exists!";
    } else {
        // Insert Query
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, address, role, status, nid_number, bank_name, account_number) VALUES (?, ?, ?, ?, ?, 'provider', 'active', ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $email, $phone, $pass, $address, $nid, $bank, $acc_num);
        
        if($stmt->execute()) {
            echo "<script>location.href='providers.php';</script>";
        } else {
            $err = "Database Error: " . $conn->error;
        }
    }
}
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
    .form-control {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 10px;
        padding: 12px;
    }
    .form-control:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
    }
    .form-label { color: #cbd5e1; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; }
    .form-control::placeholder { color: #64748b; }

    /* Headers */
    .section-title {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding-bottom: 15px;
        margin-bottom: 20px;
        font-weight: 700;
        color: #fff;
    }
</style>

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <a href="providers.php" class="text-info text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            <h4 class="fw-bold mt-1 text-white">Add New Provider</h4>
        </div>
    </div>

    <?php if($err): ?>
        <div class="alert alert-danger bg-danger text-white border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> <?= $err ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="row g-4">
        
        <div class="col-lg-8">
            <div class="glass-card p-4 h-100">
                <h6 class="section-title text-info"><i class="fas fa-user-circle me-2"></i>Personal Information</h6>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Rakib Hasan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required placeholder="+8801...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="provider@mail.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Login Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="******">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street, City..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h6 class="section-title text-success"><i class="fas fa-university me-2"></i>Bank & Identity</h6>
                
                <div class="mb-3">
                    <label class="form-label">NID / Passport No</label>
                    <input type="text" name="nid" class="form-control" placeholder="1234567890">
                </div>
                <div class="mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="Ex: City Bank">
                </div>
                <div class="mb-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control font-monospace" placeholder="Account No">
                </div>
                
                <button type="submit" name="add_provider" class="btn btn-primary w-100 py-3 fw-bold shadow-lg mt-auto">
                    <i class="fas fa-plus-circle me-2"></i> Create Account
                </button>
            </div>
        </div>

    </form>
</div>

<?php include 'includes/footer.php'; ?>