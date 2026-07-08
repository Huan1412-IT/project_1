<?php
require_once __DIR__ . '/../Database/db.php';

// Hôm nay
$homNay = date('Y-m-d');
$sql = mysqli_query($conn,"
SELECT
    IFNULL(SUM(TongTien),0) AS DoanhThu,
    COUNT(*) AS SoHoaDon
FROM HoaDon
WHERE DATE(NgayTao)='$homNay'
");
$today = mysqli_fetch_assoc($sql);

// Tháng này
$thang = date('Y-m');
$sql = mysqli_query($conn,"
SELECT
    IFNULL(SUM(TongTien),0) AS DoanhThu
FROM HoaDon
WHERE DATE_FORMAT(NgayTao,'%Y-%m')='$thang'
");
$month = mysqli_fetch_assoc($sql);

// Năm nay
$nam = date('Y');
$sql = mysqli_query($conn,"
SELECT
    IFNULL(SUM(TongTien),0) AS DoanhThu
FROM HoaDon
WHERE YEAR(NgayTao)='$nam'
");
$year = mysqli_fetch_assoc($sql);

// Tổng doanh thu
$sql = mysqli_query($conn,"
SELECT
    IFNULL(SUM(TongTien),0) AS DoanhThu,
    COUNT(*) AS TongHoaDon,
    AVG(TongTien) AS TrungBinh
FROM HoaDon
");
$total = mysqli_fetch_assoc($sql);
?>