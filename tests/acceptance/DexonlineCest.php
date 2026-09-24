<?php

namespace Tests\System\acceptance;

use Tests\System\_helpers\WebHelper;

class DexonlineCest
{
  /**
   * Test: homePage
   * Ensures that the homepage loads correctly and key elements are present.
   */
  public function homePage(WebHelper $I)
  {
    $I->wantTo('ensure the homepage loads correctly with all key elements');
    $I->amOnPage('/');
    $I->seeElement('link=despre noi');
    $I->seeElement('link=implică-te');
    $I->seeElement('link=resurse');
    $I->seeElement(['name' => 'cuv']);
    $I->seeElement('#searchButton');
    $I->see('opțiuni', '#advancedAnchor');
  }

  /**
   * Test: searchByInflectedForm
   * Searches by an inflected word form and verifies the results.
   */
  public function searchByInflectedForm(WebHelper $I)
  {
    $I->wantTo('search by inflected form and verify the results');
    $I->amOnPage('/');
    $I->fillField('cuv', 'brânzeturi');
    $I->click('#searchButton');
    $I->seeInField('cuv', 'brânză');
    $I->see('O definiție pentru brânză', 'h3');
    $I->see('Produs alimentar[1] obținut prin coagularea și prelucrarea laptelui.', 'span.def');
    $I->see('Source 1 (2010)', 'a.ref');
    $I->see('john', '.dropup > li:nth-child(2) > a');
    $I->click('acțiuni');
    $I->seeElement('link=semnalează o greșeală');
    $I->seeElement('link=permalink');
  }

  /**
   * Test: searchApproximate
   * Performs an approximate (typo) search and verifies the redirection.
   */
  public function searchApproximate(WebHelper $I)
  {
    $I->wantTo('perform an approximate search and verify the redirection and results');
    $I->amOnPage('/');
    $I->fillField('cuv', 'brbnză');
    $I->click('#searchButton');
    $I->see('V-am redirecționat automat de la brbnză la brânză.', '.alert');
    $I->seeInField('cuv', 'brânză');
    $I->see('Produs alimentar[1] obținut prin coagularea și prelucrarea laptelui.', 'span.def');
  }

  /**
   * Test: searchRegexp
   * Performs a search with a regular expression and verifies the results.
   */
  public function searchRegexp(WebHelper $I)
  {
    $I->wantTo('perform a search with a regular expression and verify the results');
    $I->amOnPage('/');
    $I->fillField('cuv', '*ă*');
    $I->click('#searchButton');
    $I->seeInField('cuv', '*ă*');
    $I->see('4 rezultate pentru *ă*', 'h3');
    $I->see('brânză', 'link=brânză');
    $I->see('cadă', 'link=cadă');
    $I->see('ladă', 'link=ladă');
    $I->see('ogradă', 'link=ogradă');
  }

  /**
   * Test: searchFullText
   * Performs a full-text search using advanced options.
   */
  public function searchFullText(WebHelper $I)
  {
    $I->wantTo('perform a full-text search and verify the results');
    $I->amOnPage('/');
    $I->fillField('cuv', 'din metal');
    $I->click('#advancedAnchor');
    $I->checkOption('text');
    $I->click('#searchButton');
    $I->seeInField('cuv', 'din metal');
    $I->seeCheckboxIsChecked(['name' => 'text']);
    $I->see('O definiție conține toate cuvintele căutate', 'h3');
    $I->see('din', '.fth:nth-child(4)');
    $I->see('metal', 'span.fth.fth1');
  }

  /**
   * Test: searchByDefinitionId
   * Searches by a specific definition ID.
   */
  public function searchByDefinitionId(WebHelper $I)
  {
    $I->wantTo('search by definition ID and verify the result');
    $I->amOnPage('/');
    $I->fillField('cuv', '1');
    $I->click('#searchButton');
    $I->seeInField('cuv', 'brânză');
    $I->see('Produs alimentar[1] obținut prin coagularea și prelucrarea laptelui.', 'span.def');
  }

  /**
   * Test: searchByLexemeId
   * Searches by a lexeme ID and verifies the results.
   */
  public function searchByLexemeId(WebHelper $I)
  {
    $I->wantTo('search by lexeme ID and verify the results');
    $I->amOnPage('/');
    $I->fillField('cuv', 'din');
    $I->click('#searchButton');
    $I->see('2 intrări', 'h3');
    $I->seeElement(['link' => 'din']);
    $I->click(['link' => 'din']);
    $I->seeInField('cuv', 'din');
    $I->see('O definiție pentru din', 'h3');
  }

