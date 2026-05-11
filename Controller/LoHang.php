<?php

include '../db.php';



// 2. KIỂM TRA ĐĂNG NHẬP
// =====================
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='../login.php';</script>";
    exit();
}

$sql = "
SELECT l.*, s.TenSP, n.TenNCC
FROM LoHang l
JOIN SanPham s ON l.MaSP = s.MaSP
JOIN NhaCungCap n ON l.MaNCC = n.MaNCC
ORDER BY l.NgayHetHan ASC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý lô hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📦 Quản lý lô hàng (HSD)</h4>
        <a href="ThemLH.php" class="btn btn-success">➕ Thêm lô hàng</a>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-success text-center">
            <tr>
                <th>Sản phẩm</th>
                <th>Nhà cung cấp</th>
                <th>Giá nhập</th>
                <th>Số lượng tồn</th>
                <th>Ngày SX</th>
                <th>Hạn sử dụng</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):

            $days = (strtotime($row['NgayHetHan']) - time()) / 86400;
            if ($days < 0) {
                $status = "<span class='badge bg-danger'>Hết hạn</span>";
            } elseif ($days <= 30) {
                $status = "<span class='badge bg-warning text-dark'>Sắp hết hạn</span>";
            } else {
                $status = "<span class='badge bg-success'>Còn hạn</span>";
            }
        ?>
            <tr class="text-center">
                <td><?= htmlspecialchars($row['TenSP']) ?></td>
                <td><?= htmlspecialchars($row['TenNCC']) ?></td>
                <td><?= number_format($row['GiaNhap'],0,',','.') ?>đ</td>
                <td><?= $row['SoLuongTon'] ?></td>
                <td><?= $row['NgaySanXuat'] ?></td>
                <td><?= $row['NgayHetHan'] ?></td>
                <td><?= $status ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
