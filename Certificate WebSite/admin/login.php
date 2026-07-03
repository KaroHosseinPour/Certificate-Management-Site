<?php
require_once '../db.php';
session_start();

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
$adminExists = $stmt->fetchColumn();


if(isset($_POST['create_admin'])){

    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

 
    if($password !== $password_confirm){

        $error = "رمزها یکسان نیستند";

    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, password, is_admin)
            VALUES (?,?,?,1)
        ");

        $stmt->execute([$full_name,$username,$hashed]);

        $_SESSION['success'] = "ادمین ساخته شد. وارد شوید";
        header("Location: login.php");
        exit;
    }
}


if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])){

        if($user['is_admin'] != 1){
            $error = "دسترسی ندارید";
        } else {
            $_SESSION['admin'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        }

    } else {
        $error = "نام کاربری یا رمز عبور اشتباه است";
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ورود به پنل</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#1f2937;
    min-height:100vh;
    display:flex;
    align-items:center;
}

.login-box{
    width:100%;
    max-width:420px;
    margin:auto;
}

.card{
    background:#374151;
    border:none;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,.35);
    color:white;
}

h3{
    color:#facc15;
    font-weight:700;
}

.form-control{
    background:#4b5563;
    border:none;
    color:white;
    padding:12px;
    border-radius:10px;
}

.form-control:focus{
    background:#4b5563;
    color:white;
    border:1px solid #facc15;
    box-shadow:none;
}

.form-label{
    color:#d1d5db;
    font-size:14px;
}

.btn-primary{
    background:#facc15 !important;
    border:none !important;
    color:#111827 !important;
    font-weight:600;
    padding:12px;
    border-radius:10px;
}

.btn-primary:hover{
    background:#eab308 !important;
}

.alert{
    background:#dc2626;
    border:none;
    color:white;
    border-radius:10px;
    font-size:14px;
}

.small-text{
    color:#9ca3af;
    font-size:12px;
    text-align:center;
    margin-top:10px;
}

</style>

</head>

<body>

<div class="container">

    <div class="login-box">

        <div class="card p-4">

<h3 class="text-center mb-4">
    <?php if($adminExists == 0): ?>
        ساخت ادمین اولیه
    <?php else: ?>
        ورود به پنل مدیریت
    <?php endif; ?>
</h3>

<?php if($adminExists == 0): ?>

<form method="POST">

<?php if(isset($error)): ?>
    <div class="alert text-center mb-2">
        <?= $error ?>
    </div>
<?php endif; ?>

    <label class="form-label">نام و نام خانوادگی</label>
    <input name="full_name" class="form-control mb-2" required>
    
    <label class="form-label">نام کاربری</label>
    <input name="username" class="form-control mb-2" required>
    
    <label class="form-label">رمز عبور</label>
    <input type="password" name="password" class="form-control mb-2" required>

    <label class="form-label">تکرار رمز عبور</label>
    <input type="password" name="password_confirm" class="form-control mb-2" required>

    <button type="submit" name="create_admin" class="btn btn-success w-100">
        ساخت ادمین
    </button>

</form>

<?php else: ?>

<form method="POST">

<?php if($success): ?>
    <div style="background:#22c55e;color:white;padding:10px;border-radius:10px;text-align:center;margin-bottom:10px;">
        <?= $success ?>
    </div>
<?php endif; ?>

    <div class="mb-3">
        <label class="form-label">نام کاربری</label>
        <input type="text" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">رمز عبور</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" name="login" class="btn btn-primary w-100">
        ورود
    </button>

</form>

<?php endif; ?>

            <div class="small-text mt-3">
                سیستم مدیریت استعلام مدارک
            </div>

        </div>

    </div>

</div>

</body>
</html>