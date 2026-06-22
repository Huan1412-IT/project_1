<?php
// 1. Kết nối DB và khởi tạo Session
session_start();
// Include DB using absolute path relative to this file
require_once __DIR__ . '/../Database/db.php';

// Kiểm tra quyền truy cập (Chỉ Admin hoặc Nhân viên mới được vào)
if (!isset($_SESSION['user']) || ($_SESSION['quyen'] != 'admin' && $_SESSION['quyen'] != 'staff')) {
    echo "<script>alert('Bạn không có quyền truy cập trang này!'); window.location.href='login.php';</script>";
    exit();
}

// 2. XỬ LÝ LƯU HÓA ĐƠN KHI BẤM THANH TOÁN (POST)
if (isset($_POST['action']) && $_POST['action'] == 'checkout') {
    $ma_nd = $_SESSION['user_id'] ?? null;
    $ho_ten_nhan = mysqli_real_escape_string($conn, $_POST['customer_name'] ?: 'Khách vãng lai');
    $sdt_nhan = mysqli_real_escape_string($conn, $_POST['customer_phone'] ?: '');
    $dia_chi_nhan = "Bán tại quầy";
    $ghi_chu = "Hóa đơn POS tại quầy";
    
    // Nhận mảng giỏ hàng gửi từ JavaScript
    $cart_items = json_decode($_POST['cart_data'], true);

    if (!$ma_nd) {
        echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập hết hạn!']);
        exit();
    }
    if (empty($cart_items)) {
        echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng trống!']);
        exit();
    }

    // Bắt đầu Transaction
    mysqli_begin_transaction($conn);
    try {
        // Tính tổng tiền từ danh sách sản phẩm gửi lên
        $total = 0;
        $product_ids = array_keys($cart_items);
        $ids_string = implode(',', $product_ids);
        
        $res_sp = mysqli_query($conn, "SELECT MaSP, GiaBan FROM SanPham WHERE MaSP IN ($ids_string)");
        $prices = [];
        while ($r = mysqli_fetch_assoc($res_sp)) {
            $prices[$r['MaSP']] = $r['GiaBan'];
            $total += $r['GiaBan'] * $cart_items[$r['MaSP']];
        }

        // Tạo hóa đơn
        $sql_hd = "INSERT INTO HoaDon (MaND, TongTien, NgayTao, HoTenNhan, SDTNhan, DiaChiNhan, GhiChu) 
                   VALUES ('$ma_nd', '$total', NOW(), '$ho_ten_nhan', '$sdt_nhan', '$dia_chi_nhan', '$ghi_chu')";
        if (!mysqli_query($conn, $sql_hd)) {
            throw new Exception("Lỗi tạo hóa đơn: " . mysqli_error($conn));
        }
        $ma_hd = mysqli_insert_id($conn);

        // Duyệt từng sản phẩm để áp dụng thuật toán FIFO (Trừ lô cận date trước)
        foreach ($cart_items as $id_sp => $sl) {
            $sl_can_tru = $sl;
            
            $sql_lo = "SELECT MaLo, SoLuongTon FROM LoHang 
                       WHERE MaSP = '$id_sp' AND SoLuongTon > 0 AND NgayHetHan > CURDATE() 
                       ORDER BY NgayHetHan ASC";
            $res_lo = mysqli_query($conn, $sql_lo);
            
            $danh_sach_lo = [];
            $tong_ton_kho = 0;
            while ($lo = mysqli_fetch_assoc($res_lo)) {
                $tong_ton_kho += $lo['SoLuongTon'];
                $danh_sach_lo[] = $lo;
            }

            if ($tong_ton_kho < $sl) {
                throw new Exception("Sản phẩm ID $id_sp không đủ số lượng trong các lô còn hạn sử dụng!");
            }

            $gia_ban = $prices[$id_sp];
            foreach ($danh_sach_lo as $lo) {
                if ($sl_can_tru <= 0) break;
                
                $ma_lo = $lo['MaLo'];
                $sl_tru = min($sl_can_tru, $lo['SoLuongTon']);

                // Thêm vào chi tiết hóa đơn
                mysqli_query($conn, "INSERT INTO ChiTietHoaDon (MaHD, MaLo, SoLuong, GiaBan) VALUES ('$ma_hd', '$ma_lo', '$sl_tru', '$gia_ban')");
                // Trừ kho theo lô
                mysqli_query($conn, "UPDATE LoHang SET SoLuongTon = SoLuongTon - $sl_tru WHERE MaLo = '$ma_lo'");
                
                $sl_can_tru -= $sl_tru;
            }
            // Trừ tổng kho ở bảng sản phẩm
            mysqli_query($conn, "UPDATE SanPham SET SoLuongTong = SoLuongTong - $sl WHERE MaSP = '$id_sp'");
        }

        mysqli_commit($conn);
        echo json_encode(['status' => 'success', 'message' => 'Thanh toán thành công!', 'ma_hd' => $ma_hd]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 3. TRUY VẤN DANH SÁCH THUỐC CÒN HÀNG ĐỂ ĐỔ RA GIAO DIỆN POS
$sql_products = "SELECT * FROM SanPham WHERE SoLuongTong > 0 ORDER BY TenSP ASC";
$products_result = mysqli_query($conn, $sql_products);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống bán hàng tại quầy - POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .pos-container { flex: 1; display: flex; overflow: hidden; }
        .product-section { flex: 7; overflow-y: auto; padding: 15px; }
        .cart-section { flex: 5; background: #fff; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; }
        .product-card { transition: all 0.2s; border: 1px solid #e3e6f0; border-radius: 8px; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-color: #1cc88a; }
        .cart-table-wrapper { flex: 1; overflow-y: auto; padding: 10px; }
        .checkout-wrapper { padding: 20px; background: #f8f9fc; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-success px-4 py-2 shadow-sm">
    <a class="navbar-brand fw-bold" href="index.php">
        <i class="fas fa-clinic-medical me-2"></i> POS - BÁN HÀNG TẠI QUẦY
    </a>
    <div class="text-white d-flex align-items-center">
        <span class="me-3 small">
            <i class="fas fa-user-circle me-1"></i> Thu ngân: <strong><?= $_SESSION['hoten'] ?></strong>
        </span>
        <a href="../index.php" class="btn btn-sm btn-outline-light rounded-pill me-2">
            <i class="fas fa-home"></i> Về trang chủ
        </a>
        
        <a href="../logout.php" class="btn btn-sm btn-danger rounded-pill shadow-sm" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất tài khoản?')">
            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
        </a>
    </div>
</nav>

<div class="pos-container">
    
    <div class="product-section">
        <div class="card p-3 mb-3 shadow-sm border-0">
            <div class="input-group">
                <span class="input-group-text bg-success text-white"><i class="fas fa-search"></i></span>
                <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Nhập tên thuốc hoặc danh mục để tìm kiếm nhanh...">
            </div>
        </div>

        <div class="row g-3" id="pos-product-grid">
            <?php while ($row = mysqli_fetch_assoc($products_result)): ?>
                <div class="col-md-4 product-item" data-name="<?= strtolower($row['TenSP']) ?>" data-cate="<?= strtolower($row['DanhMuc']) ?>">
                    <div class="card h-100 product-card p-2" onclick="addToCart(<?= $row['MaSP'] ?>, '<?= htmlspecialchars($row['TenSP']) ?>', <?= $row['GiaBan'] ?>, <?= $row['SoLuongTong'] ?>)">
                        <div class="card-body p-1 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-light text-success border border-success mb-1"><?= $row['DanhMuc'] ?></span>
                                <h6 class="fw-bold text-dark text-truncate mb-1"><?= $row['TenSP'] ?></h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="text-danger fw-bold"><?= number_format($row['GiaBan'], 0, ',', '.') ?>đ</span>
                                <span class="small text-muted">Kho: <strong class="text-primary"><?= $row['SoLuongTong'] ?></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="cart-section">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-success mb-0"><i class="fas fa-shopping-basket me-2"></i>Đơn hàng chờ xuất</h5>
            <button class="btn btn-sm btn-outline-danger" onclick="clearCart()"><i class="fas fa-trash-alt"></i> Xóa hết</button>
        </div>

        <div class="cart-table-wrapper">
            <table class="table table-hover align-middle" id="cart-table">
                <thead class="table-light">
                    <tr>
                        <th>Tên thuốc</th>
                        <th width="120" class="text-center">Số lượng</th>
                        <th class="text-end">Tổng tiền</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody id="cart-tbody">
                    </tbody>
            </table>
            <div id="empty-cart-msg" class="text-center text-muted py-5">
                <i class="fas fa-cart-plus fa-3x mb-3 text-opacity-25 text-secondary"></i>
                <p>Chưa có sản phẩm nào được chọn</p>
            </div>
        </div>

        <div class="checkout-wrapper">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <input type="text" id="cust-name" class="form-control form-control-sm" placeholder="Tên khách hàng (nếu có)">
                </div>
                <div class="col-6">
                    <input type="text" id="cust-phone" class="form-control form-control-sm" placeholder="Số điện thoại">
                </div>
            </div>

            <div class="bg-white p-3 rounded border shadow-sm mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Tổng cộng tiền hàng:</span>
                    <span class="fs-4 fw-bold text-danger" id="total-amount">0đ</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-secondary">Tiền khách đưa:</span>
                    <input type="number" id="cash-received" class="form-control form-control-sm text-end fw-bold text-success fs-5" style="width: 150px;" placeholder="0" oninput="calculateChange()">
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fw-bold text-secondary">Tiền trả lại khách:</span>
                    <span class="fs-5 fw-bold text-primary" id="change-return">0đ</span>
                </div>
            </div>

            <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow" onclick="submitPOSOrder()">
                <i class="fas fa-check-circle me-2"></i> XÁC NHẬN THANH TOÁN
            </button>
        </div>
    </div>

</div>

<script>
let cart = {}; // Object lưu trạng thái giỏ hàng: { id_sp: { name, price, qty, maxStock } }

// 1. Chức năng Live Search lọc thuốc theo thời gian thực (Không load lại trang)
document.getElementById('search-input').addEventListener('input', function(e) {
    let keyword = e.target.value.toLowerCase().trim();
    let items = document.querySelectorAll('.product-item');
    
    items.forEach(function(item) {
        let name = item.getAttribute('data-name');
        let cate = item.getAttribute('data-cate');
        if (name.includes(keyword) || cate.includes(keyword)) {
            item.style.display = "block";
        } else {
            item.style.display = "none";
        }
    });
});

// 2. Thêm thuốc vào giỏ hàng POS
function addToCart(id, name, price, maxStock) {
    if (cart[id]) {
        if (cart[id].qty >= maxStock) {
            alert("Số lượng thuốc trong kho đã đạt giới hạn tối đa!");
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = { name: name, price: price, qty: 1, maxStock: maxStock };
    }
    renderCart();
}

// 3. Cập nhật số lượng trực tiếp trong bảng
function updateQty(id, newQty) {
    let qty = parseInt(newQty);
    if (isNaN(qty) || qty <= 0) {
        delete cart[id];
    } else if (qty > cart[id].maxStock) {
        alert("Kho chỉ còn tối đa: " + cart[id].maxStock + " sản phẩm!");
        cart[id].qty = cart[id].maxStock;
    } else {
        cart[id].qty = qty;
    }
    renderCart();
}

// 4. Xóa sản phẩm khỏi giỏ hàng
function removeItem(id) {
    delete cart[id];
    renderCart();
}

// 5. Xóa toàn bộ giỏ hàng
function clearCart() {
    cart = {};
    document.getElementById('cust-name').value = '';
    document.getElementById('cust-phone').value = '';
    document.getElementById('cash-received').value = '';
    renderCart();
}

// 6. Đổ dữ liệu từ Object giỏ hàng ra HTML bảng bên phải
function renderCart() {
    let tbody = document.getElementById('cart-tbody');
    let emptyMsg = document.getElementById('empty-cart-msg');
    tbody.innerHTML = '';
    
    let total = 0;
    let hasItems = false;

    for (let id in cart) {
        hasItems = true;
        let item = cart[id];
        let subtotal = item.price * item.qty;
        total += subtotal;

        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="fw-bold text-dark small">${item.name}</div>
                <small class="text-muted">${item.price.toLocaleString('vi-VN')}đ</small>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-center fw-bold" 
                       value="${item.qty}" min="1" max="${item.maxStock}" onchange="updateQty(${id}, this.value)">
            </td>
            <td class="text-end fw-bold text-dark">${subtotal.toLocaleString('vi-VN')}đ</td>
            <td>
                <button class="btn btn-link text-danger p-0 m-0" onclick="removeItem(${id})"><i class="fas fa-times"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    if (hasItems) {
        emptyMsg.style.display = 'none';
    } else {
        emptyMsg.style.display = 'block';
    }

    document.getElementById('total-amount').innerText = total.toLocaleString('vi-VN') + 'đ';
    document.getElementById('total-amount').setAttribute('data-value', total);
    calculateChange();
}

// 7. Tự động tính toán tiền thừa trả khách
function calculateChange() {
    let total = parseInt(document.getElementById('total-amount').getAttribute('data-value')) || 0;
    let received = parseInt(document.getElementById('cash-received').value) || 0;
    let change = received - total;

    let changeEl = document.getElementById('change-return');
    if (change >= 0 && total > 0) {
        changeEl.innerText = change.toLocaleString('vi-VN') + 'đ';
        changeEl.className = "fs-5 fw-bold text-primary";
    } else {
        changeEl.innerText = '0đ';
        changeEl.className = "fs-5 fw-bold text-muted";
    }
}

// 8. Đẩy dữ liệu đơn hàng POS lên Server bằng AJAX nhận phản hồi bảo mật
function submitPOSOrder() {
    let total = parseInt(document.getElementById('total-amount').getAttribute('data-value')) || 0;
    if (total <= 0) {
        alert("Vui lòng chọn ít nhất một sản phẩm để thanh toán!");
        return;
    }

    let customerName = document.getElementById('cust-name').value;
    let customerPhone = document.getElementById('cust-phone').value;
    
    // Gói gọn danh sách sản phẩm theo định dạng đơn giản {ma_sp: so_luong}
    let cartData = {};
    for (let id in cart) {
        cartData[id] = cart[id].qty;
    }

    // Gửi yêu cầu AJAX POST
    let formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('customer_name', customerName);
    formData.append('customer_phone', customerPhone);
    formData.append('cart_data', JSON.stringify(cartData));

    fetch('/Models/staff.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            // Sau khi thành công, reset giỏ hàng
            clearCart();
            // Load lại trang để cập nhật lượng tồn kho mới nhất trên lưới sản phẩm
            window.location.reload();
        } else {
            alert("Lỗi thanh toán: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Có lỗi kết nối mạng xảy ra!");
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>