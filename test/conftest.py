# conftest.py
import pytest
import os

@pytest.hookimpl(tryfirst=True, hookwrapper=True)
def pytest_runtest_makereport(item, call):
    """
    Takes a screenshot on test failure.
    """
    # Execute all other hooks to obtain the report object
    outcome = yield
    report = outcome.get_result()

    # We only look at actual test calls, not setup/teardown
    if report.when == 'call' and report.failed:
        try:
            # 'item.instance' is the test class object
            driver = item.instance.driver
            # The test name is used for the screenshot file name
            test_name = item.name
            screenshot_path = os.path.join(os.getcwd(), f"screenshot_{test_name}.png")
            # Use Selenium's built-in screenshot driver
            driver.save_screenshot(screenshot_path)
            print(f"Screenshot saved to {screenshot_path}")
        except Exception as e:
            print(f"Failed to take screenshot: {e}")
