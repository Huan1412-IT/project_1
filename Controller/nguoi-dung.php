<?php
// 1. INCLUDE DB + SESSION
// =====================
include '../db.php';

// =====================
// 2. KIỂM TRA ĐĂNG NHẬP
// =====================
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='../login.php';</script>";
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM NguoiDung ORDER BY MaND DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý người dùng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>👥 Quản lý người dùng</h4>
        <a href="them-nguoi-dung.php" class="btn btn-success">➕ Thêm người dùng</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Tên đăng nhập</th>
                <th>Quyền</th>
                <th width="150">Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php while($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $u['MaND'] ?></td>
                <td><?= $u['HoTen'] ?></td>
                <td><?= $u['TenDangNhap'] ?></td>
                <td>
                    <span class="badge bg-<?= $u['QuyenHan']=='admin'?'danger':'primary' ?>">
                        <?= strtoupper($u['QuyenHan']) ?>
                    </span>
                </td>
                <td>
                    <a href="sua-nguoi-dung.php?id=<?= $u['MaND'] ?>" class="btn btn-warning btn-sm">✏️</a>
                    <a href="xoa-nguoi-dung.php?id=<?= $u['MaND'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Xóa người dùng này?')">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
