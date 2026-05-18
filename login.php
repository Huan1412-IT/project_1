<?php
// 1. Bật hiển thị lỗi
ini_set('display_errors', 1);
error_reporting(E_ALL);

 
include 'db.php'; 

// 3. Kiểm tra chuyển hướng an toàn
if (isset($_SESSION['user']) && isset($_SESSION['quyen'])) {
    if ($_SESSION['quyen'] == 'admin') {
        header("Location: index.php");
    } else {
        header("Location: user.php"); 
    }
    exit();
}

$error_message = "";

if (isset($_POST['login'])) {
    $userInput = mysqli_real_escape_string($conn, trim($_POST['username']));
    $passInput = $_POST['password'];

    $sql = "SELECT * FROM NguoiDung WHERE TenDangNhap = '$userInput'";
    $res = mysqli_query($conn, $sql);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        
        // Kiểm tra mật khẩu (Nên dùng password_verify nếu bạn đã mã hóa mật khẩu)
        if ($passInput == $row['MatKhau']) {
            // --- ĐOẠN QUAN TRỌNG NHẤT CẦN SỬA ---
            $_SESSION['user_id'] = $row['MaND']; // LƯU ID VÀO ĐÂY ĐỂ LÀM LỊCH SỬ & THANH TOÁN
            // ------------------------------------
            
            $_SESSION['user']   = $row['TenDangNhap'];
            $_SESSION['quyen']  = $row['QuyenHan']; 
            $_SESSION['hoten']  = $row['HoTen'];

            if ($_SESSION['quyen'] == 'admin' || $_SESSION['quyen'] == 'staff' ) {
                header("Location: index.php");
            } else {
                header("Location: user.php"); 
            }
            exit();
        } else {
            $error_message = "Sai mật khẩu!";
        }
    } else {
        $error_message = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Nhà Thuốc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #198754 0%, #20c997 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card { border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-success { background-color: #198754; border: none; transition: 0.3s; }
        .btn-success:hover { background-color: #146c43; transform: translateY(-2px); }
        .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 col-sm-8">
            <div class="card login-card p-4">
                <div class="text-center mb-4">
                    <div class="bg-success d-inline-block p-3 rounded-circle text-white mb-3 shadow-sm">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-success">ĐĂNG NHẬP</h3>
                    <p class="text-muted small">Hệ thống quản lý dược phẩm</p>
                </div>

                <?php if ($error_message != ""): ?>
                    <div class="alert alert-danger py-2 small text-center shadow-sm">
                        <i class="fas fa-exclamation-circle me-1"></i> <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tên tài khoản</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-success"></i></span>
                            <input type="text" name="username" class="form-control border-start-0" placeholder="Nhập username" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-success"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" placeholder="Nhập mật khẩu" required>
                        </div>
                    </div>
                    <button name="login" class="btn btn-success w-100 py-2 fw-bold shadow">
                    ĐĂNG NHẬP
                    </button>
                    <div class="mt-4 text-center small">
                        <span class="text-muted">Chưa có tài khoản?</span> 
                        <a href="register.php" class="text-success text-decoration-none fw-bold ms-1">Đăng ký ngay</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>