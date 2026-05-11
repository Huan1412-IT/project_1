<?php
session_start();
include '../db.php';

// 1. Lấy ID từ URL an toàn
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Truy vấn thông tin đơn bán hàng
$sql_hd = "SELECT h.*, n.HoTen FROM HoaDon h 
           LEFT JOIN NguoiDung n ON h.MaND = n.MaND 
           WHERE h.MaHD = $id";
$res_hd = mysqli_query($conn, $sql_hd);
$hd = mysqli_fetch_assoc($res_hd);

// KIỂM TRA: Nếu không có dữ liệu, thông báo lỗi thay vì hiện trang trắng hoặc lỗi null
if (!$hd) {
    die("<div class='container mt-5 alert alert-danger text-center'>
            <h4>Lỗi: Không tìm thấy đơn bán hàng #$id</h4>
            <a href='ban-hang.php' class='btn btn-primary mt-3'>Quay lại bán hàng</a>
         </div>");
}

// 3. Truy vấn danh sách thuốc trong đơn này
$sql_ct = "SELECT ct.*, s.TenSP FROM ChiTietHoaDon ct 
           JOIN SanPham s ON ct.MaSP = s.MaSP 
           WHERE ct.MaHD = $id";
$res_ct = mysqli_query($conn, $sql_ct);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bán hàng chi tiết #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box { border: 1px solid #eee; padding: 30px; border-radius: 10px; background: #fff; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="invoice-box shadow-sm mx-auto" style="max-width: 800px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-success">🧾 BÁN HÀNG CHI TIẾT</h2>
            <p class="text-muted">Nhà thuốc ABC - Đơn hàng #<?= $hd['MaHD'] ?></p>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <p class="mb-1 text-muted">Mã đơn hàng:</p>
                <h5 class="fw-bold">#<?= $hd['MaHD'] ?></h5>
                <p class="mb-1 text-muted">Ngày lập:</p>
                <h6><?= date('d/m/Y H:i', strtotime($hd['NgayLap'])) ?></h6>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1 text-muted">Nhân viên bán hàng:</p>
                <h6 class="fw-bold"><?= htmlspecialchars($hd['HoTen'] ?? 'N/A') ?></h6>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>Tên sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                $tong_cong = 0;
                while($item = mysqli_fetch_assoc($res_ct)): 
                    $thanhtien = $item['SoLuong'] * $item['DonGia'];
                    $tong_cong += $thanhtien;
                ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td><?= htmlspecialchars($item['TenSP']) ?></td>
                    <td class="text-center"><?= $item['SoLuong'] ?></td>
                    <td class="text-end"><?= number_format($item['DonGia']) ?>đ</td>
                    <td class="text-end fw-bold"><?= number_format($thanhtien) ?>đ</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end py-3">TỔNG THANH TOÁN:</th>
                    <th class="text-end py-3 text-danger h5 mb-0"><?= number_format($tong_cong) ?>đ</th>
                </tr>
            </tfoot>
        </table>

        <div class="no-print mt-5 text-center d-flex justify-content-center gap-3">
            <a href="ban-hang.php" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-plus"></i> Tạo đơn mới
            </a>
            <button onclick="window.print()" class="btn btn-success btn-lg px-4">
                <i class="fas fa-print"></i> In hóa đơn
            </button>
        </div>
    </div>
</div>
</body>
</html>