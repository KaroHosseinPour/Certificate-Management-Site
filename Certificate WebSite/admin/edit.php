<?php
require_once '../db.php';
require_once '../includes/jdf.php';
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM certificates WHERE id=?");
$stmt->execute([$id]);
$cert = $stmt->fetch();

if(!$cert){
    die("گواهی پیدا نشد.");
}


if(isset($_POST['save'])){

    $recipient_name   = trim($_POST['recipient_name']);
    $certificate_type = trim($_POST['certificate_type']);
    $title            = trim($_POST['title']);
    $issuer_name      = trim($_POST['issuer_name']);
    $status           = $_POST['status'];

    $issue_date = $cert['issue_date'];

    if(!empty($_POST['issue_date'])){

        $p = explode('/', $_POST['issue_date']);

        if(count($p)==3){
            $issue_date = jalali_to_gregorian($p[0],$p[1],$p[2],'-');
        }
    }

    $image = $cert['image'];

    if(!empty($_FILES['image']['name'])){

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $newName = time().'_'.bin2hex(random_bytes(3)).'.'.$ext;

        $path = "../uploads/certificates/";

        if(!is_dir($path)){
            mkdir($path,0777,true);
        }

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $path.$newName
        );

        $image = "uploads/certificates/".$newName;
    }

    $stmt = $pdo->prepare("
        UPDATE certificates
        SET
        recipient_name=?,
        certificate_type=?,
        title=?,
        issuer_name=?,
        issue_date=?,
        status=?,
        image=?
        WHERE id=?
    ");

    $stmt->execute([
        $recipient_name,
        $certificate_type,
        $title,
        $issuer_name,
        $issue_date,
        $status,
        $image,
        $id
    ]);

    header("Location: edit.php?id=".$id."&success=1");
    exit;
}

list($gy,$gm,$gd)=explode('-',$cert['issue_date']);

$jalaliDate = gregorian_to_jalali(
    $gy,
    $gm,
    $gd,
    '/'
);
?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ویرایش گواهی</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#1f2937;
    color:white;
    font-family:tahoma;
}

.card{
    background:#374151;
    border:none;
    border-radius:22px;
    color:white;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
}

.form-control,
.form-select{

    background:#4b5563;
    border:none;
    color:white !important;

}

.form-control::placeholder{

    color:#d1d5db;

}

.form-control:focus,
.form-select:focus{

    background:#4b5563;
    color:white !important;
    box-shadow:none;
    border:1px solid #facc15;

}

.btn-yellow{

    background:#facc15;
    color:#111827;
    border:none;
    font-weight:bold;
    border-radius:10px;

}

.btn-yellow:hover{

    background:#eab308;

}

.container .card{

    margin-bottom:18px;

}

img.preview{

    max-width:220px;
    border-radius:12px;
    margin-top:10px;

}

</style>

</head>

<body>

<div class="container py-4">

<h3 class="mb-4">
✏️ ویرایش گواهی
</h3>

<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success text-center">

ویرایش با موفقیت انجام شد.

</div>

<?php endif; ?>

<div class="card p-4">

<form method="POST" enctype="multipart/form-data">
<div class="row g-3">

    <div class="col-md-6">
        <label class="mb-2">نام دریافت‌کننده</label>
        <input
            type="text"
            name="recipient_name"
            class="form-control"
            value="<?= htmlspecialchars($cert['recipient_name']) ?>"
            required>
    </div>

    <div class="col-md-6">
        <label class="mb-2">نوع مدرک</label>
        <input
            type="text"
            name="certificate_type"
            class="form-control"
            value="<?= htmlspecialchars($cert['certificate_type']) ?>"
            required>
    </div>

    <div class="col-md-6">
        <label class="mb-2">موضوع</label>
        <input
            type="text"
            name="title"
            class="form-control"
            value="<?= htmlspecialchars($cert['title']) ?>"
            required>
    </div>

    <div class="col-md-6">
        <label class="mb-2">امضاء کننده</label>
        <input
            type="text"
            name="issuer_name"
            class="form-control"
            value="<?= htmlspecialchars($cert['issuer_name']) ?>"
            required>
    </div>

    <div class="col-md-6">
        <label class="mb-2">تاریخ (شمسی)</label>
        <input
            type="text"
            name="issue_date"
            class="form-control"
            value="<?= $jalaliDate ?>"
            placeholder="1404/05/12"
            required>
    </div>

    <div class="col-md-6">
        <label class="mb-2">وضعیت</label>

        <select
            name="status"
            class="form-select">

            <option
                value="valid"
                <?= $cert['status']=='valid' ? 'selected' : '' ?>>
                معتبر
            </option>

            <option
                value="revoked"
                <?= $cert['status']=='revoked' ? 'selected' : '' ?>>
                غیرفعال
            </option>

        </select>

    </div>

    <div class="col-md-12">

        <label class="mb-2">
            تصویر جدید (اختیاری)
        </label>

        <input
            type="file"
            name="image"
            class="form-control">

    </div>

    <?php if(!empty($cert['image'])): ?>

    <div class="col-md-12">

        <label class="mb-2">
            تصویر فعلی
        </label>

        <br>

        <img
            class="preview"
            src="../<?= $cert['image'] ?>">

    </div>

    <?php endif; ?>

</div>

<div class="mt-4">

    <button
        class="btn btn-yellow w-100"
        name="save">

        ذخیره تغییرات

    </button>

</div>
</form>

</div>

<div class="mt-3">

    <a
        href="dashboard.php"
        class="btn btn-secondary">

        ← بازگشت به داشبورد

    </a>

</div>

</div>

</body>

</html>