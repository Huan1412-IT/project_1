<?php
session_start();
include '../db.php'; // Đường dẫn ngược ra 1 cấp để vào file db.php

// 1. Kiểm tra nếu không có ID sản phẩm thì quay về trang chủ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Kiểm tra sản phẩm có tồn tại và còn hàng trong DB không
$check_sql = "SELECT TenSP, SoLuongTong FROM SanPham WHERE MaSP = '$id'";
$result = mysqli_query($conn, $check_sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='../index.php';</script>";
    exit();
}

if ($product['SoLuongTong'] <= 0) {
    echo "<script>alert('Sản phẩm này đã hết hàng!'); window.location.href='../index.php';</script>";
    exit();
}

// 3. Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// 4. Xử lý thêm sản phẩm vào giỏ
// Nếu sản phẩm đã có trong giỏ thì tăng số lượng lên 1
if (isset($_SESSION['cart'][$id])) {
    // Kiểm tra nếu tăng thêm 1 có vượt quá số lượng kho không
    if ($_SESSION['cart'][$id] + 1 <= $product['SoLuongTong']) {
        $_SESSION['cart'][$id]++;
    } else {
        echo "<script>alert('Số lượng trong giỏ đã đạt giới hạn tồn kho!'); window.location.href='../index.php';</script>";
        exit();
    }
} else {
    // Nếu chưa có thì thêm mới với số lượng là 1
    $_SESSION['cart'][$id] = 1;
}

// 5. Thông báo thành công và quay lại trang chủ
// Sử dụng JavaScript để giữ trải nghiệm mượt mà
echo "<script>
    alert('Đã thêm " . $product['TenSP'] . " vào giỏ hàng!');
    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "'; 
</script>";
exit();
?>