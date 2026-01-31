<?php include 'includes/header.php'; include 'includes/sidebar.php'; 

// --- Actions: Ban/Unban/Delete ---
if(isset($_GET['action']) && isset($_GET['id'])) {
    $uid = $_GET['id'];
    $action = $_GET['action'];
    
    if($action == 'ban') {
        $conn->query("UPDATE users SET status='banned' WHERE id=$uid");
        echo "<script>location.href='list.php';</script>";
    } elseif($action == 'active') {
        $conn->query("UPDATE users SET status='active' WHERE id=$uid");
        echo "<script>location.href='list.php';</script>";
    } elseif($action == 'delete') {
        $conn->query("DELETE FROM users WHERE id=$uid");
        echo "<script>location.href='list.php';</script>";
    }
}
?>

<style>
    /* User Avatar Glow */
    .user-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #334155; transition: 0.3s; }
    .user-row:hover .user-avatar { border-color: #38bdf8; box-shadow: 0 0 10px rgba(56, 189, 248, 0.4); }
    
    /* Action Buttons */
    .btn-action { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; border: 1px solid #334155; background: #1e293b; color: #94a3b8; }
    .btn-action:hover { background: #38bdf8; color: #000; border-color: #38bdf8; transform: translateY(-2px); }
    .btn-action.delete:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-light mb-1">Customer Management</h4>
        <p class="text-muted mb-0 small">View and manage all registered users.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="location.reload()" class="btn btn-light shadow-sm"><i class="fas fa-sync-alt"></i></button>
        <button class="btn btn-primary shadow-glow"><i class="fas fa-file-export me-2"></i> Export CSV</button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-4 border-0" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Users</h6>
                    <h3 class="fw-bold text-white mb-0"><?= $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0] ?></h3>
                </div>
                <div class="bg-soft-primary p-3 rounded-circle text-primary"><i class="fas fa-users fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 border-0" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Active Users</h6>
                    <h3 class="fw-bold text-success mb-0"><?= $conn->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='active'")->fetch_row()[0] ?></h3>
                </div>
                <div class="bg-soft-success p-3 rounded-circle text-success"><i class="fas fa-user-check fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 border-0" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Banned Users</h6>
                    <h3 class="fw-bold text-danger mb-0"><?= $conn->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='banned'")->fetch_row()[0] ?></h3>
                </div>
                <div class="bg-soft-danger p-3 rounded-circle text-danger"><i class="fas fa-user-slash fa-lg"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 mb-4">
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-secondary text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by Name, Phone, Email...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Banned">Banned</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-lg">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="userTable">
            <thead>
                <tr>
                    <th class="ps-4">User Profile</th>
                    <th>Contact Info</th>
                    <th>Joined Date</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch Users with Order Count
                $sql = "SELECT u.*, 
                        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as total_orders 
                        FROM users u 
                        WHERE u.role='user' 
                        ORDER BY u.id DESC";
                $res = $conn->query($sql);

                while($row = $res->fetch_assoc()):
                    $img = !empty($row['image']) ? "../api/uploads/".$row['image'] : "https://ui-avatars.com/api/?name=".$row['name']."&background=random&color=fff";
                    $status_badge = $row['status'] == 'active' ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger';
                ?>
                <tr class="user-row">
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <img src="<?= $img ?>" class="user-avatar me-3">
                            <div>
                                <h6 class="fw-bold text-light mb-0"><?= $row['name'] ?></h6>
                                <span class="small text-muted">ID: #USR-<?= $row['id'] ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small text-light"><i class="fas fa-phone me-2 text-muted"></i> <?= $row['phone'] ?></div>
                        <div class="small text-muted"><i class="fas fa-envelope me-2"></i> <?= $row['email'] ?></div>
                    </td>
                    <td class="text-muted small">
                        <?= date('d M, Y', strtotime($row['created_at'])) ?>
                    </td>
                    <td>
                        <span class="badge bg-soft-primary text-primary px-3">
                            <?= $row['total_orders'] ?> Orders
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $status_badge ?> rounded-pill px-3">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="#" class="btn-action" title="View Details"><i class="fas fa-eye"></i></a>
                            
                            <?php if($row['status'] == 'active'): ?>
                                <a href="list.php?action=ban&id=<?= $row['id'] ?>" class="btn-action text-warning" onclick="return confirm('Ban this user?')" title="Ban User">
                                    <i class="fas fa-ban"></i>
                                </a>
                            <?php else: ?>
                                <a href="list.php?action=active&id=<?= $row['id'] ?>" class="btn-action text-success" onclick="return confirm('Activate this user?')" title="Activate User">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>

                            <a href="list.php?action=delete&id=<?= $row['id'] ?>" class="btn-action delete" onclick="return confirm('Are you sure? This will delete all order history!')" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
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
        let rows = document.querySelectorAll('#userTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#userTable tbody tr');
        rows.forEach(row => {
            let status = row.querySelector('td:nth-child(5)').innerText.toLowerCase();
            if(filter === "") row.style.display = '';
            else row.style.display = status.includes(filter) ? '' : 'none';
        });
    });
</script>

<?php include 'includes/footer.php'; ?>