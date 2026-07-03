<?php
require_once '../db.php';
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM certificates WHERE id=?");
$stmt->execute([$id]);
$cert = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT * FROM certificate_views
    WHERE certificate_id=?
    ORDER BY viewed_at DESC
");
$stmt->execute([$id]);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<title>گزارش بازدید</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#111827;
    color:#e5e7eb;
    font-family:tahoma;
    margin:0;
    padding:0;
}

.container{
    width:100%;
    max-width:100%;
    padding:16px;
}

.card{
    background:#1f2937;
    border:1px solid #2b3647;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(0,0,0,.25);
    color:#e5e7eb;
}

h4{
    color:#fbbf24;
    font-weight:700;
    letter-spacing:.3px;
}

.count-box{
    display:inline-block;
    margin-top:10px;
    padding:6px 12px;
    border-radius:10px;
    background:#0f172a;
    border:1px solid rgba(251,191,36,.4);
    color:#fbbf24;
    font-weight:bold;
    font-size:13px;
}

.log-card{
    background:#111827;
    border:1px solid #243244;
    border-radius:14px;
    padding:12px;
    margin-bottom:12px;
    transition:.2s;
}

.log-card:hover{
    transform:translateY(-2px);
    border-color:rgba(251,191,36,.4);
}

.log-item{
    display:flex;
    justify-content:space-between;
    padding:8px 0;
    border-bottom:1px solid #1f2937;
}

.log-item:last-child{
    border-bottom:none;
}

.label{
    color:#fbbf24;
    font-weight:600;
    font-size:13px;
}

.value{
    font-size:13px;
    color:#e5e7eb;
    word-break:break-word;
    text-align:left;
}

.ip{
    color:#fbbf24;
    font-weight:bold;
}

.ua{
    font-size:11px;
    color:#9ca3af;
}

@media (max-width:768px){

    .log-item{
        flex-direction:column;
        gap:4px;
    }

    .value{
        text-align:right;
    }
}

</style>

</head>

<body>

<div class="container py-4">

<h4>📊 گزارش بازدید گواهی</h4>

<div class="card p-3 mb-3">

<p><strong>کد:</strong> <?= $cert['tracking_code'] ?></p>
<p><strong>نام:</strong> <?= $cert['recipient_name'] ?></p>

<div class="count-box">
👁️ تعداد بازدید: <?= count($logs) ?>
</div>

</div>

<div class="card p-3">

<?php if($logs): ?>

    <?php foreach($logs as $log): ?>

        <div class="log-card">

            <div class="log-item">
                <span class="label">IP</span>
                <span class="value ip"><?= $log['ip_address'] ?></span>
            </div>

            <div class="log-item">
                <span class="label">زمان</span>
                <span class="value"><?= $log['viewed_at'] ?></span>
            </div>

            <div class="log-item">
                <span class="label">User Agent</span>
                <span class="value"><?= htmlspecialchars($log['user_agent']) ?></span>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="text-center text-warning">
        هیچ بازدیدی ثبت نشده
    </div>

<?php endif; ?>

</div>

</div>

</body>
</html>