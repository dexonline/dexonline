var Alphabet = 'a-záàäåăâçèéëìíïĭîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

$(function() {
  $('span.def').click(searchClickedWord);
});

if (typeof jQuery.ui != 'undefined') {
  $(function() {
    $(document).tooltip({
      content: function () {
        return $(this).prop('title');
      }
    });

    $('.mention').tooltip({
      content: function(callback) {
        var meaningId = $(this).prop('title');
        $.getJSON(wwwRoot + 'ajax/getMeaningById', { id: meaningId })
          .done(function(resp) {
            callback('<b>' + resp.lexem + ' (' + resp.breadcrumb + '):</b> ' + resp.htmlRep);
          })
          .fail(function() {
            callback('');
          });
      }
    });
  });
}

function loadAjaxContent(url, elid) {
  $.get(url, function(data) {
    $(elid).html(data);
  });
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


function searchInitFocus() {
  document.frm.cuv.select();
  document.frm.cuv.focus();

  function slash(evt) { // ignore / and let it be used by the browser
    evt = evt || window.event;
    var charCode = evt.keyCode || evt.which;
    if (charCode == 191 && !evt.shiftKey) {
      this.blur();
      return false;
    }
  }

  document.frm.cuv.addEventListener("keydown", slash, false);
}


function searchInitAutocomplete(acMinChars, wwwRoot){

  var searchForm = $('#searchForm');
  var searchInput = $('.searchField');
  var searchCache = {};
  var queryURL = wwwRoot + 'searchComplete.php';

  searchInput.autocomplete({
    minLength: acMinChars,
    source: function(request, response){
      var term = request.term;
      if (term in searchCache){
        response(searchCache[term]);
        return;
      }
      $.getJSON(queryURL, request, function(data, status, xhr){
        searchCache[term] = data;
        response(data);
      });
    },
    select: function(event, ui){
      searchInput.val(ui.item.value);
      searchForm.submit();
    }
  });
}

function searchInit(acEnable, acMinChars) {
  searchInitFocus();
  if (acEnable) {
    searchInitAutocomplete(acMinChars, wwwRoot);
  }
}

function getWwwRoot() {
  var pos = window.location.href.indexOf('/wwwbase/');
  if (pos == -1) {
    return '/';
  } else {
    return window.location.href.substr(0, pos + 9);
  }
}

function randomDigits(count) {
  var s = '';
  while (count--) {
    s += Math.floor(Math.random() * 10);
  }
  return s;
}

function showTypoForm(evt) {
  link = evt.target;
  desiredTop = link.offsetTop + link.offsetHeight;
  definitionId = link.id.substr(9); // Skip typoLink- prefix
  currentTop = document.getElementById('typoDiv').offsetTop;
  $.get(wwwRoot + 'ajax/typo.php?definitionId=' + definitionId, function(data) {
    div = $('#typoDiv').html(data);
    divMarginTop = parseInt(div.css('margin-top').replace('px', ''));
    // Hide the div if it was already open at the same coords.
    // Show the div if it was shown at other coords or hidden
    if (div.css('display') == 'none' || desiredTop + divMarginTop != currentTop) {
      div.css('display', 'block');
    } else {
      div.css('display', 'none');
    }
    div.css({ 'top': desiredTop, 'left': link.offsetLeft });
  });
  return false;
}

function submitTypoForm() {
  textarea = $("#typoHtmlForm > textarea").val();
  defId = $("#typoHtmlForm > input:hidden").val();
  $.post(wwwRoot + 'ajax/typo.php', { definitionId: defId, text: textarea, submit: 1 }, function(data) {
    $('#typoDiv').html(data).delay(2000).fadeOut('slow');
  });
  return false;
}

function contribBodyLoad() {
  contribUpdatePreviewDiv();
}

function contribKeyPressed() {
  var previewDiv = document.getElementById('previewDiv');
  previewDiv.keyWasPressed = true;
}

function contribUpdatePreviewDiv() {
  var previewDiv = document.getElementById('previewDiv');
  if (previewDiv.keyWasPressed) {
    var internalRep = document.frmContrib.def.value;
    $.post(wwwRoot + 'ajax/htmlize.php', { internalRep: internalRep, sourceId: document.frmContrib.source.value })
      .done(function(data) { $('#previewDiv').html(data); })
      .fail(contribPreviewFail);
    previewDiv.keyWasPressed = false;
  }
  setTimeout('contribUpdatePreviewDiv()', 5000);
}

function contribPreviewFail() {
  $('#previewDiv').html('Este o problemă la comunicarea cu serverul. Voi reîncerca în 5 secunde.');
  contribKeyPressed();   // Force another attempt in 5 seconds.
}

function toggleDivVisibility(divId) {
  var div = document.getElementById(divId);
  if (div.style.display == 'block') {
    div.style.display = 'none';
  } else {
    div.style.display = 'block';
  }
  return false;
}

function toggleInflVisibility(value, lexem) {
  var div = $('#paradigmDiv');
  if (trim(div.html()) == '') {
	  param = (lexem ? 'lexemId' : 'cuv') + '=' + value;
    $.get(wwwRoot + 'paradigm.php?ajax=1&' + param)
      .done(function(data) { div.html(data).slideToggle(); }); // Slide only after content is added
  } else {
    div.slideToggle();
  }
  var arrow = $('#inflArrow');
  arrow.html((arrow.html() == '\u25bd') ? '&#x25b7;' : '&#x25bd;');
  return false;
}

function addProvider(url) {
  try {
    window.external.AddSearchProvider(url);
  } catch (e) {
    alert('Aveți nevoie de Firefox 2.0 sau Internet Explorer 7 ' +
          'pentru a adăuga DEX online la lista motoarelor de căutare.');
  }
}

function startsWith(str, sub) {
  return str.substr(0, sub.length) == sub;
}

function endsWith(str, sub) {
  return str.substr(str.length - sub.length) == sub;
}

function debug(obj) {
  var s = '';
  for (prop in obj) {
    if (obj[prop] && !startsWith(obj[prop].toString(), 'function ')) {
      s += prop + ':' + obj[prop] + '\n';
    }
  }
  alert(s);
}

function confirmDissociateDefinition(id) {
  return confirm('Doriți să disociați definiția ' + id + ' de acest lexem?');
}

/* adapted from http://stackoverflow.com/questions/7563169/detect-which-word-has-been-clicked-on-within-a-text */
function searchClickedWord(event) {
  if ($(event.target).is('abbr')) return false;

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
  } else if ((sel = document.selection) && sel.type != "Control") {
    // IE 4+
    var textRange = sel.createRange();
    if (!textRange.text) {
      textRange.expand("word");
      while (/\s$/.test(textRange.text)) {
        textRange.moveEnd("character", -1);
      }
      word = textRange.text;
    }
  }

  // Trim trailing dots
  var regex = new RegExp(nonLetter + "$");
  while (word && regex.test(word)) {
    word = word.substr(0, word.length - 1);
  }

  var source = $('#sourceDropDown').length ? $('#sourceDropDown').val() : '';
  if (source) {
    source = '-' + source;
  }

  if (word) {
    window.location = wwwRoot + 'definitie' + source + '/' + encodeURIComponent(word);
  }
}

