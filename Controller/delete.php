<?php 
include '../db.php'; 
// if (!isset($_SESSION['user'])) header("Location: login.php");

// Kiểm tra xem có ID được gửi đến không
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Thực hiện lệnh xóa
    $sql = "DELETE FROM products WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        // Xóa thành công, chuyển hướng về trang chủ
        echo "<script>alert('Xóa thành công!'); window.location.href='../index.php';</script>";
        exit();
    } else {
        echo "Lỗi khi xóa sản phẩm: " . mysqli_error($conn);
    }
} else {
    // Nếu không có ID, quay về trang chủ
    echo "<script>alert('Xóa Không thành công!'); window.location.href='../index.php';</script>";
    exit();
}
?>