<?php
include '../db.php';

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