  /**
   * Test: adsLink
   * Verifies that an external ad link works correctly.
   */
  public function adsLink(WebHelper $I)
  {
    $I->wantTo('verify that the ads link to Wikipedia works correctly');
    $I->amOnPage('/spre/wikipedia');
    $I->see('Read Wikipedia in your language', 'span.lang-list-button-text.jsl10n');
  }

  /**
   * Test: articles
   * Navigates through linguistic articles pages.
   */
  public function articles(WebHelper $I)
  {
    $I->wantTo('navigate through linguistic articles');
    $I->amOnPage('/articole');
    $I->see('Articole lingvistice', 'h1');
    $I->see('Ghid de exprimare', 'link=Ghid de exprimare');
    $I->click('Ghid de exprimare');
    $I->see('Ghid de exprimare', 'h3');
    $I->see('Conținutul articolului 2.', 'div.col-md-12.main-content > div');
  }

  /**
   * Test: articlesSearchResults
   * Verifies that relevant articles appear on search result pages.
   */
  public function articlesSearchResults(WebHelper $I)
  {
    $I->wantTo('verify that relevant articles are displayed on search results pages');
    $I->amOnPage('/definitie/metal');
    $I->see('Articole pe această temă:', 'h3');
    $I->fillField('cuv', 'brânză');
    $I->click('#searchButton');
    $I->dontSee('Articole pe această temă:');
  }

  /**
   * Test: commentView
   * Views comments on a definition page.
   */
  public function commentView(WebHelper $I)
  {
    $I->wantTo('view comments on a definition page');
    $I->amOnPage('/definitie/brânză');
    $I->see('Foarte foarte gustos — john', 'div.panel-body');
    $I->see('john', 'div.panel-body a');
  }

  /**
   * Test: constraints
   * Verifies constraints and paradigm tabs for definitions.
   */
  public function constraints(WebHelper $I)
  {
    $I->wantTo('verify constraints and paradigm tabs');
    $I->amOnPage('/definitie/ladă/paradigma');
    $I->see('declinări', 'ul.nav-tabs > li.active');
    $I->click('rezultate (1)');
    $I->see('Nicio definiție pentru ladă', 'h3');
    $I->click('declinări');
    $I->see('ladă', 'span.lexemeName');
    $I->see('ladă', '//tr[2]/td[3]');
  }

  /**
   * Test: autocomplete
   * Tests the autocomplete functionality in the search bar.
   */
  public function autocomplete(WebHelper $I)
  {
    $I->wantTo('test the autocomplete functionality in the search bar');
    $I->amOnPage('/');
    $I->fillField('cuv', 'bra');
    $I->waitForElementVisible('#ui-id-2', 5);
    $I->see('brânză', '#ui-id-2');
    $I->click('#ui-id-2');
    $I->seeInField('cuv', 'brânză');
    $I->see('O definiție pentru brânză', 'h3');
  }

  /**
   * Test: lexemeSourcesView
   * Views lexeme sources on the paradigm page.
   */
  public function lexemeSourcesView(WebHelper $I)
  {
    $I->wantTo('view lexeme sources on the paradigm page');
    $I->amOnPage('/definitie/metal/paradigma');
    $I->see('metal', 'span.lexemeName');
    $I->see('Surse flexiune: Source 2', 'div.lexemeSources');
  }

  /**
   * Test: sourcesViewAsAnonymous
   * Views sources as an anonymous user.
   */
  public function sourcesViewAsAnonymous(WebHelper $I)
  {
    $I->wantTo('view sources as an anonymous user');
    $I->amOnPage('/surse');
    $I->see('Source 1', 'span.sourceShortName');
    $I->see('English - Klingon Dictionary', 'span.sourceName');
    $I->moveMouseOver('tr:nth-child(2) .sourceName');
    $I->waitForElementVisible('tr:nth-child(2) .sourceName .popover-content', 5);
    $I->see("Autor: Ambrose Bierce", 'tr:nth-child(2) .sourceName .popover-content');
  }

