<?php
session_start();
include '../db.php';

// Bật lỗi để debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_POST['confirm_order']) || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit();
}

// 1. Lấy thông tin từ form và session
$ma_nd = $_SESSION['user_id'] ?? null;
$ho_ten = mysqli_real_escape_string($conn, $_POST['full_name']);
$sdt = mysqli_real_escape_string($conn, $_POST['phone']);
$dia_chi = mysqli_real_escape_string($conn, $_POST['address']);
$ghi_chu = mysqli_real_escape_string($conn, $_POST['note'] ?? '');
$cart = $_SESSION['cart'];

if (!$ma_nd) {
    die("Lỗi: Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.");
}

mysqli_begin_transaction($conn);

try {
    // 2. Tính tổng tiền
    $total = 0;
    $ids = implode(',', array_keys($cart));
    $res_sp = mysqli_query($conn, "SELECT MaSP, GiaBan FROM SanPham WHERE MaSP IN ($ids)");
    $prices = [];
    while ($r = mysqli_fetch_assoc($res_sp)) {
        $prices[$r['MaSP']] = $r['GiaBan'];
        $total += $r['GiaBan'] * $cart[$r['MaSP']];
    }

    // 3. Lưu Hóa Đơn (Đã thêm các cột thông tin người nhận)
    $sql_hd = "INSERT INTO HoaDon (MaND, TongTien, NgayTao, HoTenNhan, SDTNhan, DiaChiNhan, GhiChu) 
               VALUES ('$ma_nd', '$total', NOW(), '$ho_ten', '$sdt', '$dia_chi', '$ghi_chu')";
    
    if (!mysqli_query($conn, $sql_hd)) {
        throw new Exception("Lỗi Database: " . mysqli_error($conn));
    }
    $ma_hd = mysqli_insert_id($conn);

    // 4. Lưu Chi Tiết và trừ kho
    foreach ($cart as $id_sp => $sl) {
        $sql_lo = "SELECT MaLo, SoLuongTon FROM LoHang 
                   WHERE MaSP = '$id_sp' AND SoLuongTon >= $sl AND NgayHetHan > CURDATE() 
                   ORDER BY NgayHetHan ASC LIMIT 1";
        $res_lo = mysqli_query($conn, $sql_lo);
        $lo = mysqli_fetch_assoc($res_lo);

        if (!$lo) {
            throw new Exception("Sản phẩm ID $id_sp đã hết hàng hoặc hết hạn sử dụng!");
        }

        $ma_lo = $lo['MaLo'];
        $gia = $prices[$id_sp];

        // Insert chi tiết
        mysqli_query($conn, "INSERT INTO ChiTietHoaDon (MaHD, MaLo, SoLuong, GiaBan) VALUES ('$ma_hd', '$ma_lo', '$sl', '$gia')");
        
        // Cập nhật kho
        mysqli_query($conn, "UPDATE LoHang SET SoLuongTon = SoLuongTon - $sl WHERE MaLo = '$ma_lo'");
        mysqli_query($conn, "UPDATE SanPham SET SoLuongTong = SoLuongTong - $sl WHERE MaSP = '$id_sp'");
    }

    mysqli_commit($conn);
    unset($_SESSION['cart']); // Xóa giỏ hàng

    echo "<script>alert('Đặt hàng thành công!'); window.location.href='lich-su-mua-hang.php';</script>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Lỗi thanh toán: " . $e->getMessage());
}
?>