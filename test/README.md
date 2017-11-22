Testing procedure:

### One-time setup

1. Configure your testing database in the `[testing]` section of `dex.conf`.
2. Install the [Selenium IDE addon for Firefox](https://addons.mozilla.org/en-US/firefox/addon/selenium-ide/).
3. Type Ctrl+Alt+S to open the Selenium IDE. You will need to install Firefox ESR, since Selenium (and many other addons) no longer work in the latest Firefox.
4. Set the Base URL to the root URL of your dexonline installation (e.g. `http://localhost/dexonline/wwwbase/`). We cannot hardcode this in the test suite because it varies for each installation.

### Running the test suite

1. Set `[testing] enabled = true` and `[global] developmentMode = true` in `dex.conf`.
2. Run `php tools/resetTestingDatabase.php` to, well, reset the testing database.
3. Open Firefox.
4. Type Ctrl+Alt+S to open the Selenium IDE.
5. Select _File -> Open test suite..._ and open `test/dexonline-test-suite.xml`.
6. Make sure the Base URL is still correct.
7. Click the nice green button.

### Adding a new test

1. Create a new test case in Selenium IDE
2. Hit the record button
3. Navigate to the page you wish to test, fill in values, assert things.
4. Stop the recording.
5. Edit the test until you're happy with it.
6. Replace the URL in the first command _(open)_ with a relative URL, e.g. "." for the home page.
7. Save the new test somewhere under `/test` with a .xml extension.
8. Edit the XML file and remove the `<link rel="selenium.base"...` line.

After adding one or more tests, remember to save the test suite as well. Then commit and push the changes as usual.
