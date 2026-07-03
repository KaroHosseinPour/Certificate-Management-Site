<?php
require_once 'db.php';

if(!isset($_GET['code'])) {
    die("Invalid request");
}

$code = $_GET['code'];

$stmt = $pdo->prepare("SELECT image FROM certificates WHERE tracking_code = ?");
$stmt->execute([$code]);
$row = $stmt->fetch();

if(!$row) {
    die("Not found");
}

$file = $row['image'];

if(!file_exists($file)) {
    die("File not found");
}

// Force download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;