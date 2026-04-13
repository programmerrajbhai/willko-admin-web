<?php 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// =======================================================
// 🔥 1. STATS FETCHING LOGIC (Optimized)
// =======================================================

// Total Orders, Active Providers, Total Customers, Total Revenue
$total_orders = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$active_providers = $conn->query("SELECT COUNT(*) FROM users WHERE role='provider' AND status='active'")->fetch_row()[0];
$total_customers = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$total_revenue = $conn->query("SELECT SUM(final_total) FROM bookings WHERE status='completed'")->fetch_row()[0] ?? 0;

// =======================================================
// 🔥 2. PERFORMANCE FIX: N+1 Query Problem Solved!
// =======================================================
// আগে লুপের ভেতর ৭ বার ডাটাবেসে কল করা হতো। এখন মাত্র ১টি কোয়েরিতে ৭ দিনের ডাটা আনা হচ্ছে।
$sql_sales = "SELECT DATE(created_at) as sale_date, SUM(final_total) as daily_total 
              FROM bookings 
              WHERE status='completed' 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
              GROUP BY DATE(created_at) 
              ORDER BY DATE(created_at) ASC";

$sales_result = $conn->query($sql_sales);
$sales_map = [];

// ডাটাবেস থেকে পাওয়া রেজাল্ট ম্যাপে সেভ করা
while($row = $sales_result->fetch_assoc()) {
    $sales_map[$row['sale_date']] = (float)$row['daily_total'];
}

// চার্টের জন্য X-Axis (Dates) এবং Y-Axis (Sales) সাজানো
$dates = []; 
$sales = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($d)); // "14 Apr" format
    
    // যদি ওই তারিখে সেল থাকে তাহলে সেটা বসবে, না থাকলে 0 বসবে
    $sales[] = $sales_map[$d] ?? 0;
}

// =======================================================
// 🔥 3. ORDER STATUS FETCHING (Optimized)
// =======================================================
// আগে ৩টি আলাদা কোয়েরি চলতো, এখন মাত্র ১টিতে করা হলো।
$status_res = $conn->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$order_stats = ['pending' => 0, 'completed' => 0, 'cancelled' => 0];

while($row = $status_res->fetch_assoc()) {
    $order_stats[$row['status']] = $row['count'];
}

$pending = $order_stats['pending'];
$completed = $order_stats['completed'];
$cancelled = $order_stats['cancelled'];

?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Advanced Pro Dark UI CSS */
    .glass-card {
        background: rgba(30, 41, 59, 0.7); /* Semi-transparent dark blue */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 40px rgba(56, 189, 248, 0.15); /* Blue Glow */
        border-color: rgba(56, 189, 248, 0.3);
    }

    .stat-icon-box {
        width: 55px; height: 55px;
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        box-shadow: inset 0 0 10px rgba(255,255,255,0.1);
    }

    .text-bright { color: #ffffff !important; }
    .text-secondary-light { color: #cbd5e1 !important; } /* Lighter Grey for readability */
    
    .btn-refresh {
        background: rgba(255,255,255,0.1);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        transition: 0.3s;
    }
    .btn-refresh:hover {
        background: rgba(255,255,255,0.2);
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5 mt-2">
        <div>
            <h2 class="fw-bold text-bright mb-1">Dashboard</h2>
            <p class="text-secondary-light mb-0 small">Real-time overview & analytics</p>
        </div>
        <button onclick="location.reload()" class="btn btn-refresh shadow-sm px-4 py-2 rounded-pill">
            <i class="fas fa-sync-alt me-2"></i> Refresh Data
        </button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card p-4 glass-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon-box bg-soft-primary text-primary" style="background: rgba(56, 189, 248, 0.1);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="badge bg-soft-success text-success">+ Daily</span>
                </div>
                <h6 class="text-secondary-light text-uppercase small fw-bold mb-1">Total Revenue</h6>
                <h3 class="fw-bold text-bright mb-0">SAR <?= number_format($total_revenue) ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 glass-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon-box text-white" style="background: rgba(255, 255, 255, 0.1);">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <span class="badge bg-light text-dark"><?= $total_orders ?> Total</span>
                </div>
                <h6 class="text-secondary-light text-uppercase small fw-bold mb-1">Total Orders</h6>
                <h3 class="fw-bold text-bright mb-0"><?= $total_orders ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 glass-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon-box text-info" style="background: rgba(14, 165, 233, 0.1);">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <span class="badge bg-soft-info text-info">Active</span>
                </div>
                <h6 class="text-secondary-light text-uppercase small fw-bold mb-1">Service Providers</h6>
                <h3 class="fw-bold text-bright mb-0"><?= $active_providers ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 glass-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="stat-icon-box text-success" style="background: rgba(34, 197, 94, 0.1);">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="badge bg-soft-success text-success">Verified</span>
                </div>
                <h6 class="text-secondary-light text-uppercase small fw-bold mb-1">Total Customers</h6>
                <h3 class="fw-bold text-bright mb-0"><?= $total_customers ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card glass-card h-100">
                <div class="card-header border-0 bg-transparent pt-4 px-4">
                    <h5 class="fw-bold m-0 text-bright">Revenue Analytics</h5>
                    <small class="text-secondary-light">Income over the last 7 days</small>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 320px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card glass-card h-100">
                <div class="card-header border-0 bg-transparent pt-4 px-4 text-center">
                    <h5 class="fw-bold m-0 text-bright">Order Status</h5>
                    <small class="text-secondary-light">Distribution overview</small>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center position-relative">
                    <div style="height: 260px; width: 260px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="position-absolute text-center" style="pointer-events: none;">
                        <h4 class="fw-bold text-bright mb-0"><?= $total_orders ?></h4>
                        <small class="text-secondary-light">Orders</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Config for Dark Theme Charts
    Chart.defaults.color = '#e2e8f0'; // Light text color for charts
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)'; // Very subtle grid lines
    Chart.defaults.font.family = "'Outfit', sans-serif";

    // 1. Revenue Chart
    const ctx1 = document.getElementById('revenueChart').getContext('2d');
    
    // Create Gradient
    let gradient = ctx1.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(56, 189, 248, 0.8)'); // Light Blue
    gradient.addColorStop(1, 'rgba(56, 189, 248, 0.1)'); // Fade out

    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue (SAR)',
                data: <?= json_encode($sales) ?>,
                backgroundColor: gradient,
                borderRadius: 6,
                barThickness: 25,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Status Chart
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [<?= $completed ?>, <?= $pending ?>, <?= $cancelled ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Thin ring style
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: '#e2e8f0' } }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>