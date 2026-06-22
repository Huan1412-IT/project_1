<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';
$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM NhaCungCap WHERE MaNCC=$id"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['TenNCC'];
    $sdt = $_POST['SoDienThoai'];
    $diachi = $_POST['DiaChi'];

    mysqli_query($conn, "UPDATE NhaCungCap 
                         SET TenNCC='$ten', SoDienThoai='$sdt', DiaChi='$diachi'
                         WHERE MaNCC=$id");

    header("Location: NhaCC.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa NCC</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h4>✏️ Sửa nhà cung cấp</h4>

    <form method="POST" class="card p-4 shadow-sm mt-3">
        <div class="mb-3">
            <label>Tên NCC</label>
            <input type="text" name="TenNCC" value="<?= $data['TenNCC'] ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>SĐT</label>
            <input type="text" name="SoDienThoai" value="<?= $data['SoDienThoai'] ?>" class="form-control">
        </div>

        <div class="mb-3">
            <label>Địa chỉ</label>
            <textarea name="DiaChi" class="form-control"><?= $data['DiaChi'] ?></textarea>
        </div>

        <button class="btn btn-warning">Cập nhật</button>
        <a href="nha-cung-cap.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

</body>
</html>
