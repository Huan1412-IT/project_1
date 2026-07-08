from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
import traceback
import time

driver = webdriver.Chrome()
driver.maximize_window()

wait = WebDriverWait(driver, 20)

PASS = True
note = ""

try:

    print("=" * 90)
    print("TC-NK-001 : KIỂM THỬ CHỨC NĂNG NHẬP KHO THUỐC")
    print("=" * 90)

    # =====================================================
    # Đăng nhập
    # =====================================================

    driver.get("http://localhost/duocpham/login.php")

    wait.until(
        EC.visibility_of_element_located((By.NAME, "username"))
    )

    driver.find_element(By.NAME, "username").send_keys("admin")
    driver.find_element(By.NAME, "password").send_keys("admin123")
    driver.find_element(By.NAME, "login").click()

    wait.until(
        EC.url_contains("index.php")
    )

    print("✔ Đăng nhập thành công")

    # =====================================================
    # Mở trang Quản lý lô hàng
    # =====================================================

    driver.get("http://localhost/duocpham/Controller/LoHang.php")

    wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, "//a[contains(text(),'Thêm lô hàng')]")
        )
    )

    print("✔ Đã mở trang Quản lý lô hàng")

    # =====================================================
    # Click Thêm lô hàng
    # =====================================================

    driver.find_element(
        By.XPATH,
        "//a[contains(text(),'Thêm lô hàng')]"
    ).click()

    wait.until(
        EC.visibility_of_element_located((By.NAME, "MaSP"))
    )

    print("✔ Đã mở Form thêm lô hàng")

    # =====================================================
    # Chọn sản phẩm
    # =====================================================

    Select(
        driver.find_element(By.NAME, "MaSP")
    ).select_by_index(1)

    # =====================================================
    # Chọn nhà cung cấp
    # =====================================================

    Select(
        driver.find_element(By.NAME, "MaNCC")
    ).select_by_index(1)

    # =====================================================
    # Giá nhập
    # =====================================================

    gia = driver.find_element(By.NAME, "GiaNhap")
    gia.clear()
    gia.send_keys("45000")

    # =====================================================
    # Số lượng nhập
    # =====================================================

    sl = driver.find_element(By.NAME, "SoLuongNhap")
    sl.clear()
    sl.send_keys("100")

    # =====================================================
    # Ngày sản xuất
    # =====================================================

    driver.execute_script("""
    document.getElementsByName('NgaySanXuat')[0].value='2026-01-01';
    """)

    # =====================================================
    # Hạn sử dụng
    # =====================================================

    driver.execute_script("""
    document.getElementsByName('NgayHetHan')[0].value='2028-01-01';
    """)

    print("✔ Đã nhập đầy đủ thông tin")

    # =====================================================
    # Nhấn Lưu
    # =====================================================

    driver.find_element(
        By.CSS_SELECTOR,
        "button.btn-success"
    ).click()

    print("✔ Đã nhấn nút Lưu")

    # =====================================================
    # Kiểm tra Alert nếu có
    # =====================================================

    try:

        alert = WebDriverWait(driver,3).until(
            EC.alert_is_present()
        )

        print("Thông báo:", alert.text)

        alert.accept()

        time.sleep(1)

    except:
        pass

    # =====================================================
    # Chờ xử lý
    # =====================================================

    time.sleep(3)

    print("URL:", driver.current_url)

    # =====================================================
    # Kiểm tra kết quả
    # =====================================================

    if "LoHang.php" in driver.current_url or "lo-hang.php" in driver.current_url:

        PASS = True

        note = (
            "Đăng nhập thành công -> "
            "Mở Quản lý lô hàng -> "
            "Thêm lô hàng -> "
            "Chọn sản phẩm -> "
            "Chọn nhà cung cấp -> "
            "Nhập giá nhập -> "
            "Nhập số lượng -> "
            "Nhập NSX/HSD hợp lệ -> "
            "Lưu thành công."
        )

    else:

        PASS = False

        note = "Không chuyển về trang Quản lý lô hàng."

    driver.save_screenshot("TC-NK-001.png")

except Exception as e:

    PASS = False

    traceback.print_exc()

    note = str(e)

    driver.save_screenshot("TC-NK-001-FAIL.png")

    print("\nURL hiện tại :", driver.current_url)

    print("\nTiêu đề :", driver.title)

finally:

    print()
    print("=" * 100)
    print("KẾT QUẢ TỔNG HỢP")
    print("=" * 100)

    print("{:<15}{:<12}{}".format(
        "TC ID",
        "Kết quả",
        "Ghi chú"
    ))

    print("-" * 100)

    if PASS:

        print("{:<15}{:<12}{}".format(
            "TC-NK-001",
            "✅ PASS",
            note
        ))

    else:

        print("{:<15}{:<12}{}".format(
            "TC-NK-001",
            "❌ FAIL",
            note
        ))

    print("=" * 100)

    input("\nNhấn Enter để đóng...")

    driver.quit()