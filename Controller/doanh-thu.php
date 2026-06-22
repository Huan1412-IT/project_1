<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập'); window.location.href='../login.php';</script>";
    exit();
}

// Doanh thu hôm nay
$today = date('Y-m-d');
$dt_homnay = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(TongTien),0) AS Tong
    FROM HoaDon
    WHERE DATE(NgayTao) = '$today'
"))['Tong'];

// Doanh thu tháng này
$month = date('Y-m');
$dt_thang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(TongTien),0) AS Tong
    FROM HoaDon
    WHERE DATE_FORMAT(NgayTao,'%Y-%m') = '$month'
"))['Tong'];

// Doanh thu tổng
$dt_tong = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT IFNULL(SUM(TongTien),0) AS Tong
    FROM HoaDon
"))['Tong'];

// Doanh thu 7 ngày gần nhất
$chart = mysqli_query($conn, "
    SELECT DATE(NgayTao) AS Ngay, SUM(TongTien) AS Tong
    FROM HoaDon
    GROUP BY DATE(NgayTao)
    ORDER BY Ngay DESC
    LIMIT 7
");

$ngay = [];
$tien = [];
while ($row = mysqli_fetch_assoc($chart)) {
    $ngay[] = date('d/m', strtotime($row['Ngay']));
    $tien[] = $row['Tong'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Doanh thu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container mt-4">

<h3 class="fw-bold text-success mb-4">📊 THỐNG KÊ DOANH THU</h3>

<!-- THẺ DOANH THU -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-success">
            <div class="card-body">
                <h6>Doanh thu hôm nay</h6>
                <h4 class="text-success fw-bold"><?= number_format($dt_homnay) ?> đ</h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-primary">
            <div class="card-body">
                <h6>Doanh thu tháng này</h6>
                <h4 class="text-primary fw-bold"><?= number_format($dt_thang) ?> đ</h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-danger">
            <div class="card-body">
                <h6>Tổng doanh thu</h6>
                <h4 class="text-danger fw-bold"><?= number_format($dt_tong) ?> đ</h4>
            </div>
        </div>
    </div>
</div>

<!-- BIỂU ĐỒ -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="mb-3">📈 Doanh thu 7 ngày gần nhất</h5>
        <canvas id="doanhthuChart"></canvas>
    </div>
</div>

<!-- BẢNG HÓA ĐƠN -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="mb-3">📋 Danh sách hóa đơn</h5>
        <table class="table table-bordered">
            <thead class="table-success">
                <tr>
                    <th>Mã HĐ</th>
                    <th>Ngày</th>
                    <th>Nhân viên</th>
                    <th>Tổng tiền</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $hd = mysqli_query($conn, "
                SELECT h.MaHD, h.NgayTao, h.TongTien, n.HoTen
                FROM HoaDon h
                JOIN NguoiDung n ON h.MaND = n.MaND
                ORDER BY h.NgayTao DESC
            ");
            while ($row = mysqli_fetch_assoc($hd)):
            ?>
                <tr>
                    <td>#<?= $row['MaHD'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['NgayTao'])) ?></td>
                    <td><?= $row['HoTen'] ?></td>
                    <td class="text-danger fw-bold"><?= number_format($row['TongTien']) ?> đ</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<script>
const ctx = document.getElementById('doanhthuChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_reverse($ngay)) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode(array_reverse($tien)) ?>,
            backgroundColor: '#198754'
        }]
    }
});
</script>

</body>
</html>
