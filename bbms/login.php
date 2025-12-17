<?php
require_once 'db.php';
session_start();
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT admin_id, username, password_hash FROM admins WHERE username = ?');
    $stmt->bind_param('s',$username); $stmt->execute(); $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_username'] = $row['username'];
            $after = $_SESSION['after_login'] ?? '/bbms/index.php';
            unset($_SESSION['after_login']);
            header('Location: '.$after); exit;
        } else $err='Invalid username or password';
    } else $err='Invalid username or password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BBMS - Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/bbms/assets/style.css">
    <style>
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f5e6e8;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e0c7ca;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            background: #d4a5a5;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.3rem;
            margin: 0 auto 16px;
            box-shadow: 0 2px 8px rgba(212,165,165,0.2);
        }
        .login-header h2 {
            color: #333;
            margin: 0 0 8px;
            font-weight: 700;
            font-size: 1.6rem;
        }
        .login-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }
        .form-control {
            background: #fff;
            border: 1px solid #e0c7ca;
            color: #333;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: #fff;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255,107,157,0.1);
            color: #333;
        }
        .form-control::placeholder {
            color: #999;
        }
        .btn-login {
            width: 100%;
            padding: 12px 16px;
            background: #ff6b9d;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }
        .btn-login:hover {
            background: #ff5a8f;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(255,107,157,0.2);
        }
        .alert-danger {
            background: #fff5f5;
            color: #dc3545;
            border-left: 4px solid #dc3545;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }
        .login-footer a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .credentials-box {
            background: #f5e6e8;
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e0c7ca;
        }
        .credentials-box p {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 0.85rem;
        }
        .credentials-box code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            color: #ff6b9d;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .credentials-box p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">BB</div>
                <h2>Login</h2>
                <p>Enter your admin credentials</p>
            </div>
            
            <?php if($err): ?>
                <div class="alert-danger">
                    <i class="bi bi-exclamation-circle"></i> <?=$err?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            
            
        </div>
    </div>
</div>
</body>
</html>