<?php
session_start();
// Thêm 2 dòng này để hiện lỗi thay vì màn hình trắng
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php'; // Phải có ../ nếu file nằm trong thư mục User

if (isset($_POST['buy_now'])) {
    $id = $_POST['id_sp'];
    $sl = $_POST['qty'];
    unset($_SESSION['cart']);
    $_SESSION['cart'][$id] = $sl;
    header("Location: checkout_info.php");
    exit();
}
?>