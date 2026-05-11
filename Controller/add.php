<?php
// =====================
// 1. INCLUDE DB + SESSION
// =====================
include '../db.php';

// =====================
// 2. KIỂM TRA ĐĂNG NHẬP
// =====================
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='../login.php';</script>";
    exit();
}

// =====================
// 3. XỬ LÝ FORM SUBMIT
// =====================
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {

    // Lấy dữ liệu từ Form (Sử dụng tên mới: TenSP, DanhMuc...)
    $tenSP      = mysqli_real_escape_string($conn, trim($_POST['TenSP']));
    $danhMuc    = mysqli_real_escape_string($conn, $_POST['DanhMuc']);
    $giaBan     = (int) $_POST['GiaBan'];
    $soLuong    = (int) $_POST['SoLuongTong'];

    // --- XỬ LÝ HÌNH ẢNH ---
    $hinhAnh     = $_FILES['HinhAnh']['name']; 
    $hinhAnh_tmp = $_FILES['HinhAnh']['tmp_name']; // Đã sửa từ 'image' thành 'HinhAnh'
    $target      = "../uploads/" . basename($hinhAnh); 

    // Kiểm tra dữ liệu trống
    if ($tenSP === "" || $danhMuc === "" || $giaBan < 0 || $soLuong < 0 || $hinhAnh === "") {
        $error = "Vui lòng nhập đầy đủ thông tin và chọn ảnh sản phẩm.";
    } else {
        // Di chuyển file vào thư mục uploads
        if (move_uploaded_file($hinhAnh_tmp, $target)) {
            
            // CẬP NHẬT CÂU LỆNH SQL: Bảng SanPham và các cột tiếng Việt
            $sql = "INSERT INTO SanPham (TenSP, HinhAnh, DanhMuc, GiaBan, SoLuongTong)
                    VALUES ('$tenSP', '$hinhAnh', '$danhMuc', $giaBan, $soLuong)";

            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Thêm thuốc thành công!'); window.location.href='../index.php';</script>";
                exit();
            } else {
                $error = "Lỗi SQL: " . mysqli_error($conn);
            }
        } else {
            $error = "Lỗi: Không thể tải ảnh lên thư mục uploads.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 p-4" style="border-radius: 15px;">
                <h3 class="text-center mb-4 text-success fw-bold">Thêm Thuốc Mới</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên thuốc</label>
                        <input type="text" name="TenSP" class="form-control" placeholder="Ví dụ: Paracetamol" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hình ảnh sản phẩm</label>
                        <input type="file" name="HinhAnh" class="form-control" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="DanhMuc" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <option value="Thuốc giảm đau">Thuốc giảm đau</option>
                            <option value="Thuốc tiêu hóa">Thuốc tiêu hóa</option>
                            <option value="Thực phẩm chức năng">Thực phẩm chức năng</option>
                            <option value="Dụng cụ y tế">Dụng cụ y tế</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Giá bán (VNĐ)</label>
                            <input type="number" name="GiaBan" class="form-control" min="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Số lượng tổng</label>
                            <input type="number" name="SoLuongTong" class="form-control" min="0" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" name="save" class="btn btn-success btn-lg">Lưu Vào Kho</button>
                        <a href="../index.php" class="btn btn-outline-secondary">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>