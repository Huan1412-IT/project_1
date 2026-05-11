<?php
// Xác định đường dẫn gốc để tránh lỗi khi include ở các thư mục con
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow sticky-top px-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-clinic-medical me-2"></i>NHÀ THUỐC ĐẠI HỌC
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </li>

                <?php if ($_SESSION['quyen'] == 'admin' || $_SESSION['quyen'] == 'staff'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield"></i> Quản trị hệ thống
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="Admin/quan-ly-nhan-vien.php">Quản lý nhân viên</a></li>
                        <li><a class="dropdown-item" href="Admin/bao-cao-doanh-thu.php">Báo cáo doanh thu</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="Admin/NhaCC.php">Nhà cung cấp</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Admin/add.php"><i class="fas fa-plus-circle"></i> Nhập kho</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="ban-hang.php">
                        <i class="fas fa-shopping-cart"></i> Bán hàng
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="LoHang.php">
                        <i class="fas fa-boxes"></i> Quản lý lô hàng
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <div class="text-white me-3">
                    <small class="d-block text-end">Chào, <strong><?= $_SESSION['hoten'] ?></strong></small>
                    <span class="badge bg-light text-success shadow-sm"><?= ucfirst($_SESSION['quyen']) ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill" onclick="return confirm('Bạn muốn đăng xuất?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
</nav>