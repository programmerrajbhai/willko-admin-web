<?php
session_start();
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'index.php') {
    header("Location: index.php");
    exit();
}
include __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wilko Admin Pro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0f172a;       /* Deep Blue/Black Background */
            --bg-card: #1e293b;       /* Lighter Card Background */
            --text-main: #f1f5f9;     /* White Text */
            --text-muted: #94a3b8;    /* Grey Text */
            --primary: #38bdf8;       /* Neon Blue */
            --secondary: #818cf8;     /* Soft Purple */
            --border: #334155;        /* Dark Border */
            --sidebar-width: 260px;
        }

        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: var(--bg-dark); 
            color: var(--text-main);
            overflow-x: hidden; 
        }
        
        /* Sidebar Styling */
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            background: var(--bg-card); 
            border-right: 1px solid var(--border); 
            z-index: 1000; 
            padding: 20px 0; 
            transition: 0.3s; 
        }
        .sidebar-brand { padding: 0 24px 30px; font-size: 24px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .nav-item { padding: 0 12px; margin-bottom: 4px; }
        .nav-link { color: var(--text-muted); padding: 12px 16px; border-radius: 12px; font-weight: 500; font-size: 15px; display: flex; align-items: center; gap: 12px; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(56, 189, 248, 0.1); color: var(--primary); box-shadow: 0 0 15px rgba(56, 189, 248, 0.1); }
        .nav-link i { width: 20px; font-size: 18px; }

        /* Content Area */
        .main-content { margin-left: var(--sidebar-width); padding: 30px; transition: 0.3s; }
        
        /* Dark Cards */
        .card { 
            border: 1px solid var(--border); 
            border-radius: 16px; 
            background: var(--bg-card); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.2); 
            margin-bottom: 24px; 
            overflow: hidden; 
        }
        .card-header { 
            background: rgba(30, 41, 59, 0.5); 
            border-bottom: 1px solid var(--border); 
            padding: 20px 24px; 
            font-weight: 700; 
            font-size: 16px; 
            color: var(--text-main);
        }
        
        /* Inputs & Forms */
        .form-control, .form-select {
            background-color: #0f172a;
            border: 1px solid var(--border);
            color: var(--text-main);
            border-radius: 10px;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a;
            border-color: var(--primary);
            color: var(--text-main);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }
        .form-control::placeholder { color: #475569; }

        /* Tables */
        .table { color: var(--text-muted); }
        .table thead th { 
            background: #0f172a; 
            color: var(--text-main); 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 12px; 
            border-bottom: 1px solid var(--border); 
            padding: 15px;
        }
        .table tbody td { 
            padding: 15px; 
            vertical-align: middle; 
            border-bottom: 1px solid var(--border); 
            background: var(--bg-card);
            color: var(--text-main);
        }
        .table-hover tbody tr:hover td { background-color: #1e3a8a !important; color: white; }

        /* Badges */
        .badge { padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 700; }
        .bg-soft-primary { background: rgba(56, 189, 248, 0.15); color: var(--primary); }
        .bg-soft-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
        .bg-soft-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .bg-soft-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; }

        /* Buttons */
        .btn-primary { background: var(--primary); border: none; color: #000; font-weight: 600; }
        .btn-primary:hover { background: #0ea5e9; color: #fff; box-shadow: 0 0 15px var(--primary); }
        .btn-light { background: #334155; color: white; border: 1px solid var(--border); }
        .btn-light:hover { background: #475569; color: white; }

        /* Text Utils */
        .text-dark { color: var(--text-main) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .bg-white { background-color: var(--bg-card) !important; }
        .bg-light { background-color: #0f172a !important; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>