from playwright.sync_api import sync_playwright


def run(playwright):
    url = "https://www.autoscout24.ch/de/d/bmw-m5-limousine-1999-occasion?backurl=%2F&topcar=true&vehid=9324196"

    webkit = playwright.webkit
    iphone = playwright.devices["iPhone 11 Pro"]
    browser = webkit.launch(headless=False, slow_mo=100)
    context = browser.new_context(
        # **iphone,
        locale='fr-FR'
    )
    page = context.new_page()
    page.goto(url)

    page.locator("text=Accepter et fermer").click()
    content = page.content()

    # page.pause()
    # other actions...
    browser.close()

    fileOb = open('ch_auto.html', 'w', encoding='utf-8')  # 打开一个文件，没有就新建一个
    fileOb.write(content)
    fileOb.close()


with sync_playwright() as playwright:
    run(playwright)
