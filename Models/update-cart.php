<?php
session_start();
$id = $_GET['id'];
$action = $_GET['action'];

if ($action == 'plus') {
    $_SESSION['cart'][$id]++;
} else {
    if ($_SESSION['cart'][$id] > 1) {
        $_SESSION['cart'][$id]--;
    } else {
        unset($_SESSION['cart'][$id]); // Nếu giảm xuống 0 thì xóa luôn
    }
}
header("Location: GioHang.php");
exit();