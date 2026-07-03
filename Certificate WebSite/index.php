<?php
require_once 'db.php';
require_once 'includes/jdf.php';
$certificate = null;

if(isset($_GET['code']) && $_GET['code'] != ''){

    $code = trim($_GET['code']);

    $stmt = $pdo->prepare("SELECT * FROM certificates 
WHERE tracking_code = ? AND status = 'valid'");
    $stmt->execute([$code]);

    $certificate = $stmt->fetch();
    
    if($certificate){

    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];

    $stmt = $pdo->prepare("
        INSERT INTO certificate_views (certificate_id, ip_address, user_agent)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $certificate['id'],
        $ip,
        $ua
    ]);
}

}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>استعلام اصالت گواهی</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

<style>

body{
    background:#1f2937;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.verify-box{
    width:100%;
    max-width:500px;
}

.card{
    background:#374151;
    border:none;
    border-radius:24px;
    color:white;
    box-shadow:0 15px 40px rgba(0,0,0,.35);
}

.form-control{
    background:#4b5563;
    border:none;
    color:white;
    padding:14px;
}

.form-control:focus{
    background:#4b5563;
    color:white;
    border:1px solid #facc15;
    box-shadow:none;
}

.btn-warning{
    background:#facc15;
    border:none;
    color:#111827;
    font-weight:bold;
    padding:12px;
    border-radius:12px;
}

.btn-warning:hover{
    background:#eab308;
}

h3{
    color:#facc15;
    font-weight:bold;
}

small{
    color:#cbd5e1;
}

</style>

</head>

<body>

<div class="verify-box">

<div class="card p-4">

<h3 class="text-center mb-4">
استعلام اصالت گواهی
</h3>

<form method="GET">

<div class="mb-3">

<label class="form-label">
کد رهگیری
</label>

<input
type="text"
name="code"
class="form-control"
placeholder="مثال: KHP-26-F8A7-92BD"
required>

</div>

<button class="btn btn-warning w-100">
بررسی اعتبار
</button>

<div class="text-center mt-4">

<small>
کد رهگیری درج شده روی گواهی یا تقدیرنامه را وارد نمایید.
</small>

</div>

</form>
<?php if(isset($_GET['code'])): ?>

<hr class="my-4">

<?php if($certificate): ?>

<div class="alert alert-success">

<h5>✅ این مدرک معتبر است.</h5>

<hr>

<p><strong>نام دریافت کننده:</strong> <?= htmlspecialchars($certificate['recipient_name']) ?></p>

<p><strong>نوع مدرک:</strong> <?= htmlspecialchars($certificate['certificate_type']) ?></p>

<p><strong>موضوع:</strong> <?= htmlspecialchars($certificate['title']) ?></p>

<p><strong>امضاء کننده:</strong> <?= htmlspecialchars($certificate['issuer_name']) ?></p>

<p><strong>تاریخ صدور:</strong>
<?php
$date = date_create($certificate['issue_date']);
echo gregorian_to_jalali(
    date_format($date,'Y'),
    date_format($date,'m'),
    date_format($date,'d')
);
?>
</p>
<p><strong>کد رهگیری:</strong> <?= htmlspecialchars($certificate['tracking_code']) ?></p>

<?php if(!empty($certificate['image'])): ?>

<div style="margin-top:15px; text-align:center;">

    <img 
        src="<?= htmlspecialchars($certificate['image']) ?>" 
        style="max-width:100%; border-radius:12px; border:2px solid #facc15;"
    >

</div>

<?php endif; ?>


<a 
href="download.php?code=<?= urlencode($certificate['tracking_code']) ?>" 
class="btn btn-warning w-100 mt-3">

 دانلود تصویر گواهی

</a>


<?php else: ?>

<div class="alert alert-danger">

❌ مدرکی با این کد رهگیری یافت نشد.

</div>

<?php endif; ?>

<?php endif; ?>
</div>

</div>

</body>
</html>