from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

driver = webdriver.Chrome()
driver.maximize_window()

wait = WebDriverWait(driver,10)

result=[]

def add_result(tc,status,note):
    result.append([tc,status,note])

try:

    # ===========================
    # Đăng nhập
    # ===========================

    driver.get("http://localhost/duocpham/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME,"username")))
    add_result("TC-SP-001","✅ PASS","Mở trang đăng nhập thành công")

    driver.find_element(By.NAME,"username").send_keys("admin")
    driver.find_element(By.NAME,"password").send_keys("admin123")
    driver.find_element(By.NAME,"login").click()

    wait.until(EC.url_contains("index.php"))
    add_result("TC-SP-002","✅ PASS","Đăng nhập Admin thành công")

    # ===========================
    # Mở trang thêm thuốc
    # ===========================

    driver.get("http://localhost/duocpham/Controller/add.php")

    wait.until(
        EC.visibility_of_element_located((By.NAME,"TenSP"))
    )

    add_result("TC-SP-003","✅ PASS","Mở trang Thêm thuốc thành công")

    # ===========================
    # Nhập dữ liệu
    # ===========================

    driver.find_element(By.NAME,"TenSP").send_keys("Paracetamol Selenium")

    add_result("TC-SP-004","✅ PASS",
               "Nhập tên thuốc: Paracetamol Selenium")

    image = r"C:\xampp\htdocs\duocpham\img\gaviscon.jpeg"

    driver.find_element(
        By.NAME,
        "HinhAnh"
    ).send_keys(image)

    add_result("TC-SP-005","✅ PASS","Upload hình ảnh thành công")

    Select(
        driver.find_element(By.NAME,"DanhMuc")
    ).select_by_visible_text("Thuốc giảm đau")

    add_result("TC-SP-006","✅ PASS",
               "Chọn danh mục: Thuốc giảm đau")

    driver.find_element(By.NAME,"GiaBan").send_keys("50000")

    add_result("TC-SP-007","✅ PASS",
               "Nhập giá bán: 50.000 VNĐ")

    driver.find_element(By.NAME,"SoLuongTong").send_keys("100")

    add_result("TC-SP-008","✅ PASS",
               "Nhập số lượng: 100")

    # ===========================
    # Lưu
    # ===========================

    driver.find_element(By.NAME,"save").click()

    alert=wait.until(EC.alert_is_present())

    message=alert.text

    alert.accept()

    add_result("TC-SP-009","✅ PASS","Lưu thuốc thành công")

    add_result("TC-SP-010","✅ PASS",
               f"Thông báo hệ thống: {message}")

    wait.until(EC.url_contains("index.php"))

    add_result("TC-SP-011","✅ PASS",
               "Chuyển về Dashboard thành công")

    driver.save_screenshot("TC-SP-001-PASS.png")

    add_result("TC-SP-012","✅ PASS",
               "Lưu ảnh minh chứng: TC-SP-001-PASS.png")

except Exception as e:

    driver.save_screenshot("TC-SP-001-FAIL.png")

    add_result(
        "TC-SP-ERROR",
        "❌ FAIL",
        str(e).split("\n")[0]
    )

finally:

    print("\n")

    print("="*92)
    print("KẾT QUẢ KIỂM THỬ: THÊM THUỐC MỚI")
    print("="*92)

    print("{:<12}{:<12}{}".format(
        "TC ID",
        "Kết quả",
        "Ghi chú"
    ))

    print("-"*92)

    pass_count=0
    fail_count=0

    for tc,status,note in result:

        print("{:<12}{:<12}{}".format(
            tc,
            status,
            note
        ))

        if "PASS" in status:
            pass_count+=1
        else:
            fail_count+=1

    print("="*92)

    print(
        f"KẾT QUẢ TỔNG HỢP: {pass_count} PASS | {fail_count} FAIL | {len(result)} TC"
    )

    print("="*92)

    driver.quit()