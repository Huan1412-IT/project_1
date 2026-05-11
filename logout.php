<?php
// 1. Bật hiển thị lỗi tuyệt đối
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Thử nghiệm kết nối thủ công (không dùng include db.php để loại trừ lỗi từ file đó)
$conn = mysqli_connect("localhost", "root", "", "quanlynhathuoc");

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

session_start();
session_unset();
session_destroy();

echo "Đang chuyển hướng về trang đăng nhập...";
echo "<script>window.location.href='login.php';</script>";
exit();
?>