<?php
// Kiểm tra nếu session chưa được khởi tạo thì mới khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug lỗi (chỉ dùng khi dev)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quanlynhathuoc";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>