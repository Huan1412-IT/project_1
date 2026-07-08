<?php
session_start();
require_once __DIR__ . '/../Database/db.php';

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
SELECT h.MaHD, h.NgayTao, h.TongTien, n.HoTen
FROM HoaDon h
JOIN NguoiDung n ON h.MaND = n.MaND
";

if($search != ""){
    $sql .= " WHERE h.MaHD LIKE '%$search%'
           OR n.HoTen LIKE '%$search%'";
}

$sql .= " ORDER BY h.MaHD DESC";

$result = mysqli_query($conn,$sql);

// Thống kê
$countHD = mysqli_num_rows($result);

$tongTien = 0;
while($r = mysqli_fetch_assoc($result)){
    $tongTien += $r['TongTien'];
}

mysqli_data_seek($result,0);
?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<title>Quản lý hóa đơn</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f5f6fa;
}

.card{
    border:none;
    border-radius:15px;
}

.table tbody tr{
    transition:.2s;
}

.table tbody tr:hover{
    background:#eef7ff;
    transform:scale(1.01);
}

.badge{
    font-size:14px;
}

</style>

</head>

<body>

<div class="container py-4">

<div class="card shadow">

<div class="card-header bg-success text-white">

<div class="d-flex justify-content-between align-items-center">

<h3 class="mb-0">

<i class="bi bi-receipt-cutoff"></i>

Quản lý hóa đơn

</h3>

<a href="tao-hoa-don.php" class="btn btn-light">

<i class="bi bi-plus-circle"></i>

Tạo hóa đơn

</a>

</div>

</div>

<div class="card-body">

<!-- Thống kê -->

<div class="row mb-4">

<div class="col-md-6">

<div class="card bg-primary text-white shadow">

<div class="card-body">

<h6>Tổng hóa đơn</h6>

<h2><?= $countHD ?></h2>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card bg-success text-white shadow">

<div class="card-body">

<h6>Tổng doanh thu</h6>

<h2><?= number_format($tongTien,0,",",".") ?> đ</h2>

</div>

</div>

</div>

</div>

<!-- Tìm kiếm -->

<form method="GET" class="row mb-3">

<div class="col-md-5">

<input
type="text"
name="search"
class="form-control"
placeholder="Nhập mã hóa đơn hoặc tên nhân viên..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-2">

<button class="btn btn-success w-100">

<i class="bi bi-search"></i>

Tìm

</button>

</div>

<div class="col-md-2">

<a href="hoa-don.php" class="btn btn-secondary w-100">

Làm mới

</a>

</div>

</form>

<!-- Bảng -->

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>

<th width="100">Mã HD</th>

<th>Nhân viên</th>

<th width="180">Ngày tạo</th>

<th width="180">Tổng tiền</th>

<th width="180">Thao tác</th>

</tr>

</thead>

<tbody>

<?php if(mysqli_num_rows($result)>0): ?>

<?php while($row=mysqli_fetch_assoc($result)): ?>

<tr>

<td>

<span class="badge bg-primary">

#<?= $row['MaHD'] ?>

</span>

</td>

<td>

<?= htmlspecialchars($row['HoTen']) ?>

</td>

<td>

<?= date("d/m/Y H:i",strtotime($row['NgayTao'])) ?>

</td>

<td class="text-danger fw-bold">

<?= number_format($row['TongTien'],0,",",".") ?> đ

</td>

<td>

<a
href="hoa-don-chi-tiet.php?id=<?= $row['MaHD'] ?>"
class="btn btn-info btn-sm">

<i class="bi bi-eye"></i>

</a>

<a
href="in-hoa-don.php?id=<?= $row['MaHD'] ?>"
class="btn btn-success btn-sm">

<i class="bi bi-printer"></i>

</a>

<a
href="xoa-hoa-don.php?id=<?= $row['MaHD'] ?>"
class="btn btn-danger btn-sm"

onclick="return confirm('Bạn có chắc muốn xóa hóa đơn này?')">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="5" class="text-center text-muted">

Không có dữ liệu

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</body>

</html>