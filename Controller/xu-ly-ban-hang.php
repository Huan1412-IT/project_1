<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$TenDangNhap = $_SESSION['user']; // 👈 là chuỗi

// Lấy MaND từ username
$sqlND = "SELECT MaND FROM NguoiDung WHERE TenDangNhap = '$TenDangNhap'";
$resND = mysqli_query($conn, $sqlND);
$nd = mysqli_fetch_assoc($resND);

if (!$nd) {
    die("Không tìm thấy nhân viên!");
}

$MaND = $nd['MaND']; // ✅ giờ mới đúng

if (!isset($_POST['save_order']) || empty($_POST['soluong'])) {
    die("Không có sản phẩm nào được chọn!");
}

$soluong = $_POST['soluong'];
$tongTien = 0;
$dsSanPham = [];

foreach ($soluong as $MaLo => $qty) {
    if ($qty > 0) {
        $sql = "
        SELECT sp.TenSP, sp.GiaBan, lh.SoLuongTon
        FROM LoHang lh
        JOIN SanPham sp ON lh.MaSP = sp.MaSP
        WHERE lh.MaLo = $MaLo
        ";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);

        if ($row && $qty <= $row['SoLuongTon']) {
            $thanhtien = $qty * $row['GiaBan'];
            $tongTien += $thanhtien;

            $dsSanPham[] = [
                'MaLo' => $MaLo,
                'TenSP' => $row['TenSP'],
                'GiaBan' => $row['GiaBan'],
                'SoLuong' => $qty,
                'ThanhTien' => $thanhtien
            ];
        }
    }
}

if (empty($dsSanPham)) {
    die("Bạn chưa chọn sản phẩm nào!");
}

// 👉 Lưu hóa đơn
mysqli_query($conn, "INSERT INTO HoaDon(MaND, TongTien) VALUES($MaND, $tongTien)");
$MaHD = mysqli_insert_id($conn);

// 👉 Lưu chi tiết
foreach ($dsSanPham as $sp) {
    mysqli_query($conn, "
        INSERT INTO ChiTietHoaDon(MaHD, MaLo, SoLuong, GiaBan)
        VALUES($MaHD, {$sp['MaLo']}, {$sp['SoLuong']}, {$sp['GiaBan']})
    ");

    mysqli_query($conn, "
        UPDATE LoHang 
        SET SoLuongTon = SoLuongTon - {$sp['SoLuong']}
        WHERE MaLo = {$sp['MaLo']}
    ");
}

// 👉 Chuyển sang trang in
header("Location: hoa-don-chi-tiet.php?id=$MaHD");
exit();
