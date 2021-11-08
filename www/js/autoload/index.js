$(function() {
  const STORAGE_LIST = 'randomWord';
  const STORAGE_POS = 'randomWordPos';

  var obj = $('#randomWordLink');

  function init() {
    if (!obj.length) {
      return; // the user can hide the widget from preferences
    }

    var list = localStorage.getItem(STORAGE_LIST);
    if (list) {
      // serve the next word from local storage
      list = list.split('\n');
      var pos = localStorage.getItem(STORAGE_POS);
      displayWord(list[pos]);

      // advance the pointer; delete the list if it's done
      pos++;
      if (pos == list.length) {
        localStorage.removeItem(STORAGE_LIST);
        localStorage.removeItem(STORAGE_POS);
      } else {
        localStorage.setItem(STORAGE_POS, pos);
      }

    } else {
      $.ajax({
        url: 'ajax/randomWord.php',
        success: function(resp) {
          var list = resp.split('\n');
          // shuffle the list so that everyone doesn't see the same words
          shuffle(list);

          // show the first word and store the others
          displayWord(list[0]);
          localStorage.setItem(STORAGE_LIST, list.join('\n'));
          localStorage.setItem(STORAGE_POS, 1);
        }
      });
    }
  }

  function displayWord(word) {
    obj.attr('href', obj.attr('href') + word);
    obj.find('.widget-value').text(word);
  }

  /**
   * Shuffles an array in place. Welcome to 1975, where this function is not built in.
   * @param Array a
   */
  function shuffle(a) {
    for (var i = a.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      [ a[i], a[j] ] = [ a[j], a[i] ]; // swap
    }
  }

  init();
});
