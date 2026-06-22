<?php 
// =====================
// 1. INCLUDE DB + SESSION
// =====================session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// =====================
// 2. LẤY DỮ LIỆU CŨ
// =====================
if (isset($_GET['id'])) {
    // Chuyển sang MaSP và bảng SanPham
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM SanPham WHERE MaSP = $id");
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) {
        die("Sản phẩm không tồn tại!");
    }
}

// =====================
// 3. XỬ LÝ CẬP NHẬT
// =====================
if (isset($_POST['update'])) {
    // Lấy dữ liệu từ Form theo tên mới
    $tenSP      = mysqli_real_escape_string($conn, $_POST['TenSP']);
    $danhMuc    = mysqli_real_escape_string($conn, $_POST['DanhMuc']);
    $giaBan     = (int)$_POST['GiaBan'];
    $soLuongTong = (int)$_POST['SoLuongTong'];
    
    // Mặc định sử dụng lại tên ảnh cũ (cột HinhAnh)
    $hinhAnh_name = $row['HinhAnh']; 

    // Kiểm tra nếu người dùng chọn ảnh mới
    if (isset($_FILES['HinhAnh']['name']) && $_FILES['HinhAnh']['name'] != "") {
        $hinhAnh_name = $_FILES['HinhAnh']['name'];
        $hinhAnh_tmp  = $_FILES['HinhAnh']['tmp_name'];
        $target       = "../uploads/" . basename($hinhAnh_name);

        if (move_uploaded_file($hinhAnh_tmp, $target)) {
            // Xóa ảnh cũ để nhẹ server (trừ ảnh mặc định)
            if ($row['HinhAnh'] != "default.png" && file_exists("../uploads/" . $row['HinhAnh'])) {
                unlink("../uploads/" . $row['HinhAnh']);
            }
        }
    }
    
    // Cập nhật Database: Bảng SanPham, các cột tiếng Việt
    $sql = "UPDATE SanPham SET 
            TenSP = N'$tenSP', 
            HinhAnh = '$hinhAnh_name', 
            DanhMuc = N'$danhMuc', 
            GiaBan = $giaBan, 
            SoLuongTong = $soLuongTong 
            WHERE MaSP = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='../index.php';</script>";
        exit();
    } else {
        $error = "Lỗi cập nhật: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm" style="border-radius: 15px;">
                <h3 class="text-center mb-4 text-warning fw-bold">Chỉnh Sửa Thuốc</h3>
                
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên thuốc</label>
                        <input type="text" name="TenSP" class="form-control" value="<?php echo $row['TenSP']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hình ảnh sản phẩm</label>
                        <div class="mb-2">
                            <small class="text-muted">Ảnh hiện tại:</small><br>
                            <img src="../uploads/<?php echo $row['HinhAnh']; ?>" width="100" class="img-thumbnail mt-1">
                        </div>
                        <input type="file" name="HinhAnh" class="form-control" accept="image/*">
                        <small class="text-secondary italic">Để trống nếu không muốn thay đổi ảnh.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="DanhMuc" class="form-select" required>
                            <option value="Thuốc giảm đau" <?php if($row['DanhMuc'] == 'Thuốc giảm đau') echo 'selected'; ?>>Thuốc giảm đau</option>
                            <option value="Thực phẩm chức năng" <?php if($row['DanhMuc'] == 'Thực phẩm chức năng') echo 'selected'; ?>>Thực phẩm chức năng</option>
                            <option value="Dụng cụ y tế" <?php if($row['DanhMuc'] == 'Dụng cụ y tế') echo 'selected'; ?>>Dụng cụ y tế</option>
                            <option value="Thuốc tiêu hóa" <?php if($row['DanhMuc'] == 'Thuốc tiêu hóa') echo 'selected'; ?>>Thuốc tiêu hóa</option>
                            <option value="Khác" <?php if($row['DanhMuc'] == 'Khác') echo 'selected'; ?>>Khác</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                            <input type="number" name="GiaBan" class="form-control" value="<?php echo (int)$row['GiaBan']; ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Số lượng tổng</label>
                            <input type="number" name="SoLuongTong" class="form-control" value="<?php echo $row['SoLuongTong']; ?>" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button name="update" class="btn btn-warning btn-lg text-white fw-bold">Lưu Thay Đổi</button>
                        <a href="../index.php" class="btn btn-outline-secondary">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>