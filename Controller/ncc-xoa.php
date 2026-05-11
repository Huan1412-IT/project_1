<?php
include '../db.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM NhaCungCap WHERE MaNCC=$id");

header("Location: NhaCC.php");
exit();
