<?php
include 'config/db.php'; // ডাটাবেস কানেকশন 

// আপনার অ্যাডমিনের ইমেইল এবং নতুন পাসওয়ার্ড
$admin_email = 'admin@gmail.com';
$new_password = '111111';

// পাসওয়ার্ডটি সিকিউরভাবে হ্যাশ করা হচ্ছে
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// ডাটাবেস আপডেট করা হচ্ছে
$sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$admin_email' AND role = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "<h2 style='color: green; text-align: center; margin-top: 50px;'>✅ Admin password successfully updated and hashed!</h2>";
    echo "<p style='text-align: center;'>You can now login using Email: <b>$admin_email</b> and Password: <b>$new_password</b></p>";
    echo "<p style='text-align: center;'><a href='index.php'>Click here to Login</a></p>";
} else {
    echo "<h2 style='color: red;'>Error updating password: " . $conn->error . "</h2>";
}
?>