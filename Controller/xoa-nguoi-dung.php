<?php
include '../db.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM NguoiDung WHERE MaND=$id");

header("Location: nguoi-dung.php");
exit();
