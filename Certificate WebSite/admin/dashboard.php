<?php
require_once '../db.php';
require_once '../includes/jdf.php';
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

/* ================= DELETE ================= */
if(isset($_GET['delete'])){
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: dashboard.php");
    exit;
}

/* ================= TOGGLE ================= */
if(isset($_GET['toggle'])){
    $stmt = $pdo->prepare("UPDATE certificates SET status = IF(status='valid','revoked','valid') WHERE id=?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: dashboard.php");
    exit;
}

/* ================= ADD CERT ================= */
if(isset($_POST['add'])){

    $tracking_code = trim($_POST['tracking_code']);
    $recipient_name = trim($_POST['recipient_name']);
    $certificate_type = trim($_POST['certificate_type']);
    $title = trim($_POST['title']);
    $issuer_name = trim($_POST['issuer_name']);
    $status = 'valid';

    /* date jalali → gregorian */
    $issue_date = null;
    if(!empty($_POST['issue_date'])){
        $p = explode('/', $_POST['issue_date']);
        if(count($p) == 3){
            $issue_date = jalali_to_gregorian($p[0],$p[1],$p[2],'-');
        }
    }

    /* upload */
    $image = '';
    if(!empty($_FILES['image']['name'])){
        $name = time().'_'.bin2hex(random_bytes(3)).'.'.pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $path = "../uploads/certificates/";

        if(!is_dir($path)){
            mkdir($path,0777,true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'],$path.$name);
        $image = "uploads/certificates/".$name;
    }

    $stmt = $pdo->prepare("
        INSERT INTO certificates
        (tracking_code, recipient_name, certificate_type, title, issuer_name, issue_date, status, image)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $tracking_code,$recipient_name,$certificate_type,$title,$issuer_name,$issue_date,$status,$image
    ]);

    header("Location: dashboard.php?success=1");
    exit;
}

/* ================= SEARCH ================= */
$where = "WHERE 1=1";
$params = [];

if(!empty($_GET['name'])){
    $where .= " AND recipient_name LIKE ?";
    $params[] = "%".$_GET['name']."%";
}

if(!empty($_GET['from']) && !empty($_GET['to'])){
    $where .= " AND issue_date BETWEEN ? AND ?";
    $params[] = $_GET['from'];
    $params[] = $_GET['to'];
}

if(isset($_GET['status']) && $_GET['status']!=''){
    $where .= " AND status=?";
    $params[] = $_GET['status'];
}

$perPage = 20;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$offset = ($page - 1) * $perPage;


$countStmt = $pdo->prepare("SELECT COUNT(*) FROM certificates $where");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

$totalPages = ceil($totalRows / $perPage);

$stmt = $pdo->prepare("
    SELECT *
    FROM certificates
    $where
    ORDER BY id DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;400;600;700&display=swap" rel="stylesheet">

<title>داشبورد گواهی‌ها</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#1f2937;
    color:white;
    font-family:'Vazirmatn', Tahoma, sans-serif;
}

.card{
    background:#374151;
    border:none;
    border-radius:22px;
    color:white;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
}

.form-control,.form-select{
    background:#4b5563;
    border:none;
    color:white !important;
}

.form-control::placeholder{
    color:#d1d5db;
}

.form-control:focus,.form-select:focus{
    background:#4b5563;
    color:white !important;
    border:1px solid #facc15;
    box-shadow:none;
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


.table{
    color:#e5e7eb !important;
}

.table th{
    background:#4b5563 !important;
    color:#facc15 !important;
    border-color:#6b7280 !important;
}

.table td{
    color:#e5e7eb !important;
    background:#424b5c !important;
    border-color:#6b7280 !important;
}

.table tr:hover td{
    background:#4b5563 !important;
}

.small-btn{
    font-size:12px;
    padding:4px 8px;
    border-radius:8px;
}

.container .card{
    margin-bottom:18px;
}

.pagination .page-link{
    background:#374151;
    color:#facc15;
    border:1px solid #4b5563;
}

.pagination .page-link:hover{
    background:#4b5563;
    color:white;
}

.pagination .active .page-link{
    background:#facc15;
    color:#111827;
    border-color:#facc15;
}

@media(max-width:768px){
    .table-responsive{
        overflow-x:auto;
    }
}

</style>
</head>

<body>

<div class="container py-4">

<h3 class="mb-3">داشبورد مدیریت گواهی‌ها</h3>
<?php if(isset($_GET['success'])): ?>
    <div style="
        background:#22c55e;
        color:white;
        padding:12px;
        border-radius:12px;
        margin-bottom:15px;
        text-align:center;
        font-weight:bold;
    ">
        ✅ گواهی با موفقیت ثبت شد
    </div>
<?php endif; ?>

<!-- ADD -->
<div class="card p-4 mb-4">

<h5>افزودن گواهی</h5>

<form method="POST" enctype="multipart/form-data">

<div class="row g-3">

<div class="col-md-4">
    <div class="input-group">

        <input
            id="tracking_code"
            name="tracking_code"
            class="form-control"
            placeholder="کد رهگیری"
            readonly
            required>

        <button
            type="button"
            class="btn btn-yellow"
            onclick="generateCode()">
            🎲
        </button>

    </div>
</div>

<div class="col-md-4">
<input name="recipient_name" class="form-control" placeholder="نام دریافت کننده" required>
</div>

<div class="col-md-4">
<input name="certificate_type" class="form-control" placeholder="نوع گواهی" required>
</div>

<div class="col-md-4">
<input name="title" class="form-control" placeholder="موضوع گواهی" required>
</div>

<div class="col-md-4">
<input name="issuer_name" class="form-control" placeholder="نام امضا کننده" required>
</div>

<div class="col-md-4">
<input name="issue_date" class="form-control" placeholder="1404/05/12 : تاریخ" required>
</div>

<div class="col-md-4">
<input type="file" name="image" class="form-control">
</div>

</div>

<button class="btn btn-yellow w-100 mt-3" name="add">ثبت</button>

</form>

</div>

<!-- SEARCH -->
<div class="card p-4 mb-4">

<h5>جستجو</h5>

<form method="GET">

<div class="row g-3">

<div class="col-md-4">
<input name="name" class="form-control" placeholder="نام">
</div>


</div>

<button class="btn btn-yellow w-100 mt-3">جستجو</button>

</form>

</div>

<!-- LIST -->
<div class="card p-4">

<h5>لیست</h5>

<div class="table-responsive">

<table class="table align-middle">

<thead>
<tr>
<th>کد</th>
<th>نام</th>
<th>موضوع</th>
<th>تاریخ</th>
<th>وضعیت</th>
<th>عملیات</th>
</tr>
</thead>

<tbody>

<?php foreach($list as $row): ?>

<tr>

<td><?= $row['tracking_code'] ?></td>
<td><?= $row['recipient_name'] ?></td>
<td><?= $row['title'] ?></td>
<td>
<?= gregorian_to_jalali(
    date('Y', strtotime($row['issue_date'])),
    date('m', strtotime($row['issue_date'])),
    date('d', strtotime($row['issue_date'])),
    '/'
) ?>
</td>

<td>
<span class="badge <?= $row['status']=='valid'?'badge-valid':'badge-revoked' ?>">
<?= $row['status']=='valid'?'معتبر':'غیرفعال' ?>
</span>
</td>

<td style="white-space:nowrap;">
<a class="btn btn-primary btn-sm"
href="edit.php?id=<?= $row['id'] ?>">
ویرایش
</a>
<a class="btn btn-warning btn-sm" href="?toggle=<?= $row['id'] ?>">تغییر</a>
<a class="btn btn-danger btn-sm" href="?delete=<?= $row['id'] ?>">حذف</a>
<a class="btn btn-info btn-sm" href="logs.php?id=<?= $row['id'] ?>">گزارش</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php if($totalPages > 1): ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<li class="page-item <?= $i==$page ? 'active' : '' ?>">

<a class="page-link"
href="?page=<?= $i ?>">
<?= $i ?>
</a>

</li>

<?php endfor; ?>

</ul>

</nav>

<?php endif; ?>

</div>

</div>

</div>

<script>

function randomPart(length){
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    let result = "";
    for(let i=0;i<length;i++){
        result += chars.charAt(Math.floor(Math.random()*chars.length));
    }
    return result;
}

function generateCode(){

    const year = new Date().getFullYear().toString().slice(-2);

    const code =
        "KHP-" +
        year +
        "-" +
        randomPart(4) +
        "-" +
        randomPart(4);

    document.getElementById("tracking_code").value = code;
}

window.onload = generateCode;


const trackingInput = document.getElementById("tracking_code");

trackingInput.addEventListener("click", function(){

    this.select();
    this.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(this.value);

    showToast("✅ کد رهگیری کپی شد");

});


function showToast(text){

    let toast = document.createElement("div");

    toast.innerHTML = text;

    toast.style.position = "fixed";
    toast.style.bottom = "25px";
    toast.style.left = "50%";
    toast.style.transform = "translateX(-50%)";
    toast.style.background = "#22c55e";
    toast.style.color = "#fff";
    toast.style.padding = "10px 18px";
    toast.style.borderRadius = "12px";
    toast.style.fontWeight = "bold";
    toast.style.boxShadow = "0 8px 20px rgba(0,0,0,.3)";
    toast.style.zIndex = "99999";
    toast.style.opacity = "0";
    toast.style.transition = ".3s";

    document.body.appendChild(toast);

    setTimeout(()=>{
        toast.style.opacity = "1";
    },10);

    setTimeout(()=>{
        toast.style.opacity = "0";

        setTimeout(()=>{
            toast.remove();
        },300);

    },1800);

}

</script>

</body>
</html>