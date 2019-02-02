Testing procedure:

### One-time setup

1. Configure your testing database in `Config.php::TEST_DATABASE`.
2. Install the Selenium IDE addon [for Firefox](https://addons.mozilla.org/en-US/firefox/addon/selenium-ide/) or [for Google](https://chrome.google.com/webstore/detail/selenium-ide/mooikfkahbdckldjjndioackbalphokd).
3. Open the Selenium IDE.
4. Set the Base URL to the root URL of your dexonline installation (e.g. `http://localhost/dexonline/wwwbase/`). That default value is hard-coded in the test suite but it may be different for your installation.

### Running the test suite

1. Set `TEST_MODE = true` and `DEVELOPMENT_MODE = true` in `Config.php`.
2. Run `php tools/resetTestingDatabase.php` to, well, reset the testing database.
3. Open the Selenium IDE.
4. Open the test suite `test/dexonline-test-suite.side`.
5. It is important to run tests in suite order, not in alphabetical order. To ensure this, select _Test suites_ from the top-left dropdown (or press Ctrl+2). Then expand the _default suite_ and click on the first test (_homePage_).
6. Make sure the Base URL is still correct.
7. Click the button _Run all tests in suite_.

If you wish to run just parts of the suite, please be aware that some tests require anonymous access, while others require privileged access. Run the login/logout tests to switch to the correct state for each test, or reset the testing database (and log out from the test window).

1. Some initial tests run as anonymous; some of them verify that there is no privileged access.
2. The test _loginFakeUser_ logs in as John.
3. The remaining tests run as John and depend on privileged access.
4. The final test, _logout_, logs out.

### Adding a new test

1. Create a new test case in Selenium IDE
2. Hit the record button
3. Navigate to the page you wish to test, fill in values, assert things.
4. Stop the recording.
5. Edit the test until you're happy with it.
6. Replace the URL in the first command _(open)_ with a relative URL, e.g. "." for the home page.
7. Save the file over the old one, `test/dexonline-test-suite.side`.

Then commit and push the changes as usual.
