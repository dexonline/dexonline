var Alphabet = 'a-záàäåăâçèéëìíïĭîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

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

$(function() {
  $('.def,.tree-def').click(searchClickedWord);
  $('#typoModal').on('shown.bs.modal', shownTypoModal);

  $('#searchField').select().focus();
  $('#searchClear').click(function() {
    $('#searchField').val('').focus();
    $(this).hide();
  });
  $('#searchField').on('input', function() {
    if ($(this).val()) {
      // Bootstrap's d-none comes with !important, so it takes precedence over show().
      $('#searchClear').removeClass('d-none').show();
    } else {
      $('#searchClear').hide();
    }
  });

  // Prevent submitting forms twice. Forms can request permission to resubmit
  // by calling removeData('submitted').
  $('form').submit(function(e) {
    if ($(this).data('submitted')) {
      e.preventDefault();
    } else {
      $(this).data('submitted', true);
      return true;
    }
  });

  $('li.disabled a').click(function() {
    return false;
  });

  $('.doubleText').click(function() {
    var tmp = $(this).text();
    $(this).text($(this).attr('data-other-text'));
    $(this).attr('data-other-text', tmp);
  });

  // abbreviation hover
  $('.abbrev[data-bs-toggle="popover"]').each(function() {
    new bootstrap.Popover(this, {
      html: true,
      trigger: 'hover',
    });
  });

});

function formatSource(item) {
  return $('<span>' +
           item.text.replace(/(\(([^)]+)\))/, '<strong>$2</strong>') +
           '</span>');
}

/**
 * config must have the following structure
 *   * url: URL of asyncjs.php
 *   * id: value of data-revive-id in the invocation code
 *   * maxHeight (float, 0...1): how much of the screen height the banner is
       allowed to occupy
 *   * sizes: an array of [ width, height, zoneId ] listing the available
 *     banner sizes (this should match the Revive setup). The widths should be
 *     in decreasing order. We use the first line from the array having:
 *     - "width" < real screen width (JS width x JS device pixel ratio);
 *     - an acceptable height;
 *
 * Once the banner is rendered, we shrink it by the dpr.
 */
function reviveInit(config) {

  var dpr = window.devicePixelRatio,
      w = $(window).width() * dpr,
      h = $(window).height() * dpr;

  var i = 0;
  while ((i < config.sizes.length) &&
         ((config.sizes[i][0] > w) ||
          (config.sizes[i][1] > h * config.maxHeight))) {
    i++;
  }

  if (i == config.sizes.length) {
    return; // cannot accommodate any banner
  }

  var zoneId = config.sizes[i][2];

  // ask to be notified when the image is inserted
  $(document).on('DOMNodeInserted', '.banner-section ins', function() {
    var img = $('.banner-section img').first();
    if (img.length && dpr > 1) {
      img.attr('height', img.attr('height') / dpr);
      img.attr('width', img.attr('width') / dpr);
    }
  });

  $('#revive-container').attr('data-revive-zoneid', zoneId);
  $.getScript(config.url);

}

function getWidth() {
  if (self.innerWidth) {
    return self.innerWidth;
  }

  if (document.documentElement && document.documentElement.clientWidth) {
    return document.documentElement.clientWidth;
  }

  if (document.body) {
    return document.body.clientWidth;
  }
}

function loadAjaxContent(url, elid) {
  $.get(url, function(data) {
    $(elid).html(data);
  });
  return false;
}

function searchSubmit() {
  // Avoid server hit on empty query
  if (!document.frm.cuv.value) {
    return false;
  }

  // Friendly redirect
  action = document.frm.text.checked ? 'text' : 'definitie';
  source = document.frm.source.value;
  sourcePart = source ? '-' + source : '';
  window.location = wwwRoot + action + sourcePart + '/' + encodeURIComponent(document.frm.cuv.value);
  return false;
}

function getWwwRoot() {
  var pos = window.location.href.indexOf('/www/');
  if (pos == -1) {
    return '/';
  } else {
    return window.location.href.substr(0, pos + 5);
  }
}

