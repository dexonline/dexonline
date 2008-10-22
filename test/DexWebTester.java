import net.sourceforge.jwebunit.WebTester;

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;

import com.meterware.httpunit.Button;

public class DexWebTester extends WebTester {
  public DexWebTester() throws IOException {
    Properties properties = new Properties();
    properties.load(new FileInputStream("../dex.conf"));
    getTestContext().setBaseUrl(properties.getProperty("baseUrl"));
  }

  public void selectSkin(Skin skin) {
    beginAt("/index.php?skin=" + skin.getDisplayName());
  }

  public void assertOlimpSkin() {
    assertElementPresent("olimpSkinBody");
  }

  public void assertPolarSkin() {
    assertElementPresent("polarSkinBody");
  }

  public void assertSimpleSkin() {
    assertElementPresent("simpleSkinBody");
  }

  public void assertSlickSkin() {
    assertElementPresent("slickSkinBody");
  }

  public void assertSkin(Skin skin) {
    assertElementPresent(skin.getDisplayName() + "SkinBody");
  }

  public void assertIsLoggedIn() {
    assertTextInElement("user.nick", "reggie");
  }

  public void assertIsNotLoggedIn() {
    assertTextInElement("user.nick", "Anonim");
  }

  public void login() {
    clickLink("loginLink");
    setWorkingForm("loginForm");
    setFormElement("email", "reggie");
    setFormElement("password", "hello");
    clickButton("send");
  }

  public void logout() {
    clickLink("logoutLink");
  }

  public void hitGuidePage() {
    clickLink("guideLink");
  }

  public void assertOnGuidePage() {
    assertTitleEquals("DEX online - Greseli frecvente in limba romana");
  }

  public void assertTextInTableAtPosition(String tableId, int row,
      int column, String text) {
    String[][] tableContents = getDialog().getSparseTableBySummaryOrId(tableId);
    assert(tableContents[row][column].contains(text));
  }

  public void assertTableRowEquals(String tableId, int row,
      String[] expectedContents) {
    String[][] tableContents = getDialog().getSparseTableBySummaryOrId(tableId);
    String[] tableRow = tableContents[row];
    if (tableRow.length != expectedContents.length) {
      System.err.println("Expected " + expectedContents.length +
          "elements, but got " + tableRow.length);
      assert false;
    }
    assert(tableRow.length == expectedContents.length);
    for (int i = 0; i < expectedContents.length; i++) {
      if (!expectedContents[i].equals(tableRow[i])) {
        System.err.println("At position " + i + " expected '" + expectedContents[i] +
        "', but got '" + tableRow[i] + "'");
        assert false;
      }
    }
  }

  public Button getButtonWithText(String buttonValueText) {
    Button[] buttons = getDialog().getForm().getButtons();
    for (Button button : buttons) {
      if (button.getValue().equals(buttonValueText)) {
        return button;
      }
    }
    return null;
  }

  public boolean hasButtonWithText(String buttonValueText) {
    return getButtonWithText(buttonValueText) != null;
  }

  public void clickButtonWithText(String buttonValueText) throws Exception {
    getButtonWithText(buttonValueText).click();
  }

  public void assertTableRowCountEquals(String tableId, int expectedRowCount) {
    String[][] contents = getDialog().getSparseTableBySummaryOrId(tableId);
    assert expectedRowCount == contents.length;
  }
}
