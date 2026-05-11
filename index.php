<?php 
// 1. Kết nối Database và khởi tạo Session (db.php đã xử lý session_start)
include 'db.php'; 

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// 3. Lấy quyền hạn người dùng (Mặc định là 'user' nếu chưa đăng nhập lại)
$quyen_nguoi_dung = $_SESSION['quyen'] ?? 'user';

// 4. Xử lý chức năng Tìm kiếm thuốc
$search = "";
$where_sql = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_sql = " WHERE TenSP LIKE '%$search%' OR DanhMuc LIKE '%$search%' ";
}

// 5. Truy vấn danh sách Sản phẩm
$sql = "SELECT MaSP, TenSP, HinhAnh, DanhMuc, GiaBan, SoLuongTong FROM SanPham $where_sql ORDER BY MaSP DESC";
$result = mysqli_query($conn, $sql);

// Xác định tên file hiện tại để kích hoạt trạng thái 'active' trên Menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà thuốc - Pharmacy Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .navbar .nav-link.active { color: #fff !important; font-weight: bold; border-bottom: 2px solid #fff; }
        .input-group {border-radius:30px;}
        .product-card { border: none; border-radius: 15px; overflow: hidden; transition: 0.3s ease; height: 100%; }
        .product-card:hover { transform: translateY(-7px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
        .card-img-top { height: 190px; display: flex; align-items: center; justify-content: center; background: #fff; overflow: hidden; }
        .card-img-top img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
        .card-img-top i { font-size: 4rem; color: #198754; opacity: 0.3; }
        .price-text { color: #d63031; font-weight: bold; font-size: 1.25rem; }
        .category-label { font-size: 0.7rem; background: #e9ecef; padding: 4px 12px; border-radius: 20px; color: #495057; font-weight: 700; text-transform: uppercase; }
        .dropdown-menu { border-radius: 10px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow mb-4 sticky-top px-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-prescription-bottle-medical me-2"></i>PHARMACY MANAGER
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Trang chủ
                    </a>
                </li>

                <?php if ($quyen_nguoi_dung == 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-boxes me-1"></i> Quản lý kho
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="Controller/add.php"><i class="fas fa-plus-circle me-2 text-success"></i>Nhập thuốc mới</a></li>
                        <li><a class="dropdown-item" href="Controller/LoHang.php"><i class="fas fa-layer-group me-2 text-primary"></i>Quản lý lô hàng</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="Controller/NhaCC.php"><i class="fas fa-truck me-2 text-warning"></i>Nhà cung cấp</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-pie me-1"></i> Báo cáo
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="Controller/hoa-don.php"><i class="fas fa-receipt me-2"></i>Lịch sử hóa đơn</a></li>
                        <li><a class="dropdown-item" href="Controller/doanh-thu.php"><i class="fas fa-coins me-2"></i>Thống kê doanh thu</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="Controller/nguoi-dung.php"><i class="fas fa-users-cog me-1"></i> Hệ thống</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="Controller/ban-hang.php"><i class="fas fa-shopping-basket me-1"></i> Bán hàng</a>
                </li> 
            </ul>

            <div class="d-flex align-items-center">
                <div class="text-white me-3 text-end d-none d-md-block">
                    <small class="d-block opacity-75">Chào bạn,</small>
                    <span class="fw-bold"><?= $_SESSION['hoten'] ?? $_SESSION['user'] ?></span> 
                    <span class="badge bg-light text-success ms-1" style="font-size: 0.65rem;"><?= strtoupper($quyen_nguoi_dung) ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Bạn có muốn đăng xuất không?')">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row mb-5 justify-content-center">
        <div class="col-md-7">
            <form action="" method="GET" class="input-group shadow-sm">
                <input type="text" name="search" class="form-control border-0 py-3 ps-4" style="border-radius: 30px 0 0 30px;" placeholder="Tìm tên thuốc, công dụng hoặc danh mục..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-white border-0 bg-white text-success px-4" style="border-radius: 0 30px 30px 0;" type="submit">
                    <i class="fas fa-search fa-lg"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php 
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) { 
        ?>
        <div class="col">
            <div class="card product-card shadow-sm">
                <div class="card-img-top">
                    <?php if (!empty($row['HinhAnh']) && file_exists('uploads/' . $row['HinhAnh'])): ?>
                        <img src="uploads/<?= $row['HinhAnh'] ?>" alt="<?= htmlspecialchars($row['TenSP']) ?>">
                    <?php else: ?>
                        <i class="fas fa-tablets"></i>
                    <?php endif; ?>
                </div>
                
                <div class="card-body p-3">
                    <span class="category-label mb-2 d-inline-block"><?= htmlspecialchars($row['DanhMuc']) ?></span>
                    <h6 class="card-title fw-bold text-dark text-truncate" title="<?= htmlspecialchars($row['TenSP']) ?>">
                        <?= htmlspecialchars($row['TenSP']) ?>
                    </h6>
                    <p class="price-text mb-1"><?= number_format($row['GiaBan'], 0, ',', '.') ?>đ</p>
                    <p class="text-muted small mb-0">
                        Kho: <b class="<?= ($row['SoLuongTong'] < 10) ? 'text-danger' : 'text-success' ?>"><?= $row['SoLuongTong'] ?></b>
                    </p>
                </div>

                <div class="card-footer bg-white border-0 pb-3">
                    <div class="btn-group w-100 shadow-sm rounded">
                        <?php if ($quyen_nguoi_dung == 'admin'): ?>
                            <a href="Controller/edit.php?id=<?= $row['MaSP'] ?>" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="Controller/delete.php?id=<?= $row['MaSP'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa thuốc này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        <?php else: ?>
                            <a href="ban-hang.php?id=<?= $row['MaSP'] ?>" class="btn btn-success btn-sm w-100 fw-bold">
                                <i class="fas fa-cart-plus me-1"></i> Mua Hàng
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            echo "<div class='col-12 text-center py-5'><i class='fas fa-box-open fa-3x text-muted mb-3'></i><p class='text-muted'>Không tìm thấy sản phẩm nào trong hệ thống.</p></div>";
        }
        ?>
    </div>
</div>

<footer class="text-center py-4 border-top bg-white mt-auto">
    <p class="text-muted small mb-0 fw-bold">&copy; 2026 Pharmacy Manager System - Đồ án lập trình Web</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>