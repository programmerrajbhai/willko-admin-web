<?php
session_start();
include 'config/db.php'; // ডাটাবেস কানেকশন ফাইল

// যদি আগে থেকেই অ্যাডমিন লগইন করা থাকে, তবে ড্যাশবোর্ডে পাঠিয়ে দিন
if (isset($_SESSION['admin_id'])) { 
    header("Location: dashboard.php"); 
    exit(); 
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']); 
    $password = $_POST['password'];
    
    // SQL Injection প্রতিরোধের জন্য Prepared Statement ব্যবহার করা হয়েছে
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 🔥 ফিক্সড লজিক: শুধুমাত্র password_verify ব্যবহার করা হয়েছে
        // প্লেন টেক্সট পাসওয়ার্ড ($password == $row['password']) তুলনা করা সম্পূর্ণ বাদ দেওয়া হয়েছে
        if (password_verify($password, $row['password'])) {
            
            // সেশন ফিক্সেশন প্রোটেকশনের জন্য সেশন আইডি পরিবর্তন
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
        }
    } else {
        $error = "এই ইমেইল দিয়ে কোনো অ্যাডমিন অ্যাকাউন্ট পাওয়া যায়নি!";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Wilko Admin - Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-card { width: 400px; padding: 40px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 0 50px rgba(56, 189, 248, 0.15); }
        .form-control { background: rgba(15, 23, 42, 0.8) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; color: white !important; border-radius: 10px; padding: 12px; }
        .form-control:focus { border-color: #38bdf8 !important; box-shadow: 0 0 10px rgba(56, 189, 248, 0.3); }
        .btn-primary { background: linear-gradient(135deg, #38bdf8, #0ea5e9); border: none; color: #000; font-weight: bold; padding: 12px; border-radius: 10px; width: 100%; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(56, 189, 248, 0.4); }
        .text-logo { background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-logo">Wilko Admin</h3>
            <p class="text-secondary small">সুরক্ষিতভাবে লগইন করুন</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 text-center border-0 bg-danger bg-opacity-25 text-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-white">ইমেইল ঠিকানা</label>
                <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-white">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary">লগইন করুন</button>
        </form>
    </div>
</body>
</html>