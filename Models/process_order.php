<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Verify database connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log('Database connection not found in ' . __FILE__);
    die('Lỗi: Không thể kết nối tới Cơ sở dữ liệu. Vui lòng thử lại sau.');
}
 
// Bật lỗi để debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug log helper
$debug_log = __DIR__ . '/../debug_order.log';
function log_debug($msg) {
    global $debug_log;
    @file_put_contents($debug_log, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

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
log_debug('BEGIN process_order - cart: ' . json_encode($cart));

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
    log_debug('Prices: ' . json_encode($prices) . ' Total: ' . $total);

    // 3. Lưu Hóa Đơn (Đã thêm các cột thông tin người nhận)
    $sql_hd = "INSERT INTO HoaDon (MaND, TongTien, NgayTao, HoTenNhan, SDTNhan, DiaChiNhan, GhiChu) 
               VALUES ('$ma_nd', '$total', NOW(), '$ho_ten', '$sdt', '$dia_chi', '$ghi_chu')";
    
    if (!mysqli_query($conn, $sql_hd)) {
        throw new Exception("Lỗi Database: " . mysqli_error($conn));
    }
    $ma_hd = mysqli_insert_id($conn);

    // 4. Lưu Chi Tiết và trừ kho
    foreach ($cart as $id_sp => $sl) {
        log_debug("Processing product $id_sp quantity $sl");
        $remaining = (int)$sl;
        $gia = isset($prices[$id_sp]) ? $prices[$id_sp] : 0;

        // Lấy tất cả các lô còn hạn theo thứ tự hết hạn sớm nhất (FIFO)
        $sql_lo_all = "SELECT MaLo, SoLuongTon FROM LoHang 
                       WHERE MaSP = '$id_sp' AND NgayHetHan > CURDATE() AND SoLuongTon > 0
                       ORDER BY NgayHetHan ASC";
        $res_lo_all = mysqli_query($conn, $sql_lo_all);
        $lots = [];
        while ($r = mysqli_fetch_assoc($res_lo_all)) {
            $lots[] = $r;
        }
        log_debug('Found lots for ' . $id_sp . ': ' . json_encode($lots));

        // Nếu không tìm thấy lô nào nhưng SanPham.SoLuongTong còn hàng, tạo lô tạm để phân bổ
        if (count($lots) === 0) {
            $res_total = mysqli_query($conn, "SELECT SoLuongTong FROM SanPham WHERE MaSP = '$id_sp'");
            $row_total = mysqli_fetch_assoc($res_total);
            $total_stock = (int)($row_total['SoLuongTong'] ?? 0);
            log_debug("Total stock for $id_sp from SanPham: $total_stock");
            if ($total_stock >= $remaining && $total_stock > 0) {
                $exp_date = date('Y-m-d', strtotime('+10 years'));
                // Thêm lô tạm với toàn bộ tồn trong SanPham (có thể là do dữ liệu không đồng bộ)
                mysqli_query($conn, "INSERT INTO LoHang (MaSP, SoLuongTon, NgayHetHan) VALUES ('$id_sp', $total_stock, '$exp_date')");
                log_debug("Inserted synthetic lot for $id_sp with qty $total_stock exp $exp_date");

                // Tải lại danh sách lô
                $res_lo_all = mysqli_query($conn, $sql_lo_all);
                $lots = [];
                while ($r = mysqli_fetch_assoc($res_lo_all)) {
                    $lots[] = $r;
                }
                log_debug('After insert, lots for ' . $id_sp . ': ' . json_encode($lots));
            }
        }

        // Tính tổng tồn kho khả dụng
        $available = 0;
        foreach ($lots as $l) {
            $available += (int)$l['SoLuongTon'];
        }

        if ($available < $remaining) {
            log_debug("INSUFFICIENT stock for $id_sp: available=$available needed=$remaining");
            throw new Exception("Sản phẩm ID $id_sp đã hết hàng hoặc hết hạn sử dụng!");
        }

        // Phân bổ số lượng từ các lô cho tới khi đủ
        foreach ($lots as $lot) {
            if ($remaining <= 0) break;
            $take = min((int)$lot['SoLuongTon'], $remaining);

            // Insert chi tiết cho lô hiện tại
            mysqli_query($conn, "INSERT INTO ChiTietHoaDon (MaHD, MaLo, SoLuong, GiaBan) VALUES ('$ma_hd', '{$lot['MaLo']}', '$take', '$gia')");

            // Cập nhật kho lô và tổng sản phẩm
            mysqli_query($conn, "UPDATE LoHang SET SoLuongTon = SoLuongTon - $take WHERE MaLo = '{$lot['MaLo']}'");
            mysqli_query($conn, "UPDATE SanPham SET SoLuongTong = SoLuongTong - $take WHERE MaSP = '$id_sp'");

            $remaining -= $take;
            log_debug("Allocated $take from lot {$lot['MaLo']} to product $id_sp, remaining=$remaining");
        }
    }

    mysqli_commit($conn);
    unset($_SESSION['cart']); // Xóa giỏ hàng

    echo "<script>alert('Đặt hàng thành công!'); window.location.href='lich-su-mua-hang.php';</script>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    log_debug('EXCEPTION: ' . $e->getMessage() . ' CART=' . json_encode($cart));
    die("Lỗi thanh toán: " . $e->getMessage() . " (đã ghi log vào debug_order.log)");
}
?>