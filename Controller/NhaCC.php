<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';
if (!isset($_SESSION['user']) || $_SESSION['quyen'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM NhaCungCap ORDER BY MaNCC DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nhà cung cấp</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>🚚 Quản lý Nhà cung cấp</h4>
        <a href="ncc-them.php" class="btn btn-success">➕ Thêm NCC</a>
    </div>

    <table class="table table-bordered table-hover shadow-sm">
        <thead class="table-success">
            <tr>
                <th>#</th>
                <th>Tên NCC</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th width="160">Thao tác</th>
            </tr>
        </thead>
        <style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.main-card{
    border:none;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header-custom{

    background:linear-gradient(135deg,#00b894,#2ecc71);

    color:white;

    padding:22px;
}

.table{

    border-collapse:separate;

    border-spacing:0 10px;

}

.table thead{

    background:#28a745;

    color:white;

}

.table thead th{

    border:none;

}

.table tbody tr{

    background:white;

    transition:.3s;

    box-shadow:0 3px 12px rgba(0,0,0,.05);

}

.table tbody tr:hover{

    transform:translateY(-2px);

    box-shadow:0 8px 20px rgba(0,0,0,.12);

}

.btn{

    border-radius:30px;

}

.form-control{

    border-radius:30px;

}

.stat-box{

    background:white;

    border-left:5px solid #28a745;

    border-radius:15px;

    padding:18px;

    box-shadow:0 4px 12px rgba(0,0,0,.06);

}

</style>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['MaNCC'] ?></td>
                <td><?= htmlspecialchars($row['TenNCC']) ?></td>
                <td><?= $row['SoDienThoai'] ?></td>
                <td><?= htmlspecialchars($row['DiaChi']) ?></td>
                <td>
                    <a href="ncc-sua.php?id=<?= $row['MaNCC'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="ncc-xoa.php?id=<?= $row['MaNCC'] ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Xóa nhà cung cấp này?')">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">Chưa có dữ liệu</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
