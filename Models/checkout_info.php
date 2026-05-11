<?php
// 1. Khởi tạo session và bật hiển thị lỗi
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Kiểm tra đường dẫn db.php (Dùng __DIR__ để chính xác tuyệt đối)
require_once __DIR__ . '/../db.php'; 

// 3. Kiểm tra đăng nhập và giỏ hàng
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Bạn chưa đăng nhập. <a href='../login.php'>Đăng nhập ngay</a>");
}

if (empty($_SESSION['cart'])) {
    die("Lỗi: Giỏ hàng trống. <a href='../index.php'>Quay lại mua hàng</a>");
}

$cart = $_SESSION['cart'];
$total_money = 0;
$products_in_cart = [];

// 4. Lấy dữ liệu sản phẩm
try {
    $ids = implode(',', array_keys($cart));
    $sql = "SELECT * FROM SanPham WHERE MaSP IN ($ids)";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $products_in_cart[] = $row;
    }
} catch (Exception $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .checkout-box { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .product-line { border-bottom: 1px solid #eee; padding: 10px 0; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row">
        <div class="col-md-7">
            <div class="p-4 checkout-box mb-4">
                <h4 class="mb-4 fw-bold text-success"><i class="fas fa-map-marker-alt me-2"></i>Thông tin nhận hàng</h4>
                <form action="process_order.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên người nhận</label>
                        <input type="text" name="full_name" class="form-control" value="<?= $_SESSION['hoten'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Số điện thoại gọi khi giao hàng" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ giao hàng</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Ví dụ: Số 123, đường ABC, phường X..." required></textarea>
                    </div>
                    <button type="submit" name="confirm_order" class="btn btn-success w-100 py-3 fw-bold rounded-pill">
                        XÁC NHẬN ĐẶT HÀNG
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="p-4 checkout-box">
                <h4 class="mb-4 fw-bold">Đơn hàng của bạn</h4>
                <?php foreach ($products_in_cart as $item): 
                    $qty = $cart[$item['MaSP']];
                    $subtotal = $item['GiaBan'] * $qty;
                    $total_money += $subtotal;
                ?>
                <div class="product-line d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="../uploads/<?= $item['HinhAnh'] ?>" width="50" height="50" class="rounded me-3 border" onerror="this.src='https://via.placeholder.com/50'">
                        <div>
                            <div class="fw-bold"><?= $item['TenSP'] ?></div>
                            <small class="text-muted">Số lượng: <?= $qty ?></small>
                        </div>
                    </div>
                    <span class="fw-bold"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2 h5">
                        <span>Tổng tiền thanh toán:</span>
                        <span class="text-danger fw-bold"><?= number_format($total_money, 0, ',', '.') ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>