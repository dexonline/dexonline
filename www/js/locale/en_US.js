/**
 * An array of key: translation pairs. Should define all the keys for which
 * _() is called. Values can be strings or, for singular/plural distinctions,
 * arrays of strings. For arrays, the length should match the number of values
 * returned by _plural().
 */
const I18N_MESSAGES = {

  'expand': 'expand',
  'hangman-word-list-download-error':
  'Cannot download the word list. Please retry in a few minutes.',
  'scroll-top': 'back to top',

};

/**
 * Returns the type of singular/plural form to be used for this value. The
 * corresponding array element will be returned from I18N_MESSAGES.
 * See ro_RO.js for a more complex rule.
 */
function _plural(n) {
  return (n == 1) ? 0 : 1;
}
