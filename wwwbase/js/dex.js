var Alphabet = 'a-záàäåăâçèéëìíïĭîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

$(function() {
  $('span.def').click(searchClickedWord);
  $('.inflLink').click(toggleInflections);
  $('#typoModal').on('shown.bs.modal', shownTypoModal);
  reviveInit();
});

if (typeof jQuery.ui != 'undefined') {
  $(function() {
    $('.tooltip2').tooltip({
      content: function () {
        return $(this).prop('title');
      },
      show: { delay: 10 },
      track: true
    });

    $('.tooltip').tooltip({
      content: function () {
          return $(this).html();
      },
      show: { delay: 10 },
      track: true
    });

    $('.mention').tooltip().each(resolveMention);
  });
}

function reviveInit() {
  if ($('#theZone').length) {
    var w = $(window).width(), zoneId, width;
    if (w > reviveBreakpoint1) {
      zoneId = reviveZoneId1;
      width = reviveWidth1;
    } else if (w > reviveBreakpoint2) {
      zoneId = reviveZoneId2;
      width = reviveWidth2;
    } else {
      zoneId = reviveZoneId3;
      width = reviveWidth3;
    }
    $('#theZone').attr('data-revive-zoneid', zoneId);
    $('#bannerWrapper').width(width);
  }
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
  var queryURL = wwwRoot + 'ajax/searchComplete.php';

  searchInput.autocomplete({
    delay: 500,
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

function shownTypoModal(event) {
  var link = $(event.relatedTarget); // link that triggered the modal
  var defId = link.data('definitionId');
  $('input[name="definitionId"]').val(defId);
  $('#typoTextarea').val('').focus();
}

function submitTypoForm() {
  var text = $('#typoTextarea').val();
  var defId = $('input[name="definitionId"]').val();
  $.post(wwwRoot + 'ajax/typo.php',
         { definitionId: defId, text: text, submit: 1 },
         function() {
           $('#typoModal').modal('hide');
           $('#typoConfModal').modal();
         });
  return false;
}

function toggle(id) {
  $('#' + id).slideToggle();
  return false;
}

function toggleInflections() {
  var div = $('#paradigmDiv');

  if (trim(div.html()) == '') {
    var lexemId = $(this).data('lexemId');
    var cuv = $(this).data('cuv');
	  var param = lexemId
        ? ('lexemId=' + lexemId)
        : ('cuv=' + cuv);
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
          'pentru a adăuga dexonline la lista motoarelor de căutare.');
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
  var regex = new RegExp(nonLetter + "$", 'i');
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

function resolveMention() {
  var elem = $(this);
  var meaningId = elem.data('originalTitle');
  $.getJSON(wwwRoot + 'ajax/getMeaningById', { id: meaningId })
    .done(function(resp) {
      elem.attr('data-original-title',
                '<b>' + resp.description + '</b> (' + resp.breadcrumb + '): ' + resp.htmlRep);
    });
}

function trim(str) {
	var	str = str.replace(/^\s\s*/, ''),
    ws = /\s/,
		i = str.length;
	while (ws.test(str.charAt(--i)));
	return str.slice(0, i + 1);
}

/************************* Bookmark-related code ***************************/
$(function() {

  function init() {
    $('.bookmarkAddButton').click(addBookmark);
    $('.bookmarkRemoveButton').click(removeBookmark);
  }

  function addBookmark() {
    var url = $(this).attr('href');
    var ajaxLoader = createAjaxLoader();

    // show ajax indicator
    $(this).replaceWith(ajaxLoader);

    $.ajax({
      url: url,
      success: function (data) { handleAjaxResponse(data, ajaxLoader, addBookmarkSuccess, bookmarkResponseError) },
      error: function () { bookmarkResponseError(ajaxLoader); },
      dataType: 'json'
    });

    return false;
  }

  function addBookmarkSuccess(targetEl) {
    targetEl.replaceWith('Adăugat la favorite');
  }

  function removeBookmark(evt) {
    evt.preventDefault();

    var url = $(this).attr('href');
    var ajaxLoader = createAjaxLoader();

    // show ajax indicator
    $(this).replaceWith(ajaxLoader);

    $.ajax({
      url: url,
      success: function (data) { handleAjaxResponse(data, ajaxLoader, removeBookmarkSuccess, bookmarkResponseError) },
      error: function () { bookmarkResponseError(ajaxLoader); },
      dataType: 'json'
    });
    removeBookmarkSuccess(ajaxLoader);
    return false;
  }

  function removeBookmarkSuccess(targetEl) {
    var idx = targetEl.parent().data('idx');
    var favDefsParent = targetEl.closest('.favoriteDefs');

    // get all elements with matching data-dev
    var favDef = $('[data-idx="' + idx + '"]');

    // remove elements from the DOM
    favDef.fadeOut(function(){
      favDef.remove();

      // update favorites index
      // placed in the fadeout callback so the deleted items
      // will be removed from the dom before the length assertion
      var favDefs = favDefsParent.children('dt');
      if (favDefs.length > 0) {
        favDefs.each(function(idx, elem){
          var fav = $(elem);
          var dd = fav.next();
          var new_idx = idx + 1;

          fav.children('.count').text(new_idx + '.');
        });
      } else {
        favDefsParent.text('Nu aveți niciun cuvânt favorit.');
      }
    });
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

  init();
});
