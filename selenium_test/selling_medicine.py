from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time


# ==========================================
# KHỞI TẠO
# ==========================================

driver = webdriver.Chrome()
driver.maximize_window()

wait = WebDriverWait(driver,20)

PASS = True
note = ""


try:

    print("="*90)
    print("TC-BH-001 : KIỂM THỬ CHỨC NĂNG BÁN THUỐC")
    print("="*90)


    # ==========================================
    # 1. LOGIN
    # ==========================================

    driver.get(
        "http://localhost/duocpham/login.php"
    )


    wait.until(
        EC.visibility_of_element_located(
            (By.NAME,"username")
        )
    )


    driver.find_element(
        By.NAME,
        "username"
    ).send_keys("admin")


    driver.find_element(
        By.NAME,
        "password"
    ).send_keys("admin123")


    driver.find_element(
        By.NAME,
        "login"
    ).click()



    wait.until(
        EC.url_contains("index.php")
    )


    print("✓ Đăng nhập thành công")



    # ==========================================
    # 2. MỞ POS
    # ==========================================

    driver.get(
        "http://localhost/duocpham/Models/staff.php"
    )


    wait.until(
        EC.presence_of_element_located(
            (
                By.ID,
                "search-input"
            )
        )
    )


    print("✓ Mở giao diện bán hàng")



    # ==========================================
    # 3. TÌM PARACETAMOL
    # ==========================================


    search = driver.find_element(
        By.ID,
        "search-input"
    )


    search.clear()

    search.send_keys(
        "Paracetamol"
    )


    time.sleep(2)



    products = driver.find_elements(
        By.CLASS_NAME,
        "product-item"
    )


    selected = False



    for item in products:


        if "paracetamol" in item.text.lower():


            card = item.find_element(
                By.CSS_SELECTOR,
                ".product-card"
            )


            driver.execute_script(
                """
                arguments[0].scrollIntoView({
                    block:'center'
                });

                arguments[0].click();
                """,
                card
            )


            selected = True

            break



    if not selected:

        raise Exception(
            "Không tìm thấy Paracetamol"
        )


    print(
        "✓ Đã chọn sản phẩm Paracetamol"
    )


    time.sleep(2)



    # ==========================================
    # 4. KIỂM TRA GIỎ HÀNG
    # ==========================================


    cart = driver.find_element(
        By.ID,
        "cart-tbody"
    )


    if "paracetamol" not in cart.text.lower():

        raise Exception(
            "Sản phẩm chưa vào giỏ hàng"
        )


    print(
        "✓ Paracetamol đã vào giỏ"
    )



    # ==========================================
    # 5. ĐỔI SỐ LƯỢNG = 5
    # ==========================================


    quantity = wait.until(
        EC.presence_of_element_located(
            (
                By.CSS_SELECTOR,
                "#cart-tbody input[type='number']"
            )
        )
    )


    driver.execute_script(
        """
        arguments[0].value='5';

        arguments[0].dispatchEvent(
            new Event(
                'input',
                {bubbles:true}
            )
        );

        arguments[0].dispatchEvent(
            new Event(
                'change',
                {bubbles:true}
            )
        );
        """,
        quantity
    )


    time.sleep(2)


    print(
        "✓ Đã chọn số lượng = 5"
    )



    # ==========================================
    # 6. NHẬP TIỀN KHÁCH ĐƯA
    # ==========================================


    cash = wait.until(
        EC.presence_of_element_located(
            (
                By.ID,
                "cash-received"
            )
        )
    )


    driver.execute_script(
        """
        arguments[0].value='100000';

        arguments[0].dispatchEvent(
            new Event(
                'input',
                {bubbles:true}
            )
        );

        arguments[0].dispatchEvent(
            new Event(
                'change',
                {bubbles:true}
            )
        );
        """,
        cash
    )


    time.sleep(2)


    money = cash.get_attribute(
        "value"
    )


    if money != "100000":

        raise Exception(
            "Không nhập được tiền khách đưa"
        )


    print(
        "✓ Tiền khách đưa:",
        money
    )



    # ==========================================
    # 7. CLICK THANH TOÁN
    # ==========================================


    checkout = wait.until(
        EC.presence_of_element_located(
            (
                By.XPATH,
                "//button[contains(.,'XÁC NHẬN THANH TOÁN')]"
            )
        )
    )


    driver.execute_script(
        """
        arguments[0].scrollIntoView({
            block:'center'
        });

        arguments[0].click();
        """,
        checkout
    )


    print(
        "✓ Đã nhấn XÁC NHẬN THANH TOÁN"
    )



    # ==========================================
    # 8. KIỂM TRA ALERT
    # ==========================================


    alert = wait.until(
        EC.alert_is_present()
    )


    message = alert.text


    print(
        "Thông báo:",
        message
    )


    if "thành công" in message.lower():

        print(
            "✓ Thanh toán thành công"
        )

        alert.accept()


    else:

        raise Exception(
            message
        )



    # ==========================================
    # SCREENSHOT
    # ==========================================

    driver.save_screenshot(
        "TC-BH-001-PASS.png"
    )


    note = (
        "Đăng nhập -> "
        "Mở POS -> "
        "Chọn Paracetamol -> "
        "Thêm giỏ hàng -> "
        "Số lượng 5 -> "
        "Nhập tiền khách đưa -> "
        "Xác nhận thanh toán."
    )



except Exception as e:


    PASS = False

    note = (
        f"{type(e).__name__}: {e}"
    )


    print(
        "\n✗ KIỂM THỬ THẤT BẠI"
    )


    print(
        "URL:",
        driver.current_url
    )


    print(
        "Lỗi:",
        e
    )


    driver.save_screenshot(
        "TC-BH-001-FAIL.png"
    )



finally:


    print()

    print("="*95)
    print("KẾT QUẢ KIỂM THỬ")
    print("="*95)


    print(
        "{:<15}{:<12}{}".format(
            "TC ID",
            "Kết quả",
            "Ghi chú"
        )
    )


    print("-"*95)


    print(
        "{:<15}{:<12}{}".format(
            "TC-BH-001",
            "PASS" if PASS else "FAIL",
            note
        )
    )


    print("="*95)


    input(
        "\nNhấn Enter để đóng..."
    )


    driver.quit()