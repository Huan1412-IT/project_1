<?php
session_start();
include '../db.php';

$sql = "
SELECT 
    lh.MaLo,
    sp.TenSP,
    lh.NgayHetHan,
    lh.SoLuongTon,
    sp.GiaBan
FROM LoHang lh
JOIN SanPham sp ON lh.MaSP = sp.MaSP
WHERE lh.SoLuongTon > 0
ORDER BY lh.NgayHetHan ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Lỗi SQL: " . mysqli_error($conn));
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Bán hàng tại quầy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-success">🛒 BÁN HÀNG TẠI QUẦY</h2>

    <form action="xu-ly-ban-hang.php" method="POST">
        <div class="table-responsive bg-white shadow rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Thuốc</th>
                        <th>Hạn dùng</th>
                        <th>Tồn lô</th>
                        <th>Giá</th>
                        <th style="width:140px">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['TenSP']) ?></strong></td>
                        <td><?= $row['NgayHetHan'] ?></td>
                        <td><?= $row['SoLuongTon'] ?></td>
                        <td><?= number_format($row['GiaBan']) ?> đ</td>
                        <td>
                            <input type="number"
                                   name="soluong[<?= $row['MaLo'] ?>]"
                                   class="form-control"
                                   min="0"
                                   max="<?= $row['SoLuongTon'] ?>"
                                   value="0">
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <button type="submit" name="save_order" class="btn btn-success btn-lg">
                💾 Lưu & In hóa đơn
            </button>
        </div>
    </form>
</div>

</body>
</html>
