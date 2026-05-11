<?php 
// 1. Kết nối DB và Session
include 'db.php'; 

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$quyen_nguoi_dung = $_SESSION['quyen'] ?? 'user';

// 3. Xử lý tìm kiếm & Lọc danh mục
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? "");
$category = mysqli_real_escape_string($conn, $_GET['cat'] ?? "");

$where_sql = " WHERE 1=1 ";
if (!empty($search)) {
    $where_sql .= " AND (TenSP LIKE '%$search%' OR DanhMuc LIKE '%$search%') ";
}
if (!empty($category)) {
    $where_sql .= " AND DanhMuc = '$category' ";
}

$sql = "SELECT * FROM SanPham $where_sql ORDER BY MaSP DESC";
$result = mysqli_query($conn, $sql);

// Xác định trang hiện tại cho Navbar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Store - Chăm sóc sức khỏe gia đình</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #198754; --accent-color: #ffc107; --bg-light: #f8f9fa; }
        body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('https://img.freepik.com/free-photo/pharmacist-working-pharmacy-store_23-2148892243.jpg');
            background-size: cover; background-position: center;
            color: white; padding: 120px 0; border-radius: 0 0 50px 50px; margin-bottom: 40px;
        }

        /* Category Cards */
        .cat-card {
            background: white; border-radius: 20px; padding: 25px; text-align: center;
            transition: all 0.3s cubic-bezier(.25,.8,.25,1); border: 2px solid transparent; 
            cursor: pointer; text-decoration: none; color: #333; height: 100%;
        }
        .cat-card.active { border-color: var(--primary-color); background: #eefdf5; }
        .cat-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .cat-card i { font-size: 2.8rem; color: var(--primary-color); margin-bottom: 15px; display: block; }
        .cat-card h6 { font-size: 1rem; margin: 0; }

        /* Product Card Customization */
        .product-card {
            border: none; border-radius: 25px; transition: 0.4s; background: #fff; position: relative; overflow: hidden;
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .img-container {
            height: 220px; background: #fff; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .img-container img { max-width: 100%; max-height: 100%; object-fit: contain; transition: 0.5s; }
        .product-card:hover .img-container img { transform: scale(1.1); }
        
        .price-text { color: var(--primary-color); font-weight: 800; font-size: 1.25rem; }
        .category-badge {
            background: #eefdf5; color: var(--primary-color); padding: 4px 12px;
            border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        }

        .add-cart-btn {
            position: absolute; right: 20px; bottom: 85px; width: 50px; height: 50px;
            border-radius: 50%; background: var(--primary-color); color: white;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 15px rgba(25, 135, 84, 0.3); transition: 0.3s; z-index: 10;
        }
        .add-cart-btn:hover { background: #146c43; color: white; transform: rotate(90deg) scale(1.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm px-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-success fs-3" href="user.php">
            <i class="fas fa-leaf me-2"></i>PHARMACY
        </a>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <form action="user.php" method="GET" class="mx-auto w-50 position-relative">
                <input type="text" name="search" class="form-control rounded-pill ps-4 py-2 border-0 bg-light" 
                       placeholder="Tìm thuốc, vitamin, khẩu trang..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn position-absolute end-0 top-0 mt-1 me-2 text-success"><i class="fas fa-search"></i></button>
            </form>
            
            <ul class="navbar-nav align-items-center">
                <?php if($quyen_nguoi_dung == 'admin'): ?>
                <li class="nav-item me-3">
                    <a class="btn btn-outline-success btn-sm rounded-pill" href="index.php">
                        <i class="fas fa-user-shield me-1"></i> Quản trị
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item dropdown me-3">
                    <a href="#" class="nav-link dropdown-toggle text-dark fw-600" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg text-success me-1"></i> <?= $_SESSION['hoten'] ?? $_SESSION['user'] ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                <li>
                      <a class="dropdown-item" href="Models/lich-su-mua-hang.php">
                        <i class="fas fa-history me-2 text-primary"></i> Lịch sử mua hàng
                      </a>
                </li>                        
                     <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('Đăng xuất?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Thoát</a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a href="Models/GioHang.php" class="nav-link position-relative">
                        <i class="fas fa-shopping-basket fa-lg text-dark"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3">Sức Khỏe Của Bạn,<br><span class="text-success">Sứ Mệnh Của Chúng Tôi</span></h1>
        <p class="lead mb-5 opacity-75">Cam kết thuốc chính hãng - Giao hàng siêu tốc - Tư vấn tận tâm</p>
        <a href="#sanpham" class="btn btn-warning btn-lg px-5 py-3 rounded-pill fw-bold text-white shadow-lg">MUA SẮM NGAY</a>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <?php 
            $cats = [
                ['name' => 'Thuốc giảm đau', 'icon' => 'fas fa-capsules'],
                ['name' => 'Thực phẩm chức năng', 'icon' => 'fas fa-apple-alt'],
                ['name' => 'Dụng cụ y tế', 'icon' => 'fas fa-stethoscope'],
                ['name' => '', 'icon' => 'fas fa-th-large', 'label' => 'Tất Cả Danh Mục']
            ];
            foreach($cats as $c):
                $isActive = ($category === $c['name']) ? 'active' : '';
                $url = !empty($c['name']) ? "user.php?cat=".urlencode($c['name']) : "user.php";
                $label = $c['label'] ?? $c['name'];
        ?>
        <div class="col-6 col-md-3">
            <a href="<?= $url ?>" class="cat-card d-block <?= $isActive ?>">
                <i class="<?= $c['icon'] ?>"></i>
                <h6 class="fw-bold"><?= $label ?></h6>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container" id="sanpham">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">Sản Phẩm Nổi Bật</h2>
            <div class="bg-success" style="width: 60px; height: 5px; border-radius: 10px;"></div>
        </div>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill">
            <i class="fas fa-filter me-2 text-success"></i><?= mysqli_num_rows($result) ?> sản phẩm
        </span>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col">
            <div class="card product-card h-100 shadow-sm position-relative">
                
                <div class="img-container">
                    <?php if (!empty($row['HinhAnh'])): ?>
                        <img src="uploads/<?= $row['HinhAnh'] ?>" alt="product">
                    <?php else: ?>
                        <i class="fas fa-pills fa-4x opacity-25"></i>
                    <?php endif; ?>
                </div>
                
                <a href="product_detail.php?id=<?= $row['MaSP'] ?>" class="stretched-link"></a>

                <a href="Models/add-cart.php?id=<?= $row['MaSP'] ?>" class="add-cart-btn text-decoration-none shadow" style="z-index: 5; position: absolute;">
                    <i class="fas fa-plus"></i>
                </a>

                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <span class="category-badge"><?= htmlspecialchars($row['DanhMuc']) ?></span>
                    </div>
                    <h5 class="fw-bold mb-3 text-dark" style="font-size: 1.1rem;"><?= htmlspecialchars($row['TenSP']) ?></h5>
                    
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="price-text"><?= number_format($row['GiaBan'], 0, ',', '.') ?>đ</span>
                        <span class="badge <?= $row['SoLuongTong'] > 0 ? 'bg-light text-dark' : 'bg-danger' ?> border">
                            Còn: <?= $row['SoLuongTong'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<footer class="bg-dark text-white py-5 mt-5" style="border-radius: 50px 50px 0 0;">
    <div class="container text-center">
        <h2 class="fw-bold text-success mb-4"><i class="fas fa-leaf me-2"></i>PHARMACY</h2>
        <p class="opacity-75 mb-4 px-md-5">Chúng tôi cung cấp giải pháp chăm sóc sức khỏe toàn diện với đội ngũ dược sĩ chuyên nghiệp và sản phẩm đạt chuẩn quốc tế.</p>
        <div class="d-flex justify-content-center gap-4 mb-5">
            <a href="#" class="text-white fs-3 transition"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-white fs-3 transition"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-white fs-3 transition"><i class="fab fa-youtube"></i></a>
        </div>
        <hr class="opacity-25 mb-4">
        <small class="opacity-50">© 2026 Hệ thống Quản lý Nhà thuốc Chuyên nghiệp. All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>