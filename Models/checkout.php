<?php
// 1. Kiểm tra session trước khi khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../db.php';

// 2. Kiểm tra quyền truy cập chặt chẽ hơn
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location.href='../login.php';</script>";
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "<script>alert('Giỏ hàng trống!'); window.location.href='../index.php';</script>";
    exit();
}

// 3. Gán biến từ Session đã được sửa ở file login.php
$ma_nguoi_dung = $_SESSION['user_id']; 
$cart = $_SESSION['cart'];
$tong_tien = 0;

// 4. Bắt đầu giao dịch (Transaction)
mysqli_begin_transaction($conn);

try {
    // Bước A: Lấy giá và kiểm tra sản phẩm tồn tại
    $ids = implode(',', array_keys($cart));
    $sql_price = "SELECT MaSP, GiaBan FROM SanPham WHERE MaSP IN ($ids)";
    $res_price = mysqli_query($conn, $sql_price);
    
    $prices = [];
    while ($row = mysqli_fetch_assoc($res_price)) {
        $prices[$row['MaSP']] = $row['GiaBan'];
        $tong_tien += $row['GiaBan'] * $cart[$row['MaSP']];
    }

    // Bước B: Tạo Hóa Đơn (Đúng cột NgayTao trong DB của bạn)
    $sql_hd = "INSERT INTO HoaDon (MaND, TongTien, NgayTao) VALUES ('$ma_nguoi_dung', '$tong_tien', NOW())";
    if (!mysqli_query($conn, $sql_hd)) {
        throw new Exception("Lỗi tạo hóa đơn: " . mysqli_error($conn));
    }
    $ma_hd = mysqli_insert_id($conn);

    // Bước C: Duyệt giỏ hàng để lưu chi tiết và trừ kho
    foreach ($cart as $id_sp => $sl_mua) {
        
        // Tìm lô hàng còn hạn và còn hàng (FIFO - Ưu tiên lô hết hạn trước)
        $sql_lo = "SELECT MaLo, SoLuongTon FROM LoHang 
                   WHERE MaSP = '$id_sp' AND SoLuongTon > 0 AND NgayHetHan > CURDATE() 
                   ORDER BY NgayHetHan ASC LIMIT 1";
        $res_lo = mysqli_query($conn, $sql_lo);
        $lo_hang = mysqli_fetch_assoc($res_lo);

        if (!$lo_hang) {
            throw new Exception("Sản phẩm ID $id_sp hiện không có lô hàng nào còn hạn sử dụng!");
        }
        
        if ($lo_hang['SoLuongTon'] < $sl_mua) {
            throw new Exception("Số lượng yêu cầu cho SP ID $id_sp vượt quá tồn kho của lô hiện tại!");
        }

        $ma_lo = $lo_hang['MaLo'];
        $gia_ban = $prices[$id_sp];

        // Lưu vào ChiTietHoaDon
        $sql_ct = "INSERT INTO ChiTietHoaDon (MaHD, MaLo, SoLuong, GiaBan) 
                   VALUES ('$ma_hd', '$ma_lo', '$sl_mua', '$gia_ban')";
        if (!mysqli_query($conn, $sql_ct)) {
            throw new Exception("Lỗi lưu chi tiết đơn hàng: " . mysqli_error($conn));
        }

        // Trừ kho ở bảng LoHang
        $sql_update_lo = "UPDATE LoHang SET SoLuongTon = SoLuongTon - $sl_mua WHERE MaLo = '$ma_lo'";
        mysqli_query($conn, $sql_update_lo);

        // Cập nhật lại tổng kho ở bảng SanPham để đồng bộ hiển thị ngoài trang chủ
        $sql_update_sp = "UPDATE SanPham SET SoLuongTong = (SELECT SUM(SoLuongTon) FROM LoHang WHERE MaSP = '$id_sp') WHERE MaSP = '$id_sp'";
        mysqli_query($conn, $sql_update_sp);
    }

    // Nếu không có lỗi gì, xác nhận lưu mọi thay đổi
    mysqli_commit($conn);
    
    // Xóa giỏ hàng
    unset($_SESSION['cart']);

    echo "<script>alert('Đặt hàng thành công! Mã đơn: #$ma_hd'); window.location.href='lich-su-mua-hang.php';</script>";

} catch (Exception $e) {
    // Nếu có bất kỳ lỗi nào, hủy bỏ toàn bộ dữ liệu đã insert/update ở trên
    mysqli_rollback($conn);
    echo "<script>alert('Thanh toán thất bại: " . $e->getMessage() . "'); window.history.back();</script>";
}
?>