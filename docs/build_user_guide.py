import os
from pathlib import Path
from playwright.sync_api import sync_playwright

BASE_URL = "https://puprprabumulih.id"
OUT = Path(__file__).parent / "panduan-assets"
OUT.mkdir(parents=True, exist_ok=True)

email = os.environ["PUPR_GUIDE_EMAIL"]
password = os.environ["PUPR_GUIDE_PASSWORD"]

with sync_playwright() as p:
    browser = p.chromium.launch(
        headless=True,
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        args=["--disable-gpu"],
    )
    context = browser.new_context(
        viewport={"width": 1440, "height": 900},
        device_scale_factor=1,
        locale="id-ID",
        timezone_id="Asia/Jakarta",
    )
    page = context.new_page()

    page.goto(BASE_URL, wait_until="domcontentloaded", timeout=60000)
    page.wait_for_timeout(3000)
    page.screenshot(path=str(OUT / "01-beranda.png"), full_page=False)
    page.screenshot(path=str(OUT / "02-beranda-penuh.png"), full_page=True)

    page.goto(f"{BASE_URL}/admin/login", wait_until="domcontentloaded", timeout=60000)
    page.wait_for_timeout(2000)
    page.screenshot(path=str(OUT / "03-login-admin.png"), full_page=False)
    page.locator('input[name="email"]').fill(email)
    page.locator('input[name="password"]').fill(password)
    page.locator('.login-card form button').click()
    page.wait_for_url("**/admin", timeout=60000)
    page.wait_for_load_state("domcontentloaded")
    page.wait_for_timeout(2000)
    page.screenshot(path=str(OUT / "04-dashboard-admin.png"), full_page=False)

    page.goto(f"{BASE_URL}/admin/news", wait_until="domcontentloaded", timeout=60000)
    page.wait_for_timeout(2000)
    page.screenshot(path=str(OUT / "05-daftar-berita.png"), full_page=False)

    page.goto(f"{BASE_URL}/admin/news/create", wait_until="domcontentloaded", timeout=60000)
    page.wait_for_timeout(2000)
    page.screenshot(path=str(OUT / "06-tambah-berita.png"), full_page=True)

    page.goto(BASE_URL, wait_until="domcontentloaded", timeout=60000)
    page.wait_for_timeout(2000)
    page.set_viewport_size({"width": 390, "height": 844})
    page.screenshot(path=str(OUT / "07-beranda-mobile.png"), full_page=False)
    browser.close()

print(f"Screenshots saved to {OUT}")