function shownTypoModal(event) {
  var link = $(event.relatedTarget); // link that triggered the modal
  var defId = link.data('definitionId');
  $('input[name="definitionId"]').val(defId);
  $('#typoTextarea').focus();
  $('#typoSubmit').removeData('submitted'); // allow clicking the button again
}

function submitTypoForm() {
  var text = $('#typoTextarea').val();
  var defId = $('input[name="definitionId"]').val();
  $.post(wwwRoot + 'ajax/typo.php',
         { definitionId: defId, text: text, submit: 1 },
         function() {
           $('#typoModal').modal('hide');
           $('#typoTextarea').val('');
           var confModal = new bootstrap.Modal($('#typoConfModal'));
           confModal.show();
         });
  return false;
}

function toggle(id) {
  $('#' + id).stop().slideToggle();
  return false;
}

function addProvider(url) {
  try {
    window.external.AddSearchProvider(url);
  } catch (e) {
    alert('Aveți nevoie de Firefox 2.0 sau Internet Explorer 7 ' +
          'pentru a adăuga dexonline la lista motoarelor de căutare.');
  }
}

function startsWith(str, sub) {
  return str.substr(0, sub.length) == sub;
}

function endsWith(str, sub) {
  return str.substr(str.length - sub.length) == sub;
}

/* adapted from http://stackoverflow.com/questions/7563169/detect-which-word-has-been-clicked-on-within-a-text */
function searchClickedWord(event) {
  if ($(event.target).is('abbr')) return false;
  if ($(event.target).is('a')) {
    console.log(event.target.href);
    window.location = event.target.href;
    return;
  }

  // Gets clicked on word (or selected text if text is selected)
  var word = '';
  if (window.getSelection && (sel = window.getSelection()).modify) {
    // Webkit, Gecko
    var s = window.getSelection();
    if (s.isCollapsed) { // Do not redirect when the user is trying to select text
      s.modify('move', 'forward', 'character');
      s.modify('move', 'backward', 'word');
      s.modify('extend', 'forward', 'word');
      word = s.toString();
      s.modify('move', 'forward', 'character'); // clear selection
    }
  } else if ((sel = document.selection) && sel.type != 'Control') {
    // IE 4+
    var textRange = sel.createRange();
    if (!textRange.text) {
      textRange.expand('word');
      while (/\s$/.test(textRange.text)) {
        textRange.moveEnd('character', -1);
      }
      word = textRange.text;
    }
  }

  // Trim trailing dots
  var regex = new RegExp(nonLetter + '$', 'i');
  while (word && regex.test(word)) {
    word = word.substr(0, word.length - 1);
  }

  var source = $('#source-field-hidden').length
      ? $('#source-field-hidden').val()
      : '';
  if (source) {
    source = '-' + source;
  }

  if (word) {
    window.location = wwwRoot + 'definitie' + source + '/' + encodeURIComponent(word);
  }
}

function installFirefoxSpellChecker(evt) {
  var params = {
    'ortoDEX': { URL: evt.target.href,
                 toString : function() { return this.URL; }
    }
  }
  InstallTrigger.install(params);
  return false;
}

$(function() {
  $('.mention').hover(mentionHoverIn, mentionHoverOut);

  function mentionHoverIn() {
    var elem = $(this);
    elem.data('inside', 1)

    if (elem.data('loaded')) {
      $(this).popover('show');
    } else {
      var meaningId = elem.attr('title');
      $.getJSON(wwwRoot + 'ajax/getMeaningById', { id: meaningId })
        .done(function(resp) {
          var title = resp.description +
              (resp.breadcrumb ? ' (' + resp.breadcrumb + ')' : '');
          elem.attr('title', title);
          elem.data('loaded', 1);
          var p = new bootstrap.Popover(elem, {
            content: resp.html,
            html: true,
            title: title,
          });
          if (elem.data('inside')) {
            p.show();
          }
        });
    }
  }

  function mentionHoverOut() {
    $(this).popover('hide');
    $(this).data('inside', 0)
  }
});

