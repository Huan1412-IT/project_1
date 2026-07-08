from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

# ==========================================
# Khởi tạo
# ==========================================

driver = webdriver.Chrome()
driver.maximize_window()

wait = WebDriverWait(driver, 20)

PASS = True
note = ""

try:

    print("=" * 90)
    print("TC-HD-001 : THANH TOÁN HÓA ĐƠN")
    print("=" * 90)

    # ==========================================
    # Đăng nhập
    # ==========================================

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

    print("✓ Đăng nhập thành công")

    # ==========================================
    # Mở trang bán hàng
    # ==========================================

    driver.get("http://localhost/duocpham/Controller/ban-hang.php")

    wait.until(
        EC.presence_of_element_located((By.TAG_NAME, "table"))
    )

    print("✓ Đã mở trang bán hàng")

    # ==========================================
    # Nhập số lượng sản phẩm
    # ==========================================

    quantity = wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, "input[type='number']")
        )
    )

    quantity.clear()
    quantity.send_keys("2")

    print("✓ Đã nhập số lượng = 2")

    # ==========================================
    # Nhấn nút Lưu & In hóa đơn
    # ==========================================

    save = wait.until(
        EC.element_to_be_clickable(
            (By.NAME, "save_order")
        )
    )

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        save
    )

    time.sleep(1)

    driver.execute_script(
        "arguments[0].click();",
        save
    )

    print("✓ Đã nhấn nút Lưu & In hóa đơn")

    # ==========================================
    # Chờ chuyển sang trang chi tiết hóa đơn
    # ==========================================

    wait.until(
        EC.url_contains("hoa-don-chi-tiet.php")
    )

    print("✓ Đã chuyển sang trang chi tiết hóa đơn")

    # ==========================================
    # Kiểm tra nội dung hóa đơn
    # ==========================================

    wait.until(
        EC.presence_of_element_located(
            (By.TAG_NAME, "table")
        )
    )

    page = driver.page_source.lower()

    if "chi tiết hóa đơn" in page or "chi tiet hoa don" in page:
        print("✓ Hiển thị hóa đơn thành công")
    else:
        raise Exception("Không tìm thấy nội dung hóa đơn")

    # ==========================================
    # Nhấn nút In hóa đơn
    # ==========================================

    print_button = wait.until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                "//button[contains(.,'In hóa đơn')]"
            )
        )
    )

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        print_button
    )

    time.sleep(1)

    driver.execute_script(
        "arguments[0].click();",
        print_button
    )

    print("✓ Đã nhấn nút In hóa đơn")

    # Chờ hộp thoại Print xuất hiện
    time.sleep(3)

    driver.save_screenshot("TC-HD-001-PASS.png")

    note = (
        "Đăng nhập thành công -> "
        "Mở trang bán hàng -> "
        "Nhập số lượng = 2 -> "
        "Nhấn 'Lưu & In hóa đơn' -> "
        "Hiển thị trang chi tiết hóa đơn -> "
        "Nhấn 'In hóa đơn' thành công."
    )

except Exception as e:

    PASS = False

    note = f"{type(e).__name__}: {e}"

    print("\n✗ Kiểm thử thất bại")
    print("URL hiện tại:", driver.current_url)
    print("Chi tiết lỗi:", e)

    driver.save_screenshot("TC-HD-001-FAIL.png")

finally:

    print()
    print("=" * 95)
    print("KẾT QUẢ TỔNG HỢP")
    print("=" * 95)

    print("{:<15}{:<12}{}".format(
        "TC ID",
        "Kết quả",
        "Ghi chú"
    ))

    print("-" * 95)

    if PASS:
        print("{:<15}{:<12}{}".format(
            "TC-HD-001",
            "PASS",
            note
        ))
    else:
        print("{:<15}{:<12}{}".format(
            "TC-HD-001",
            "FAIL",
            note
        ))

    print("=" * 95)

    input("\nNhấn Enter để đóng...")

    driver.quit()