<?php
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM NhaCungCap WHERE MaNCC=$id");

header("Location: NhaCC.php");
exit();
