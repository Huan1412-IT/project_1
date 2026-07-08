<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

$result = mysqli_query($conn,"
    SELECT h.MaHD, h.NgayTao, h.TongTien, n.HoTen
    FROM HoaDon h
    JOIN NguoiDung n ON h.MaND = n.MaND
    ORDER BY h.MaHD DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Hóa đơn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
<h4>📜 Lịch sử hóa đơn</h4>

<table class="table table-bordered table-hover">
<thead class="table-success">
<tr>
<th>Mã HD</th>
<th>Nhân viên</th>
<th>Ngày</th>
<th>Tổng tiền</th>
<th></th>
</tr>
</thead>
//ghi chu sql sua lan 1
<tbody>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
<td>#<?= $row['MaHD'] ?></td>
<td><?= $row['HoTen'] ?></td>
<td><?= $row['NgayTao'] ?></td>
<td><?= number_format($row['TongTien']) ?>đ</td>
<td>
<a href="hoa-don-chi-tiet.php?id=<?= $row['MaHD'] ?>" class="btn btn-sm btn-info">
Chi tiết
</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</body>
</html>
