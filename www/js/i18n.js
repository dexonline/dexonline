/**
 * Returns a translated string for the given key.
 * @param string key Key to look up in the dictionary.
 * @param string[] args Any additional arguments will be supplied for %1, %2
 *   etc. in the translated string.
 * @return string
 */
function _(key, ...args) {
  if (typeof I18N_MESSAGES == 'undefined') {
    return key; // translation dictionary not loaded
  }

  if (!(key in I18N_MESSAGES)) {
    return key; // undefined translation key
  }

  var s = I18N_MESSAGES[key];

  if (Array.isArray(s)) {
    // pick the correct singular/plural form
    var n = args[0] ?? 1; // pick singular by default
    s = I18N_MESSAGES[key][_plural(n)];
  }

  for (var i = 0; i < args.length; i++) {
    s = s.replace('%' + (i + 1), args[i]);
  }

  return s;
}
