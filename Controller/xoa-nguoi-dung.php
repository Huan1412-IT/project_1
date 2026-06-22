<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM NguoiDung WHERE MaND=$id");

header("Location: nguoi-dung.php");
exit();
