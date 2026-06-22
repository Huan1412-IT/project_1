<?php
// 1. INCLUDE DB + SESSION
// =====================
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $HoTen = $_POST['HoTen'];
    $TenDangNhap = $_POST['TenDangNhap'];
    $MatKhau = password_hash($_POST['MatKhau'], PASSWORD_DEFAULT);
    $QuyenHan = $_POST['QuyenHan'];

    mysqli_query($conn, "
        INSERT INTO NguoiDung (HoTen, TenDangNhap, MatKhau, QuyenHan)
        VALUES ('$HoTen','$TenDangNhap','$MatKhau','$QuyenHan')
    ");

    header("Location: nguoi-dung.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Thêm người dùng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
<h4>➕ Thêm người dùng</h4>

<form method="POST" class="card p-4 shadow-sm mt-3">
    <div class="mb-3">
        <label>Họ tên</label>
        <input type="text" name="HoTen" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Tên đăng nhập</label>
        <input type="text" name="TenDangNhap" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Mật khẩu</label>
        <input type="password" name="MatKhau" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Quyền</label>
        <select name="QuyenHan" class="form-control">
            <option value="nhanvien">Nhân viên</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <button class="btn btn-success">Lưu</button>
    <br/>
    <a href="nguoi-dung.php" class="btn btn-secondary">Quay lại</a>
</form>
</div>

</body>
</html>
