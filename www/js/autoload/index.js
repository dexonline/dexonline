rndw=function() {
  const STORAGE_LIST = 'randomWord';
  const STORAGE_POS = 'randomWordPos';
  const DELAY_TIME = 1600;

  var obj = $('#randomWordLink');
  var objImg = $('#randomWordImg');

  function init() {
    if (!obj.length) {
      return; // the user can hide the widget from preferences
    }

    var list = localStorage.getItem(STORAGE_LIST);
    if (list) {
      // serve the next word from local storage
      list = list.split('\n');
      var pos = localStorage.getItem(STORAGE_POS);
      displayWord(list[pos], DELAY_TIME);

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
          displayWord(list[0], DELAY_TIME);
          localStorage.setItem(STORAGE_LIST, list.join('\n'));
          localStorage.setItem(STORAGE_POS, 1);
        }
      });
    }
  }

  function displayWord(word, delayTime) {
    oldImg = objImg.attr('src');
    oldhref = obj.attr('href');
    newhref = oldhref.substring(0, oldhref.lastIndexOf('/')) + '/' + word;
    var timestamp = new Date().getTime();
    objImg.attr('src', oldImg.split('?')[0] + '?t=' + timestamp);
    obj.find('.widget-value').text('...');
    setTimeout(function () {
      obj.attr('href', newhref);
      obj.find('.widget-value').text(word);
    }, delayTime);
  }

  init();
}

$(rndw);
