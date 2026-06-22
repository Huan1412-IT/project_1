<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['quyen'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['TenNCC'];
    $sdt = $_POST['SoDienThoai'];
    $diachi = $_POST['DiaChi'];

    mysqli_query($conn, "INSERT INTO NhaCungCap (TenNCC, SoDienThoai, DiaChi)
                         VALUES ('$ten','$sdt','$diachi')");

    header("Location: NhaCC.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm NCC</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h4>➕ Thêm nhà cung cấp</h4>

    <form method="POST" class="card p-4 shadow-sm mt-3">
        <div class="mb-3">
            <label>Tên nhà cung cấp</label>
            <input type="text" name="TenNCC" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="SoDienThoai" class="form-control">
        </div>

        <div class="mb-3">
            <label>Địa chỉ</label>
            <textarea name="DiaChi" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="nha-cung-cap.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

</body>
</html>
