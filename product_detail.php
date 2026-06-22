<?php
session_start();
include './Database/db.php'; 

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM SanPham WHERE MaSP = '$id'";
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // QUAN TRỌNG: Gán $row vào $sp để các dòng code bên dưới không bị lỗi
        $sp = $row; 
    } else {
        die("Không tìm thấy sản phẩm!");
    }
} else {
    die("Thiếu mã sản phẩm!");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sp['TenSP']) ?> - Chi tiết sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .product-img { max-height: 450px; object-fit: contain; border-radius: 15px; background: white; }
        .price-tag { font-size: 2.2rem; color: #dc3545; font-weight: bold; }
        .breadcrumb-item a { text-decoration: none; color: #198754; }
        .card-detail { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="mb-4">
        <a href="user.php" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ
        </a>
    </div>

    <div class="row card-detail bg-white p-4 g-5">
        <div class="col-md-6 text-center border-end">
            <?php if (!empty($sp['HinhAnh']) && file_exists('uploads/' . $sp['HinhAnh'])): ?>
                <img src="uploads/<?= $sp['HinhAnh'] ?>" class="img-fluid product-img shadow-sm p-2">
            <?php else: ?>
                <img src="https://via.placeholder.com/400x400?text=No+Image" class="img-fluid product-img">
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Cửa hàng</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($sp['DanhMuc']) ?></li>
                </ol>
            </nav>
            
            <h1 class="fw-bold text-dark mb-3"><?= htmlspecialchars($sp['TenSP']) ?></h1>
            <p class="text-muted">Mã sản phẩm: <span class="fw-bold">SP00<?= $sp['MaSP'] ?></span></p>
            <hr>
            
            <div class="price-tag mb-4">
                <?= number_format($sp['GiaBan'], 0, ',', '.') ?> <small>VNĐ</small>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold text-secondary">Tình trạng kho:</h6>
                <?php if ($sp['SoLuongTong'] > 0): ?>
                    <span class="badge bg-success px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i>Còn hàng (<?= $sp['SoLuongTong'] ?>)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger px-3 py-2">
                        <i class="fas fa-times-circle me-1"></i>Hết hàng
                    </span>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <form action="./Models/add_to_cart_fast.php" method="POST">
                    <input type="hidden" name="id_sp" value="<?= $sp['MaSP'] ?>">
                    
                    <div class="d-flex align-items-center mb-4">
                        <label class="fw-bold me-3">Số lượng:</label>
                        <div class="input-group shadow-sm" style="width: 140px;">
                            <input type="number" name="qty" class="form-control text-center" value="1" min="1" max="<?= $sp['SoLuongTong'] ?>">
                        </div>
                    </div>
                    
                    <?php if ($sp['SoLuongTong'] > 0): ?>
                        <button type="submit" name="buy_now" class="btn btn-danger btn-lg w-100 py-3 rounded-pill shadow fw-bold transition-all">
                           <i class="fas fa-bolt me-2"></i> MUA NGAY & THANH TOÁN
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary btn-lg w-100 py-3 rounded-pill disabled">
                            LIÊN HỆ ĐẶT HÀNG
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded-3">
                <small class="text-muted">
                    <i class="fas fa-shipping-fast me-2"></i>Giao hàng nhanh nội thành trong 2 giờ. <br>
                    <i class="fas fa-shield-alt me-2"></i>Cam kết hàng chính hãng 100%.
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>