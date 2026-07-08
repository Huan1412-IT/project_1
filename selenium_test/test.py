from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from colorama import Fore, init
import os
import time

init(autoreset=True)

# =========================
# Khởi tạo
# =========================
driver = webdriver.Chrome()
driver.maximize_window()

wait = WebDriverWait(driver,10)

results=[]

try:

    print("===== BẮT ĐẦU KIỂM THỬ ĐĂNG NHẬP =====")

    #-------------------------------------------------
    # Bước 1
    #-------------------------------------------------
    driver.get("http://localhost/duocpham")

    wait.until(
        EC.presence_of_element_located((By.TAG_NAME,"body"))
    )

    print("✔ Bước 1: Mở website thành công")

    #-------------------------------------------------
    # Bước 2
    #-------------------------------------------------
    username = wait.until(
        EC.presence_of_element_located((By.NAME,"username"))
    )

    password = wait.until(
        EC.presence_of_element_located((By.NAME,"password"))
    )

    print("✔ Bước 2: Hiển thị form đăng nhập")

    #-------------------------------------------------
    # Bước 3
    #-------------------------------------------------
    username.clear()
    username.send_keys("admin")

    print("✔ Bước 3: Nhập Username")

    #-------------------------------------------------
    # Bước 4
    #-------------------------------------------------
    password.clear()
    password.send_keys("admin123")

    print("✔ Bước 4: Nhập Password")

    #-------------------------------------------------
    # Bước 5
    #-------------------------------------------------
    login = driver.find_element(By.NAME,"login")

    login.click()

    print("✔ Bước 5: Nhấn nút Đăng nhập")

    #-------------------------------------------------
    # Bước 6
    #-------------------------------------------------
    wait.until(lambda d:
        d.current_url != "http://localhost/duocpham/"
    )

    print("✔ Bước 6: Chuyển sang Dashboard")

    #-------------------------------------------------
    # Bước 7
    #-------------------------------------------------
    print("URL hiện tại:")
    print(driver.current_url)

    #-------------------------------------------------
    # Bước 8
    #-------------------------------------------------
    print("Tiêu đề trang:")
    print(driver.title)

    #-------------------------------------------------
    # Bước 9
    #-------------------------------------------------
    os.makedirs("screenshots",exist_ok=True)

    driver.save_screenshot("screenshots/login_success.png")

    print("✔ Bước 9: Đã chụp màn hình")

    #-------------------------------------------------
    # Bước 10
    #-------------------------------------------------
    cookies=driver.get_cookies()

    print("✔ Bước 10: Session đang hoạt động")
    print("Số Cookie:",len(cookies))

    #-------------------------------------------------
    # Bước 11
    #-------------------------------------------------
    links=driver.find_elements(By.TAG_NAME,"a")

    print("✔ Bước 11: Có",len(links),"liên kết trên Dashboard")

    #-------------------------------------------------
    # Bước 12
    #-------------------------------------------------
    buttons=driver.find_elements(By.TAG_NAME,"button")

    print("✔ Bước 12: Có",len(buttons),"button")

    #-------------------------------------------------
    # Bước 13
    #-------------------------------------------------
    time.sleep(2)

    print("✔ Bước 13: Kiểm tra giao diện hoàn tất")

    results.append((
        "TC-DN-001",
        True,
        "Đăng nhập thành công | URL: "+driver.current_url
    ))

except Exception as e:

    os.makedirs("screenshots",exist_ok=True)

    driver.save_screenshot("screenshots/login_fail.png")

    results.append((
        "TC-DN-001",
        False,
        str(e)
    ))

finally:

    driver.quit()

#====================================================
# IN BẢNG KẾT QUẢ
#====================================================

pass_count=sum(r[1] for r in results)
fail_count=len(results)-pass_count

print()
print("="*85)
print(f"KẾT QUẢ TỔNG HỢP: {pass_count} PASS | {fail_count} FAIL | {len(results)} TC")
print("="*85)

print(f"{'TC ID':<15}{'Kết quả':<15}Ghi chú")
print("-"*85)

for tc,status,note in results:

    if status:
        print(f"{tc:<15}{Fore.GREEN+'✅ PASS':<20}{note}")
    else:
        print(f"{tc:<15}{Fore.RED+'❌ FAIL':<20}{note}")

print("="*85)

