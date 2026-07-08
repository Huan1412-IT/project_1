<?php
include "../Database/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$sql = "SELECT * FROM NguoiDung WHERE TenDangNhap='$username'";
$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);

    if ($password == $row['MatKhau']) {
        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công",
            "user" => [
                "id" => $row['MaND'],
                "username" => $row['TenDangNhap'],
                "role" => $row['QuyenHan']
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Sai mật khẩu"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Tài khoản không tồn tại"
    ]);
}