function hideSubmitButton(button) {
  button.disabled = true;
  button.form.submit();
  return false;
}

function installFirefoxSpellChecker(evt) {
  var params = {
    "ortoDEX": { URL: evt.target.href,
                 toString : function() { return this.URL; }
    }
  }
  InstallTrigger.install(params);
  return false;
}

function ignoreTypo(typoDivId, typoId) {
  $.get(wwwRoot + 'ajax/ignoreTypo.php', { id: typoId })
    .done(function() { $('#' + typoDivId).css('display', 'none'); })
    .fail(function() { alert('A apărut o problemă la comunicarea cu serverul. Greșeala de tipar nu a fost încă ștearsă.'); });
  return false;
}

function deleteDefinition(defDivId, defId) {
  $.get(wwwRoot + 'ajax/deleteDefinition.php', { id: defId })
    .done(function() { $('#' + defDivId).css('display', 'none'); })
    .fail(function() { alert('A apărut o problemă la comunicarea cu serverul. Definiția nu a fost încă ștearsă.'); });
  return false;
}

function editModelAppendBox(inflId) {
  var obj = $('#td_' + inflId);
  var count = $('#td_' + inflId + '> p').length;
  obj.append('<p>');
  obj.append('<input class="fieldColumn" type="text" name="forms_' + inflId + '_' + count + '" value=""/> ');
  obj.append('<input class="checkboxColumn" type="checkbox" name="isLoc_' + inflId + '_' + count + '" value="1" checked="checked"/>');
  obj.append('<input class="checkboxColumn" type="checkbox" name="recommended_' + inflId + '_' + count + '" value="1" checked="checked"/>');
  obj.append('</p>');
  return false;
}