  /**
   * Test: hangman
   * Plays the hangman game.
   */
  public function hangman(WebHelper $I)
  {
    $I->wantTo('play the hangman game');
    $I->amOnPage('/spanzuratoarea');
    $I->seeInField("(//input[@name='out[]'])[1]", 'B');
    $I->seeInField("(//input[@name='out[]'])[6]", 'Ă');
    $I->seeElement('input.letterButtons.buttonGuessed[value="B"]');
    $I->click("//input[@value='G']");
    $I->seeElement('input.letterButtons.buttonMissed[value="G"]');
    $I->click("//input[@value='R']");
    $I->seeInField("(//input[@name='out[]'])[2]", 'R');
  }

  /**
   * Test: localeChange
   * Changes the interface language.
   */
  public function localeChange(WebHelper $I)
  {
    $I->wantTo('change the interface language');
    $I->amOnPage('/');
    $I->click('i.glyphicon.glyphicon-globe');
    $I->click('English');
    $I->see('Interface language changed.', '.alert');
    $I->see('Dictionaries of the Romanian language', 'div.tagline');
    $I->click('i.glyphicon.glyphicon-globe');
    $I->click('română');
    $I->see('Am schimbat limba interfeței.', '.alert');
    $I->see('Dicționare ale limbii române', 'div.tagline');
  }

  /**
   * Test: loginFakeUser
   * Logs in as a fake user with privileges.
   */
  public function loginFakeUser(WebHelper $I)
  {
    $I->wantTo('log in as a fake user');
    $I->amOnPage('/');
    $I->see('Anonim', 'link=Anonim');
    $I->click('Anonim');
    $I->click('.dropdown-menu > li:nth-child(2) span');
    $I->fillField('fakeUserNick', 'john');
    $I->checkOption('#allPriv');
    $I->click('input.btn.btn-warning');
    $I->seeElement('link=john');
    $I->click('john');
    $I->seeElement('link=pagina moderatorului');
  }

  /**
   * Test: bookmarks
   * Manages user bookmarks after logging in.
   */
  public function bookmarks(WebHelper $I)
  {
    $I->wantTo('manage bookmarks');
    $this->loginFakeUser($I); // Ensure user is logged in
    $I->amOnPage('/definitie/brânză');
    $I->click('acțiuni');
    $I->click('a.bookmarkAddButton');
    $I->wait(0.5);
    $I->see('adăugată la favorite', 'a.bookmarkAddButton > span');
    $I->amOnPage('/cuvinte-favorite');
    $I->see('Produs alimentar[1] obținut prin coagularea și prelucrarea laptelui.', 'span.def');
    $I->click('acțiuni');
    $I->click('a.bookmarkRemoveButton > span');
    $I->wait(0.5);
    $I->amOnPage('/cuvinte-favorite');
    $I->see('Nu aveți niciun cuvânt favorit.', 'dl.favoriteDefs');
  }

  /**
   * Test: definitionRepWhitespace
   * Checks how whitespace is handled in the definition editor.
   */
  public function definitionRepWhitespace(WebHelper $I)
  {
    $I->wantTo('check whitespace representation in definition editing');
    $this->loginFakeUser($I); // Editing requires login
    $I->amOnPage('/');
    $I->fillField('searchField', 'spatiere');
    $I->click('searchButton');
    $I->click('editează');
    $I->seeInField('#internalRep', "Definiție\n pe mai multe\nlinii pentru a testa spațierea.");
  }

  /**
   * Test: redundantLinkWarning
   * Ensures warnings for redundant links are displayed.
   */
  public function redundantLinkWarning(WebHelper $I)
  {
    $I->wantTo('test for redundant link warnings');
    $this->loginFakeUser($I); // Editing requires login
    $I->amOnPage('/');
    $I->fillField('searchField', 'spatiere');
    $I->click('searchButton');
    $I->click('editează');
    $I->fillField('#internalRep', "Definiție\n pe mai multe\nlinii pentru a testa spațierea.\n\n|vibrarea|vibra|");
    $I->click(['name' => 'saveButton']);
    $I->see('Legătura de la "vibrarea" la "vibra" este considerată redundantă', '.alert');
  }

  /**
   * Test: logout
   * Logs the user out of the application.
   */
  public function logout(WebHelper $I)
  {
    $I->wantTo('log out from the application');
    $this->loginFakeUser($I); // Ensure user is logged in to log out
    $I->amOnPage('/');
    $I->see('john', '.dropdown-toggle');
    $I->click('john');
    $I->click('închide sesiunea');
    $I->seeElement('link=Anonim');
    $I->dontSeeElement('link=john');
  }
}
