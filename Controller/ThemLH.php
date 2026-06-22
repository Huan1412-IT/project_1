<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// 1. KHỞI ĐỘNG SESSION (CHỐNG LỖI LẶP)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='../login.php';</script>";
    exit();
}

// 3. LẤY DỮ LIỆU
$sp  = mysqli_query($conn, "SELECT MaSP, TenSP FROM SanPham");
$ncc = mysqli_query($conn, "SELECT MaNCC, TenNCC FROM NhaCungCap");

// 4. XỬ LÝ FORM
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $MaSP = $_POST['MaSP'];
    $MaNCC = $_POST['MaNCC'];
    $GiaNhap = $_POST['GiaNhap'];
    $SoLuongNhap = $_POST['SoLuongNhap'];
    $NgaySX = $_POST['NgaySanXuat'];
    $HSD = $_POST['NgayHetHan'];

    // Kiểm tra hạn sử dụng
    if ($HSD <= $NgaySX) {
        echo "<script>alert('Hạn sử dụng phải lớn hơn ngày sản xuất');</script>";
    } else {

        $sql = "INSERT INTO LoHang 
            (MaSP, MaNCC, GiaNhap, SoLuongNhap, SoLuongTon, NgaySanXuat, NgayHetHan)
            VALUES 
            ('$MaSP','$MaNCC','$GiaNhap','$SoLuongNhap','$SoLuongNhap','$NgaySX','$HSD')";

        if (mysqli_query($conn, $sql)) {

            // Cập nhật tồn kho tổng
            mysqli_query($conn, "UPDATE SanPham 
                SET SoLuongTong = SoLuongTong + $SoLuongNhap
                WHERE MaSP = $MaSP");

            header("Location: lo-hang.php");
            exit();
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm lô hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h4>➕ Thêm lô hàng</h4>

    <form method="POST" class="card p-4 shadow-sm mt-3">
        <div class="mb-3">
            <label>Sản phẩm</label>
            <select name="MaSP" class="form-control" required>
                <?php while($row = mysqli_fetch_assoc($sp)): ?>
                    <option value="<?= $row['MaSP'] ?>">
                        <?= $row['TenSP'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Nhà cung cấp</label>
            <select name="MaNCC" class="form-control" required>
                <option value="">-- Chọn nhà cung cấp --</option>
                <?php while ($row = mysqli_fetch_assoc($ncc)): ?>
                    <option value="<?= $row['MaNCC'] ?>">
                        <?= $row['TenNCC'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Giá nhập</label>
            <input type="number" name="GiaNhap" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Số lượng nhập</label>
            <input type="number" name="SoLuongNhap" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Ngày sản xuất</label>
            <input type="date" name="NgaySanXuat" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Hạn sử dụng</label>
            <input type="date" name="NgayHetHan" class="form-control" required>
        </div>

        <button class="btn btn-success">Lưu lô hàng</button>
        <br>
        <a href="lo-hang.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

</body>
</html>
