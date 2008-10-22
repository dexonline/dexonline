import junit.framework.TestCase;
import junit.framework.TestSuite;

import java.io.IOException;
import java.io.File;

public class DexHtmlTest extends TestCase {
  private DexWebTester tester;

  public DexHtmlTest(String name) throws IOException {
    super(name);
    tester = new DexWebTester();
}

  public static void main(String[] args) {
    junit.textui.TestRunner.run(new TestSuite(DexHtmlTest.class));
  }

  protected void setUp() throws Exception {
    Runtime.getRuntime().exec("php resetDatabase.php", null,
        new File("../tools"));
  }

  public void testSkinPersistence() {
    tester.selectSkin(Skin.SIMPLE);
    tester.assertSimpleSkin();
    tester.clickLinkWithText("Informaţii");
    tester.assertSimpleSkin();
  }

  public void testLoginAndLogout() {
    for (Skin skin : Skin.values()) {
      tester.selectSkin(skin);
      tester.assertSkin(skin);
      tester.assertIsNotLoggedIn();
      tester.login();
      tester.assertIsLoggedIn();
      tester.logout();
      tester.assertIsNotLoggedIn();
    }
  }

  public void testGuideEntriesViewAnonymous() {
    tester.beginAt("/");
    tester.hitGuidePage();
    tester.assertOnGuidePage();
    tester.assertTableRowEquals("guideEntryTable", 0,
        new String[] { "Corect", "Greşit", "Comentarii" });
    tester.assertTableRowEquals("guideEntryTable", 1,
        new String[] { "Aşa e bine!", "Aşa e rău!", "Şi o explicaţie, cu legătură." });
  }

  public void testGuideEntriesViewModerator() {
    tester.beginAt("/");
    tester.login();
    tester.hitGuidePage();
    tester.assertTableRowEquals("guideEntryTable", 0,
        new String[] { "Corect", "Greşit", "Comentarii", "Acţiuni" });
    tester.assertTableRowEquals("guideEntryTable", 1,
        new String[] { "Aşa e bine!", "Aşa e rău!", "Şi o explicaţie, cu legătură.",
            "editează  şterge "});
  }

  public void testGuideEntryEdit() throws Exception {
    tester.beginAt("/");
    tester.login();
    tester.hitGuidePage();
    tester.clickLink("edit1");
    tester.setWorkingForm("GuideEntryEdit");
    tester.setFormElement("Correct", "Modified correct");
    tester.setFormElement("Wrong", "Modified wrong");
    tester.setFormElement("Comments", "Modified comments");
    tester.clickButtonWithText("Salvează");
    tester.assertOnGuidePage();
    tester.assertTableRowEquals("guideEntryTable", 1,
        new String[] { "Modified correct", "Modified wrong", "Modified comments",
            "editează  şterge "});

    // set them back to the original values
    tester.clickLink("edit1");
    tester.setWorkingForm("GuideEntryEdit");
    tester.setFormElement("Correct", "@Aşa@ e bine!");
    tester.setFormElement("Wrong", "@Aşa@ e rău!");
    tester.setFormElement("Comments", "Şi o explicaţie, cu |legătură||.");
    tester.clickButtonWithText("Salvează");
    tester.assertOnGuidePage();
    tester.assertTableRowEquals("guideEntryTable", 1,
        new String[] { "Aşa e bine!", "Aşa e rău!", "Şi o explicaţie, cu legătură.",
            "editează  şterge "});
  }

  public void testGuideEntryAddAndDelete() throws Exception {
    tester.beginAt("/");
    tester.login();
    tester.hitGuidePage();
    tester.clickLinkWithText("Adaugă o înregistrare nouă");
    tester.setWorkingForm("GuideEntryEdit");
    tester.setFormElement("Correct", "New correct");
    tester.setFormElement("Wrong", "New wrong");
    tester.setFormElement("Comments", "New comments");
    tester.clickButtonWithText("Salvează");
    tester.assertOnGuidePage();
    tester.assertTableRowEquals("guideEntryTable", 2,
        new String[] { "New correct", "New wrong", "New comments",
            "editează  şterge "});
    tester.clickLink("delete2");
    tester.assertOnGuidePage();
    tester.assertTableRowCountEquals("guideEntryTable", 2);
  }
}
