<?php 
// 1. Include db.php để lấy kết nối $conn và session
include 'db.php'; 

// Nếu đã đăng nhập thì không cần đăng ký, đẩy về trang chủ
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$message_type = "";

// 2. Xử lý khi người dùng nhấn nút Đăng ký
if (isset($_POST['register'])) {
    $hoTen    = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Mặc định khi đăng ký mới sẽ là quyền 'nhanvien'
    $quyen    = 'user';

    // Kiểm tra tên đăng nhập đã tồn tại chưa trong bảng NguoiDung
    $check = mysqli_query($conn, "SELECT * FROM NguoiDung WHERE TenDangNhap='$username'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = "Tên đăng nhập này đã được sử dụng!";
        $message_type = "danger";
    } else {
        // Chèn dữ liệu vào bảng NguoiDung với các cột tiếng Việt
        $sql = "INSERT INTO NguoiDung (HoTen, TenDangNhap, MatKhau, QuyenHan) 
                VALUES ('$hoTen', '$username', '$password', '$quyen')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Đăng ký thành công! Hãy đăng nhập để tiếp tục.";
            $message_type = "success";
        } else {
            $message = "Lỗi hệ thống: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - Nhà Thuốc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #198754 0%, #20c997 100%); min-height: 100vh; display: flex; align-items: center; }
        .register-card { border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card register-card p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-success">TẠO TÀI KHOẢN</h3>
                    <p class="text-muted small">Dành cho nhân viên nhà thuốc mới</p>
                </div>

                <?php if ($message != ""): ?>
                    <div class="alert alert-<?= $message_type ?> py-2 small text-center"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Họ và tên</label>
                        <input type="text" name="fullname" class="form-control" placeholder="Họ và Tên" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control" placeholder="Tên tài khoản viết liền" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>

                    <button name="register" class="btn btn-success w-100 py-2 fw-bold rounded-pill">ĐĂNG KÝ NGAY</button>
                    
                    <div class="mt-3 text-center small">
                        Đã có tài khoản? <a href="login.php" class="text-success text-decoration-none fw-bold">Đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>