/****************** „Read more” link for long sections ******************/

/**
 * Note: Attempting to render the 'expand' button on read-more does nothing if
 * the tab is hidden on page render, because its scroll height is zero.  To
 * collapse read-more sections in a hidden parent, call .readMore() once the
 * parent becomes visible.
 */

$(function() {
  const BTN_HTML =
        '<button class="read-more-btn btn btn-sm">' +
        '<span class="material-icons">expand_more</span>' +
        _('expand') +
        '</btn>';

  $.fn.readMore = function() {
    $(this).each(function() {

      var realHeight = $(this).prop('scrollHeight');
      var lineHeight = parseInt($(this).css('line-height')); // ignore the 'px' suffix
      var lines = $(this).data('readMoreLines');

      // If the whole thing isn't much larger than the proposed visible area,
      // don't hide anything
      if ((realHeight / lineHeight > lines * 1.33) &&
          !$(this).find('.read-more-btn').length) {

        $(this).css('max-height', (lines * lineHeight) + 'px');
        $(this).append(BTN_HTML);
      }
    });

    return this;
  }

  $('.read-more').readMore();

  $(document).on('click', '.read-more-btn', function() {
    var p = $(this).closest('.read-more')
    var realHeight = p.prop('scrollHeight');

    p.animate({ maxHeight: realHeight }, 1000);
    $(this).animate({ opacity: 0 }, 1000);
    //$('.read-more-btn').hide()

    return false;
  });
});

/************************ light/dark mode toggle ************************/

$(function() {
  $('.light-mode-toggle, .dark-mode-toggle').click(function() {
    setColorScheme($(this).data('mode'));
    $(this).hide();
    $(this).siblings().css('display', 'block');
    return false;
  });
});

/***************************** autocomplete *****************************/

/**
 * Note: <datalist>'s are tempting, but they are not aware of diacriticals.
 * Hence, a search for "saptamana" will not by default include the result
 * "săptămână", even if we add the <option> explicitly from Javascript.
 */

