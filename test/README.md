Testing procedure:

One-time setup
==============

1. Configure your testing database in the `[testing]` section of `varena.conf`.
2. Install the [Selenium IDE addon for Firefox](https://addons.mozilla.org/en-US/firefox/addon/selenium-ide/).
3. Type Ctrl+Alt+S to open the Selenium IDE.
4. Set the Base URL to the root URL of your dexonline installation (e.g. `http://localhost/var/www/dexonline/wwwbase/`). We cannot hardcode this in the test suite because it varies for each installation.

Running the test suite
======================

1. Set `[testing] enabled = true` and `[global] developmentMode = true` in `varena.conf`.
2. Run `php tools/resetTestingDatabase.php` to, well, reset the testing database.
3. Open Firefox.
4. Type Ctrl+Alt+S to open the Selenium IDE.
5. Select _File -> Open test suite..._ and open `test/dexonline-test-suite.xml`.
6. Makre sure the Base URL is still correct.
7. Click the nice green button.