function trim(str) {
	var	str = str.replace(/^\s\s*/, ''),
    ws = /\s/,
		i = str.length;
	while (ws.test(str.charAt(--i)));
	return str.slice(0, i + 1);
}

// Add/remove bookmarks
function addBookmark(linkElement) {
  var url = linkElement.attr('href');
  var ajaxLoader = createAjaxLoader();

  // show ajax indicator
  linkElement.replaceWith(ajaxLoader);

  $.ajax({
    url: url,
    success: function (data) { handleAjaxResponse(data, ajaxLoader, addBookmarkSuccess, bookmarkResponseError) },
    error: function () { bookmarkResponseError(ajaxLoader); },
    dataType: 'json'
  });
}

function addBookmarkSuccess(targetEl) {
  targetEl.replaceWith('Adăugat la favorite');
}

function removeBookmark(linkElement) {
  var url = linkElement.attr('href');
  var ajaxLoader = createAjaxLoader();

  // show ajax indicator
  linkElement.replaceWith(ajaxLoader);

  $.ajax({
    url: url,
    success: function (data) { handleAjaxResponse(data, ajaxLoader, removeBookmarkSuccess, bookmarkResponseError) },
    error: function () { bookmarkResponseError(ajaxLoader); },
    dataType: 'json'
  });
  removeBookmarkSuccess(ajaxLoader);
}

function removeBookmarkSuccess(targetEl) {
  var favDef = targetEl.closest('div.favoriteDef');
  var favDefsDiv = favDef.parent();

  // remove element from the DOM
  favDef.remove();

  // update favorites index
  var favDefs = favDefsDiv.children('div');
  if (favDefs.length > 0) {
    for(var i=0; i < favDefs.length; i++) {
      var index = i + 1;
      var fav = $(favDefs[i]);
      fav.children('b').text(index + '.');
    }
  } else {
    favDefsDiv.text('Nu aveți niciun cuvânt favorit.');
  }
}

function bookmarkResponseError(targetEl, msg) {
  if(msg == null) {
    msg = 'Eroare la încărcare';
  }
  targetEl.replaceWith(msg);
}

function handleAjaxResponse(data, targetEl, successCallback, errorCallback) {
  if (data.status == 'success') {
    successCallback(targetEl);
  } else if (data.status == 'redirect') {
    window.location.replace(wwwRoot + data.url);
  } else {
    errorCallback(targetEl, data.msg);
  }
}

function createAjaxLoader() {
  return $('<img src="' + wwwRoot + 'img/icons/ajax-indicator.gif" />');
}

if (typeof jQuery != 'undefined') {
  $(document).ready(function() {
    $('body').click(function() {
      $('#mainMenu li ul, #userMenu li ul').hide();
    });
    $('#mainMenu > li').click(function(event) {
      event.stopPropagation();
      $(this).siblings().children('ul').hide();
      $('#userMenu li ul').hide();
      $(this).children('ul').toggle();
    });
    $('#userMenu > li').click(function(event) {
      event.stopPropagation();
      $('#mainMenu li ul').hide();
      $(this).children('ul').toggle();
    });
  });
}
