from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException
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
    print("TC-TK-001 : THỐNG KÊ DOANH THU")
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
    # Mở trang thống kê
    # ==========================================

    driver.get("http://localhost/duocpham/Controller/doanh-thu.php")

    wait.until(
        EC.presence_of_element_located((By.TAG_NAME, "body"))
    )

    print("✓ Đã mở trang thống kê")

    # ==========================================
    # Kiểm tra có lỗi PHP hay không
    # ==========================================

    page = driver.page_source.lower()

    if "fatal error" in page:
        raise Exception("Trang thống kê đang bị lỗi PHP")

    # ==========================================
    # Kiểm tra tiêu đề
    # ==========================================

    wait.until(
        EC.visibility_of_element_located(
            (
                By.XPATH,
                "//h3[contains(.,'THỐNG KÊ DOANH THU')]"
            )
        )
    )

    print("✓ Hiển thị tiêu đề")

    # ==========================================
    # Kiểm tra 3 thẻ doanh thu
    # ==========================================

    cards = driver.find_elements(
        By.CSS_SELECTOR,
        ".card h4"
    )

    if len(cards) < 3:
        raise Exception("Không đủ 3 thẻ doanh thu")

    print("✓ Hiển thị đầy đủ 3 thẻ doanh thu")

    # ==========================================
    # Kiểm tra dữ liệu doanh thu
    # ==========================================

    for card in cards[:3]:

        value = (
            card.text
            .replace(".", "")
            .replace(",", "")
            .replace("đ", "")
            .replace(" ", "")
        )

        if not value.isdigit():
            raise Exception("Giá trị doanh thu không hợp lệ")

        if int(value) < 0:
            raise Exception("Doanh thu âm")

    print("✓ Giá trị doanh thu hợp lệ")

    # ==========================================
    # Kiểm tra biểu đồ
    # ==========================================

    chart = wait.until(
        EC.presence_of_element_located(
            (By.ID, "doanhthuChart")
        )
    )

    width = chart.size["width"]
    height = chart.size["height"]

    if width == 0 or height == 0:
        raise Exception("Biểu đồ không hiển thị")

    print("✓ Biểu đồ doanh thu hiển thị")

    # ==========================================
    # Kiểm tra bảng hóa đơn
    # ==========================================

    table = wait.until(
        EC.visibility_of_element_located(
            (By.TAG_NAME, "table")
        )
    )

    headers = [
        h.text.strip()
        for h in driver.find_elements(By.CSS_SELECTOR, "thead th")
    ]

    expected = [
        "Mã HĐ",
        "Ngày",
        "Nhân viên",
        "Tổng tiền"
    ]

    for col in expected:

        if col not in headers:
            raise Exception(f"Thiếu cột {col}")

    print("✓ Bảng hóa đơn đúng cấu trúc")

    # ==========================================
    # Kiểm tra dữ liệu bảng
    # ==========================================

    rows = driver.find_elements(
        By.CSS_SELECTOR,
        "tbody tr"
    )

    if len(rows) == 0:
        print("⚠ Không có dữ liệu hóa đơn")
    else:
        print(f"✓ Có {len(rows)} hóa đơn")

    # ==========================================
    # Chụp màn hình
    # ==========================================

    driver.save_screenshot("TC-TK-001-PASS.png")

    note = (
        "Đăng nhập thành công -> "
        "Mở trang thống kê -> "
        "Hiển thị tiêu đề -> "
        "Hiển thị 3 thẻ doanh thu -> "
        "Biểu đồ doanh thu hiển thị -> "
        "Bảng hóa đơn đúng cấu trúc -> "
        "Kiểm tra dữ liệu thành công."
    )

except Exception as e:

    PASS = False

    note = str(e)

    print("\n✗ KIỂM THỬ THẤT BẠI")
    print("URL:", driver.current_url)
    print("Chi tiết:", e)

    driver.save_screenshot("TC-TK-001-FAIL.png")

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

    print("{:<15}{:<12}{}".format(
        "TC-TK-001",
        "PASS" if PASS else "FAIL",
        note
    ))

    print("=" * 95)

    input("\nNhấn Enter để đóng...")

    driver.quit()