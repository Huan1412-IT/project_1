<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['quyen'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM NguoiDung WHERE MaND=$id"));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa người dùng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f0f2f5;
}
.edit-card {
    border-radius: 20px;
    border: none;
}
.edit-card .card-header {
    background: linear-gradient(135deg, #198754, #20c997);
    color: white;
    border-radius: 20px 20px 0 0;
}
.form-control, .form-select {
    border-radius: 10px;
}
</style>
</head>

<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card edit-card shadow-lg">
                <div class="card-header text-center py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Cập nhật thông tin người dùng
                    </h5>
                </div>

                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-success"></i>
                        <p class="mt-2 fw-bold"><?= $user['TenDangNhap'] ?></p>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-card me-1"></i> Họ tên
                            </label>
                            <input type="text" name="HoTen"
                                   value="<?= $user['HoTen'] ?>"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user-shield me-1"></i> Quyền hạn
                            </label>
                            <select name="QuyenHan" class="form-select">
                                <option value="nhanvien" <?= $user['QuyenHan']=='nhanvien'?'selected':'' ?>>
                                    Nhân viên
                                </option>
                                <option value="admin" <?= $user['QuyenHan']=='admin'?'selected':'' ?>>
                                    Admin
                                </option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="nguoi-dung.php" class="btn btn-outline-secondary px-4 rounded-pill">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>

                            <button class="btn btn-success px-4 rounded-pill">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
