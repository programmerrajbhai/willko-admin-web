<?php
session_start();
include 'config/db.php';

if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit(); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; 
    $password = $_POST['password'];
    
    // Check Email & Role
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 🔥 FIX: Check BOTH Hash AND Plain Text Password
        if (password_verify($password, $row['password']) || $password == $row['password']) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect Password!";
        }
    } else {
        $error = "Admin account not found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #0f172a; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .login-card { 
            width: 400px; 
            padding: 40px; 
            background: rgba(30, 41, 59, 0.7); /* Glass Effect */
            backdrop-filter: blur(10px);
            border-radius: 20px; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 0 50px rgba(56, 189, 248, 0.15); 
        }
        .form-control { 
            background: rgba(15, 23, 42, 0.8) !important; 
            border: 1px solid rgba(255, 255, 255, 0.1) !important; 
            color: white !important; 
            border-radius: 10px; 
            padding: 12px; 
        }
        .form-control:focus { 
            border-color: #38bdf8 !important; 
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3); 
        }
        .form-label { color: #cbd5e1; font-size: 0.9rem; margin-bottom: 5px; }
        .btn-primary { 
            background: linear-gradient(135deg, #38bdf8, #0ea5e9); 
            border: none; 
            color: #000; 
            font-weight: bold; 
            padding: 12px; 
            border-radius: 10px; 
            width: 100%; 
            transition: 0.3s;
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(56, 189, 248, 0.4); 
        }
        .text-logo {
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-logo">Wilko Admin</h3>
            <p class="text-secondary small">Sign in to manage your dashboard</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 text-center border-0 bg-danger bg-opacity-25 text-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary">Login Now</button>
        </form>
    </div>
</body>
</html>