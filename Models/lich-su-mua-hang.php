<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Verify database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not found in ' . __FILE__);
    die('Lỗi: Không thể kết nối tới Cơ sở dữ liệu. Vui lòng thử lại sau.');
}

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    die("<div style='text-align:center; padding:50px;'>
            <h3>Bạn chưa đăng nhập!</h3>
            <a href='../login.php'>Đi đến trang đăng nhập</a>
         </div>");
}

$ma_nd = $_SESSION['user_id'];

// 2. TRUY VẤN LẤY HÓA ĐƠN (Dùng MaND từ Database của bạn)
$sql = "SELECT * FROM HoaDon WHERE MaND = '$ma_nd' ORDER BY NgayTao DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .order-container { max-width: 900px; margin: 40px auto; }
        .card-order { border: none; border-radius: 15px; overflow: hidden; margin-bottom: 25px; transition: 0.3s; }
        .card-order:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .item-img { width: 65px; height: 65px; object-fit: cover; border-radius: 8px; }
        .price-total { color: #dc3545; font-size: 1.25rem; font-weight: bold; }
    </style>
</head>
<body>

<div class="container order-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-box-open me-2 text-success"></i>Lịch sử mua hàng</h2>
        <a href="../User.php" class="btn btn-outline-primary btn-sm rounded-pill"><i class="fas fa-shopping-cart me-1"></i> Tiếp tục mua</a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($hd = mysqli_fetch_assoc($result)): ?>
            <div class="card card-order shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <div>
                        <span class="badge bg-success me-2">Đã thanh toán</span>
                        <span class="text-muted small">Ngày đặt: <?= date('d/m/Y H:i', strtotime($hd['NgayTao'])) ?></span>
                    </div>
                    <span class="fw-bold text-dark">Mã đơn: #<?= $hd['MaHD'] ?></span>
                </div>

                <div class="card-body bg-white border-top border-bottom">
                    <?php
                    $ma_hd = $hd['MaHD'];
                    // JOIN dựa trên cấu trúc bảng của bạn: HoaDon -> ChiTietHoaDon -> LoHang -> SanPham
                    $sql_ct = "SELECT ct.*, sp.TenSP, sp.HinhAnh 
                               FROM ChiTietHoaDon ct
                               JOIN LoHang lh ON ct.MaLo = lh.MaLo
                               JOIN SanPham sp ON lh.MaSP = sp.MaSP
                               WHERE ct.MaHD = '$ma_hd'";
                    $res_ct = mysqli_query($conn, $sql_ct);
                    while ($ct = mysqli_fetch_assoc($res_ct)):
                    ?>
                    <div class="d-flex align-items-center py-2">
                        <img src="../uploads/<?= $ct['HinhAnh'] ?>" class="item-img me-3 border" onerror="this.src='https://via.placeholder.com/65?text=No+Img'">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold"><?= $ct['TenSP'] ?></h6>
                            <small class="text-muted">Số lượng: x<?= $ct['SoLuong'] ?> | Giá: <?= number_format($ct['GiaBan'], 0, ',', '.') ?>đ</small>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <span class="text-secondary small">Hệ thống Nhà thuốc 24/7</span>
                    <div>
                        <span class="me-2">Thành tiền:</span>
                        <span class="price-total"><?= number_format($hd['TongTien'], 0, ',', '.') ?>đ</span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center bg-white p-5 rounded-4 shadow-sm">
            <img src="https://cdn-icons-png.flaticon.com/512/11329/11329061.png" width="120" class="mb-3 opacity-50">
            <h4 class="text-muted">Bạn chưa có đơn hàng nào</h4>
            <p class="text-secondary">Hãy thử thực hiện thanh toán một đơn hàng mới!</p>
            <a href="../index.php" class="btn btn-success px-4 rounded-pill mt-2">Đi mua sắm</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>