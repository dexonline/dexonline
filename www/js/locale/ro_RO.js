const I18N_MESSAGES = {

  'expand': 'extinde',
  'hangman-word-list-download-error': 'Nu pot descărca lista de cuvinte. Te rog reîncearcă în cîteva minute.',

  'hide tags': 'ascunde etichetele',
  'scroll-top': 'înapoi sus',
  'show tags': 'arată etichetele',

};

function _plural(n) {
  return (n == 1)
    ? 0 // un copil
    : ((n == 0 || (n % 100 > 0 && n % 100 < 20))
       ? 1   // 0 copii / 19 copii
       : 2); // 34 de copii
}
