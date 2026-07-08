<body style="background:#f4f7fb;">

<div class="container py-4">

    <div class="card shadow-lg border-0 rounded-4">

        <div class="card-header text-white d-flex justify-content-between align-items-center"
            style="background:linear-gradient(135deg,#00b894,#2ecc71);">

            <div>
                <h3 class="mb-0">
                    💊 Quản lý lô hàng
                </h3>

                <small>Quản lý hạn sử dụng sản phẩm</small>

            </div>

            <a href="ThemLH.php" class="btn btn-light fw-bold">
                ➕ Thêm lô hàng
            </a>

        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-4">

                    <div class="alert alert-success shadow-sm">

                        📦 Tổng số lô hàng:

                        <strong>
                            <?= mysqli_num_rows($result); ?>
                        </strong>

                    </div>

                </div>

                <div class="col-md-8">

                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="🔍 Tìm theo tên sản phẩm hoặc nhà cung cấp...">

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle text-center" id="myTable">

                    <thead>

                        <tr>

                            <th>Sản phẩm</th>

                            <th>Nhà cung cấp</th>

                            <th>Giá nhập</th>

                            <th>Số lượng tồn</th>

                            <th>Ngày SX</th>

                            <th>HSD</th>

                            <th>Trạng thái</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($row=mysqli_fetch_assoc($result)):

                        $days=(strtotime($row['NgayHetHan'])-time())/86400;

                        if($days<0){

                            $status="<span class='badge rounded-pill bg-danger px-3 py-2'>Hết hạn</span>";

                        }

                        elseif($days<=30){

                            $status="<span class='badge rounded-pill bg-warning text-dark px-3 py-2'>Sắp hết hạn</span>";

                        }

                        else{

                            $status="<span class='badge rounded-pill bg-success px-3 py-2'>Còn hạn</span>";

                        }

                    ?>

                    <tr>

                        <td><?= htmlspecialchars($row['TenSP']) ?></td>

                        <td><?= htmlspecialchars($row['TenNCC']) ?></td>

                        <td class="text-danger fw-bold">

                            <?= number_format($row['GiaNhap'],0,',','.') ?> đ

                        </td>

                        <td>

                            <span class="badge bg-info">

                                <?= $row['SoLuongTon'] ?>

                            </span>

                        </td>

                        <td><?= $row['NgaySanXuat'] ?></td>

                        <td><?= $row['NgayHetHan'] ?></td>

                        <td><?= $status ?></td>

                    </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>