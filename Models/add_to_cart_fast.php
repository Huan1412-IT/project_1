<?php
session_start();
// Thêm 2 dòng này để hiện lỗi thay vì màn hình trắng
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Verify database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not found in ' . __FILE__);
    die('Lỗi: Không thể kết nối tới Cơ sở dữ liệu. Vui lòng thử lại sau.');
}

if (isset($_POST['buy_now'])) {
    $id = $_POST['id_sp'];
    $sl = $_POST['qty'];
    unset($_SESSION['cart']);
    $_SESSION['cart'][$id] = $sl;
    header("Location: checkout_info.php");
    exit();
}
?>