$(function() {

  const COMPACT_FORMS_URL = 'https://dexonline.ro/static/download/compact-forms/*.txt';

  var cache = {}; // map of first letter -> expanded forms
  var delayTimer; // delay before processing input
  var dropdown;
  var limit;      // number of results to display
  var minChars;   // when to kick in

  var d = $('#search-autocomplete');
  if (d.length) {
    limit = d.data('limit');
    minChars = d.data('minChars');
    dropdown = new bootstrap.Dropdown($('#searchField')[0]);

    $('#searchField').on('input', onInput);
    $('#searchField').on('show.bs.dropdown', onDropdownShow);
    $('#search-autocomplete').on('click', 'a', onSelect);
  }

  function onInput() {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(doAutocomplete, 100);
  }

  /* prevents showing an empty dropdown */
  function onDropdownShow() {
    return $('#search-autocomplete li').length > 0;
  }

  function onSelect(e) {
    $('#searchField').val($(this).text());
    $('#searchForm').submit();
  }

  /* removes diacriticals, returning an ASCII string */
  function translit(s) {
    return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  /* does the opposite of lib/Str.php:compactForms() */
  function expand(data) {
    var lines = data.split('\n');
    var result = [];

    var prev = '';
    for (var i = 0; i < lines.length; i++) {
      var common = lines[i][0] - '0';
      prev = prev.substr(0, common) + lines[i].substr(1);
      result.push(prev);
    }

    return result;
  }

  /**
   * Scans the word list (with or without diacriticals, based on whether the
   * query contains diacriticals). Returns up to @limit matches. Assumes the
   * necessary list is cached.
   */
  function match(term, trans) {
    var result = [];

    var lists = cache[trans[0]];
    var field = (term == trans) ? 1 : 0;
    var i = 0;
    while ((i < lists[0].length) && (result.length < limit)) {
      if (startsWith(lists[field][i].toLowerCase(), term)) {
        result.push(lists[0][i]);
      }
      i++;
    }
    return result;
  }

  /**
   * Builds the dropdown and shows or hides it as needed.
   */
  function output(term, trans) {
    var data = match(term, trans);
    var div = $('#search-autocomplete').empty();

    if (data.length) {
      for (var i = 0; i < data.length; i++) {
        div.append($('<li><a class="dropdown-item" href="#">' +  data[i] + '</a></li>'));
      }

      dropdown.show();
    } else {
      dropdown.hide();
    }
  }

  /**
   * Fetches the necessary word list if needed, then runs the autocomplete.
   */
  function doAutocomplete() {
    var term = $('#searchField').val().toLowerCase().trim();
    if (term.length < minChars) {
      dropdown.hide();
      return;
    }
    var trans = translit(term);
    var first = trans[0];

    if (!first.match(/[a-z]/i)) {
      dropdown.hide();
      return;
    }

    if (first in cache) {
      output(term, trans);
      return;
    }

    var url = COMPACT_FORMS_URL.replace('*', first);
    $.get(url)
      .done(function(data) {
        // cache the original data as well as the transliterated data
        data = expand(data);
        var transData = [];
        for (var i = 0; i < data.length; i++) {
          transData.push(translit(data[i].toLowerCase()));
        }
        cache[first] = [data, transData];
        output(term, trans);
      });
  }

});

/********************** source field in search form **********************/

$(function() {
  $('#source-field a.dropdown-item').click(function(e) {
    // copy the visible HTML
    $(this).closest('.dropdown').find('.dropdown-toggle').html($(this).html());

    // set the hidden field value
    $('#source-field-hidden').val($(this).data('value'));

    // prevent the # from being appended to the URL
    e.preventDefault();

    // perform the search (if the word is introduced) after change the source
    if ($('#searchField')[0].value) {
      $('#searchButton').click();
    }
  });

  $('#source-field').on('shown.bs.dropdown', function () {
    // make sure all the menu options are visible
    $('#source-field a.dropdown-item').show();

    // focus the filter
    $('#source-field input').val('').focus();
  });

  $('#source-field').on('hidden.bs.dropdown', function () {
    // go back to the main search field
    $('#searchField').focus();
  });

  $('#source-field input').on('keyup paste', function(e) {
    var isDownArrow = (e.keyCode == 40);

    if (isDownArrow) {
      // highlight the first visible option
      $('#source-field a.dropdown-item:visible').first().focus();
    } else {
      // perform the filtering
      var val = $(this).val().toLowerCase();
      $('#source-field a.dropdown-item').each(function() {
        var visible = $(this).text().toLowerCase().indexOf(val) !== -1;
        $(this).toggle(visible);
      });
    }
  });

});

/******************************* sortable *******************************/

$(function() {

  // Sortable.js may not be included, so don't try to call it when no
  // .sortable elements exist.
  $('.sortable').each(function() {
    $(this).sortable();
  });

});

/****** tab switch *******/
$(function() {
  var tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
  for (i=0; i<tabs.length; i++) {
    tabType = /[^/]*$/.exec($(tabs[i]).attr("data-permalink"))[0];
    if (tabType == 'imagini') {
      tabs[i].addEventListener('shown.bs.tab', function (event) {
        document.getElementsByClassName('gallery')[0].click();
      });
      // if the tab is open directly from link
      for (j=0; j<tabs[i].classList.length; j++) {
        if((tabs[i].classList)[j] == 'active') {
          $( document ).ready(function() {
            document.getElementsByClassName('gallery')[0].click();
          });
        }
      }
    }
  }
});

/* limba de lemn */
var limbaDeLemn = [
  ["Doamnelor și domnilor,","conjunctura actuală","trebuie integrată în finalizarea globală a","unui proces care să conducă spre trezirea în conștiință."],
  ["Rămân fundamental convins că","situația de excludere cu care se confruntă unii dintre dumneavoastră","arată că trebuie să facem față și mai eficient ","unui viitor care se îndreaptă spre mai mult progres și mai multă dreptate și egalitate."],
  ["Așadar, astăzi pot afirma în cunoștință de cauză că","determinarea acerbă de a scoate țara noastră din criză","are drept consecință obligatorie necesitatea urgentă a","unei valorizări fără concesii a caracterelor noastre specifice."],
  ["Profit de această ocazie pentru a-mi exprima hotărârea fermă de a afirma sus și tare că","prioritatea acordată statutului precar al celor excluși","îmi întărește dorința incontestabilă de a merge pe calea","unui plan care să răspundă cu adevărat cerințelor legitime ale tuturor."],
  ["De mult timp — după cum știți — apăr ideea că","natura specială a istoriei noastre unice","ar trebui să ne determine să facem o alegere cu adevărat imperativă a","soluțiilor rapide corespunzătoare principalelor axe sociale prioritare."],
  ["Cu toată conștiința afirm cu convingere că","aspirația mai mult decât legitimă a fiecăruia la progres social","trebuie să ia în considerare preocupările populației de bază în dezvoltarea","unui program mai uman, mai fratern și mai echitabil."],
  ["Cu siguranță nu sunteți voi, dragii mei compatrioți, cei care mă vor contrazice dacă vă spun că","nevoia de a răspunde preocupărilor voastre zilnice, fie că sunteți tineri sau bătrâni,","implică o misiune care este, în ansamblu, una dintre cele mai entuziasmante pentru mine: dezvoltarea","unui proiect care oferă o speranță reală, în special pentru cei mai dezavantajați."],
];

// Builds the HTML Table out of myList.
function buildHtmlTable(selector,myList) {
  var columns = addAllColumnHeaders(myList, selector);

  for (var i = 0; i < myList.length; i++) {
    var row$ = $('<tr/>');
    for (var colIndex = 0; colIndex < columns.length; colIndex++) {
      var cellValue = myList[i][columns[colIndex]];
      if (cellValue == null) cellValue = "";
      row$.append($('<td/>').html(cellValue));
    }
    $(selector).append(row$);
  }
}

function addAllColumnHeaders(myList, selector) {
  var columnSet = [];
  var headerTr$ = $('<tr/>');

  for (var i = 0; i < myList.length; i++) {
    var rowHash = myList[i];
    for (var key in rowHash) {
      if ($.inArray(key, columnSet) == -1) {
        columnSet.push(key);
        headerTr$.append($('<th/>').html(key));
      }
    }
  }
  $(selector).append(headerTr$);

  return columnSet;
}

function transpose(matrix) {
  return matrix[0].map((col, i) => matrix.map(row => row[i]));
}

function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
}

