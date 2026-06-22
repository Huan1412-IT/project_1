<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Verify database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not found in ' . __FILE__);
    die('Lỗi: Không thể kết nối tới Cơ sở dữ liệu. Vui lòng thử lại sau.');
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_remove]);
    header("Location: GioHang.php");
    exit();
}

// 3. Lấy danh sách ID từ giỏ hàng
$cart = $_SESSION['cart'] ?? [];
$products_in_cart = [];
$total_money = 0;

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $sql = "SELECT * FROM SanPham WHERE MaSP IN ($ids)";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $products_in_cart[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .cart-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
        .quantity-control { max-width: 120px; }
        .btn-checkout { background: #198754; color: white; border-radius: 50px; padding: 12px 30px; font-weight: bold; transition: 0.3s; }
        .btn-checkout:hover { background: #146c43; transform: translateY(-2px); color: white; }
        .empty-cart { padding: 100px 0; text-align: center; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="mb-4">
        <a href="/../user.php" class="text-success text-decoration-none fw-bold">
            <i class="fas fa-chevron-left me-2"></i> Tiếp tục mua thuốc
        </a>
    </div>

    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-basket me-2 text-success"></i>Giỏ hàng của bạn</h2>

    <div class="row g-4">
        <?php if (!empty($products_in_cart)): ?>
            <div class="col-lg-8">
                <div class="card cart-card">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products_in_cart as $item): 
                                    $id = $item['MaSP'];
                                    $qty = $cart[$id];
                                    $subtotal = $item['GiaBan'] * $qty;
                                    $total_money += $subtotal;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="../uploads/<?= $item['HinhAnh'] ?>" class="product-img me-3 border">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= $item['TenSP'] ?></h6>
                                                <small class="text-muted"><?= $item['DanhMuc'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?= number_format($item['GiaBan'], 0, ',', '.') ?>đ</td>
                                    <td>
                                        <div class="input-group quantity-control border rounded-pill overflow-hidden">
                                            <button class="btn btn-sm btn-light border-0" onclick="location.href='update-cart.php?id=<?= $id ?>&action=minus'">-</button>
                                            <input type="text" class="form-control form-control-sm border-0 text-center bg-white" value="<?= $qty ?>" readonly>
                                            <button class="btn btn-sm btn-light border-0" onclick="location.href='update-cart.php?id=<?= $id ?>&action=plus'">+</button>
                                        </div>
                                    </td>
                                    <td class="text-success fw-bold"><?= number_format($subtotal, 0, ',', '.') ?>đ</td>
                                    <td class="pe-4 text-end">
                                        <a href="?remove=<?= $id ?>" class="text-danger" onclick="return confirm('Xóa khỏi giỏ hàng?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card cart-card p-4">
                    <h5 class="fw-bold mb-4">Tóm tắt đơn hàng</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?= number_format($total_money, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold">Tổng cộng:</span>
                        <span class="h5 fw-bold text-danger"><?= number_format($total_money, 0, ',', '.') ?>đ</span>
                    </div>
                    <button class="btn btn-checkout w-100 shadow">
                      <a href="checkout_info.php" class="btn btn-checkout w-100 shadow" onclick="return confirm('Bạn chắc chắn muốn đặt hàng?')">
                       XÁC NHẬN THANH TOÁN <i class="fas fa-arrow-right ms-2"></i>
                      </a>                    
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="col-12 empty-cart card cart-card">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Giỏ hàng của bạn đang trống</h4>
                <p class="mb-4">Hãy chọn những sản phẩm thuốc cần thiết cho sức khỏe của bạn.</p>
                <div>
                    <a href="/../user.php" class="btn btn-success rounded-pill px-5 py-2">MUA SẮM NGAY</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>