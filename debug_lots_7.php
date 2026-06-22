<?php
require_once __DIR__ . '/Database/db.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("No DB connection\n");
}
$sp = 7;
$sql = "SELECT MaLo, MaSP, SoLuongTon, NgayHetHan FROM LoHang WHERE MaSP = '$sp' ORDER BY NgayHetHan ASC";
$res = mysqli_query($conn, $sql);
if (!$res) { die('Query error: ' . mysqli_error($conn) . "\n"); }
$num = mysqli_num_rows($res);
echo "Found rows: $num\n";
if ($num == 0) { echo "No lots for product $sp\n"; }
while ($r = mysqli_fetch_assoc($res)) {
    echo json_encode($r) . "\n";
}

// Show SanPham info
$sql2 = "SELECT MaSP, TenSP, SoLuongTong FROM SanPham WHERE MaSP = '$sp'";
$res2 = mysqli_query($conn, $sql2);
if ($res2) {
    $prod = mysqli_fetch_assoc($res2);
    echo "Product: " . json_encode($prod) . "\n";
} else {
    echo "Product query error: " . mysqli_error($conn) . "\n";
}
?>