function genereazaLinie(matrice){
  var text = "";
  for (var i = 0; i < matrice.length; i++) {
    text += matrice[i][Math.floor(Math.random()*matrice[i].length)] + " ";
  }
  return text;
}

function genereazaDiscurs(mat){
  var matrice = mat.slice();
  for (var i = 0; i < matrice.length; i++) {
    shuffleArray(matrice[i]);
  }

  var text = "";
  var iteratii = matrice[0].length;
  for (var j = 0; j < iteratii; j++) {
    for (var i = 0; i < matrice.length; i++) {
      text += matrice[i][j] + " ";
    }
  }
  return text;
}

/*** START call for poll ***/
function pollModalCreate() {
  sessionStorage.setItem('pollModal', 1);
  var modalDiv = document.createElement('div');
  modalDiv.setAttribute("class", "pollModal");
  modalDiv.innerHTML='<div id="pollDiv" class="pollModalContent">' +
    '<b>Sondajele <i>dexonline</i></b><p></p>' +
    '<p>Votați pentru Nicușor Dan?</p><br/>' +
    '<input class="btn btn-primary btn-poll" type="submit" onclick="pollModalClose()" value=" DA ">' +
    '<input class="btn btn-primary btn-poll" type="submit" onclick="pollModalResponse()" value=" NU ">' +
    '</div>';
  domModal = document.body.insertBefore(modalDiv, document.getElementById('pageModal'));
  domModal.querySelector('input').focus();
}

function pollModalClose() {
  $('.pollModal').remove();
}

