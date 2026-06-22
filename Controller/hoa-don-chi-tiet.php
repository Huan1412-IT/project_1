<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';
// ❌ Không gọi session_start() nếu db.php đã gọi rồi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập'); window.location.href='../login.php';</script>";
    exit();
}

$MaHD = $_GET['id'] ?? '';

if ($MaHD === '' || !ctype_digit($MaHD)) {
    echo "<div style='color:red;text-align:center'>❌ Mã hóa đơn không hợp lệ</div>";
    exit();
}

// ===== LẤY THÔNG TIN HÓA ĐƠN =====
$sqlHD = "
    SELECT h.MaHD, h.NgayTao, h.TongTien, n.HoTen
    FROM HoaDon h
    JOIN NguoiDung n ON h.MaND = n.MaND
    WHERE h.MaHD = ?
";
$stmtHD = mysqli_prepare($conn, $sqlHD);
mysqli_stmt_bind_param($stmtHD, "i", $MaHD);
mysqli_stmt_execute($stmtHD);
$resultHD = mysqli_stmt_get_result($stmtHD);
$hoadon = mysqli_fetch_assoc($resultHD);

if (!$hoadon) {
    echo "<div style='color:red;text-align:center'>❌ Không tìm thấy hóa đơn</div>";
    exit();
}

// ===== LẤY CHI TIẾT HÓA ĐƠN =====
$sqlCT = "
    SELECT s.TenSP, c.SoLuong, c.GiaBan
    FROM ChiTietHoaDon c
    JOIN LoHang l ON c.MaLo = l.MaLo
    JOIN SanPham s ON l.MaSP = s.MaSP
    WHERE c.MaHD = ?
";
$stmtCT = mysqli_prepare($conn, $sqlCT);
mysqli_stmt_bind_param($stmtCT, "i", $MaHD);
mysqli_stmt_execute($stmtCT);
$chitiet = mysqli_stmt_get_result($stmtCT);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết hóa đơn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-4">

<div class="text-center mb-4">
    <h3 class="fw-bold text-success">🧾 CHI TIẾT HÓA ĐƠN</h3>
    <p class="text-muted">Nhà thuốc ABC</p>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Mã hóa đơn:</strong> #<?= $hoadon['MaHD'] ?></p>
                <p><strong>Ngày lập:</strong>
                    <?= !empty($hoadon['NgayTao']) ? date('d/m/Y H:i', strtotime($hoadon['NgayTao'])) : '---' ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Nhân viên:</strong> <?= $hoadon['HoTen'] ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead class="table-success text-center">
                <tr>
                    <th>#</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $tong = 0;
                while ($row = mysqli_fetch_assoc($chitiet)): 
                    $thanhtien = $row['SoLuong'] * $row['GiaBan'];
                    $tong += $thanhtien;
                ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><?= $row['TenSP'] ?></td>
                    <td class="text-center"><?= $row['SoLuong'] ?></td>
                    <td class="text-end"><?= number_format($row['GiaBan']) ?> đ</td>
                    <td class="text-end fw-bold"><?= number_format($thanhtien) ?> đ</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="card border-success shadow-sm">
            <div class="card-body text-end">
                <h5>Tổng thanh toán</h5>
                <h3 class="text-danger fw-bold">
                    <?= number_format($hoadon['TongTien'] ?? $tong) ?> đ
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="hoa-don.php" class="btn btn-secondary">⬅ Quay lại</a>
    <button onclick="window.print()" class="btn btn-success">🖨 In hóa đơn</button>
</div>

</div>
</body>
</html>