function pollModalResponse() {
  pollModalClose();
  var modalDiv = document.createElement('div');
  modalDiv.setAttribute("class", "pollModal");
  modalDiv.innerHTML='<div id="pollDiv" class="pollModalContent">' +
    '<p>Of! N-ați vrea să vă mai gândiți puțin? 🙇🏻‍♀️🙇‍♂️</p>' +
    '<p>E în joc viitorul nostru și al copiilor noștri. Haideți să alegem mintea deschisă și calculată a unui matematician, ' +
    'nu izolarea care ne-ar lăsa pe dinafară, la ușa Europei. Ce spuneți?</p><br/>' +
    '<input class="btn btn-primary btn-response" type="submit" onclick="pollModalClose()" value="OK, aveți dreptate, mă mai gândesc 👍">' +
    '</div>';
  domModal = document.body.insertBefore(modalDiv, document.getElementById('pageModal'));
  domModal.querySelector('input').focus();
}

setTimeout(function(){
  if (sessionStorage.getItem('pollModal') != 1) {
    pollModalCreate();
  }
}, 500);

/*** END call for poll ***/

/*** leonardo ***/
/*
function toggleLeonardo() {
    classListBody = document.body.classList;
    if (classListBody.contains('leonardo')) {
        classListBody.remove('leonardo');
        localStorage.setItem("leonardo", 0);
    } else {
        classListBody.add('leonardo');
        localStorage.setItem("leonardo", 1);
    }
}

function isHomePage() {
  if ($('.widgets').length != 0) {
    return true;
  }
  return false;
}

function leoModalClose() {
  $('.leoModal').hide();
}

function leoModalCloseAndForget() {
  localStorage.setItem('leoModalShown', 1);
  $('.leoModal').hide();
}

function leoModalCreate() {
  var modalDiv = document.createElement('div');
  modalDiv.setAttribute("class", "leoModal");
  modalDiv.innerHTML='<div id="leoDiv" class="leoModalContent">' +
    '<span class="leoClose" onclick="leoModalClose()">&times;</span><p>Sunteți pe modul <i>Leonardo</i>!</p>' +
    '<p>Astfel îi aducem astăzi un omagiu lui Leonardo da Vinci, născut pe 15 aprilie 1452. ' +
    'Avea modul său foarte inventiv de a scrie: de la dreapta la stânga, cu mâna stângă, ' +
    'încât era foarte dificil să îl citești fără oglindă. Se numește scriere „speculară” (speculum = oglindă).</p>' +
    '<p>Pentru a trece din modul <i>Leonardo</i> în modul normal și viceversa, pe calculator apăsați iconița ' +
    '<span class="material-icons ">swap_horiz</span> din partea de sus a paginii, iar pe telefon o găsiți ' +
    'în meniu, apăsând iconița <span class="material-symbols ">☰</span>.</p>' +
//    '<span class="leoForget" onclick="leoModalCloseAndForget()">Ascunde mesajul!</span>' +
    '</div>';
  document.body.insertBefore(modalDiv, document.getElementById('pageModal'));
}

setTimeout(function(){
//  if (localStorage.getItem('leoModalShown') != 1) {
    if (localStorage.getItem('leonardo') == 1) {
      if (!isHomePage()) {
        leoModalCreate();
      }
    }
//  }
}, 5000);



$(function() {
  // set by default (if it is not set yet)
  if (localStorage.getItem('leonardo') === null) {
    localStorage.setItem("leonardo", 1); // uncomment when it is ready!
  }
  if (localStorage.getItem('leonardo') == 1) {
    if (isHomePage()) {
      //do nothing on homepage
    }
    else {
      document.body.classList.add('leonardo');
      $('.leonardo-on-toggle').css('display', 'block');
    }
  } else {
    $('.leonardo-off-toggle').css('display', 'block');
  }

  $('.leonardo-on-toggle, .leonardo-off-toggle').click(function() {
    if (isHomePage()) {
      //do nothing on homepage
    }
    else {
      toggleLeonardo();
      $(this).hide();
      $(this).siblings().css('display', 'block');
    }
    return false;
  });
});

 */


