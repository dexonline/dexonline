var Alphabet = 'a-záàäåăâçèéëìíïĭîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

$(function() {
  $('p.def').click(searchClickedWord);
  $('#typoModal').on('shown.bs.modal', shownTypoModal);

  $('.searchField').select();

  $('.sourceDropDown').select2({
    templateResult: formatSource,
    templateSelection: formatSource,
    width: '100%',
  });

  var d = $('#autocompleteEnabled');
  if (d.length) {
    searchInitAutocomplete(d.data('minChars'));
  }

  // prevent double clicking of submit buttons
  $('input[type="submit"], button[type="submit"]').click(function() {
    if ($(this).data('clicked')) {
      return false;
    } else {
      $(this).data('clicked', true);
      return true;
    }
  });

  $('li.disabled a').click(function() {
    return false;
  });

  $('.defWrapper .deleteLink').click(deleteDefinition);
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

function formatSource(item) {
  return $('<span>' +
           item.text.replace(/(\(([^)]+)\))/, '<strong>$2</strong>') +
           '</span>');
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

function searchInitAutocomplete(acMinChars){

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

function getWwwRoot() {
  var pos = window.location.href.indexOf('/wwwbase/');
  if (pos == -1) {
    return '/';
  } else {
    return window.location.href.substr(0, pos + 9);
  }
}

function shownTypoModal(event) {
  var link = $(event.relatedTarget); // link that triggered the modal
  var defId = link.data('definitionId');
  $('input[name="definitionId"]').val(defId);
  $('#typoTextarea').focus();
  $('#typoSubmit').removeData('clicked'); // allow clicking the button again
}

function submitTypoForm() {
  var text = $('#typoTextarea').val();
  var defId = $('input[name="definitionId"]').val();
  $.post(wwwRoot + 'ajax/typo.php',
         { definitionId: defId, text: text, submit: 1 },
         function() {
           $('#typoModal').modal('hide');
           $('#typoTextarea').val('');
           $('#typoConfModal').modal();
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

  var source = $('.sourceDropDown').length ? $('.sourceDropDown').val() : '';
  if (source) {
    source = '-' + source;
  }

  if (word) {
    window.location = wwwRoot + 'definitie' + source + '/' + encodeURIComponent(word);
  }
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

function deleteDefinition() {
  var link = $(this);
  var defId = link.data('id');

  $.get(wwwRoot + 'ajax/deleteDefinition.php?id=' + defId)
    .done(function() { link.closest('.defWrapper').slideUp(); })
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
    var anchor = $(this);
    var url = anchor.attr('href');

    // show loading message
    anchor.find('span').text('un moment...');

    $.ajax({
      url: url,
      dataType: 'json',
      success: function (data) {
        handleAjaxResponse(data, anchor, addBookmarkSuccess, bookmarkResponseError);
      },
      error: function () {
        bookmarkResponseError(anchor);
      },
    });

    return false;
  }

  function addBookmarkSuccess(anchor) {
    anchor.find('span').text('adăugat la favorite');
    anchor.closest('li').addClass('disabled');
  }

  function removeBookmark(evt) {
    evt.preventDefault();

    var anchor = $(this);
    var url = anchor.attr('href');

    // show ajax indicator
    $(this).text('un moment...');

    $.ajax({
      url: url,
      dataType: 'json',
      success: function (data) {
        handleAjaxResponse(data, anchor, removeBookmarkSuccess, bookmarkResponseError);
      },
      error: function () {
        bookmarkResponseError(anchor);
      },
    });

    return false;
  }

  function removeBookmarkSuccess(anchor) {
    var favDefsParent = anchor.closest('.favoriteDef');
    var idx = favDefsParent.data('idx');

    // get all elements with matching data-dev
    var favDef = $('[data-idx="' + idx + '"]');

    // remove elements from the DOM
    favDef.fadeOut(function(){
      favDef.remove();

      // update favorites index
      // placed in the fadeout callback so the deleted items
      // will be removed from the dom before the length assertion
      var favDefs = favDefsParent.children('dd');
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

  function bookmarkResponseError(anchor, msg) {
    if(msg == null) {
      msg = 'eroare la încărcare';
    }
    anchor.find('span').text(msg);
    anchor.closest('li').addClass('disabled');
  }

  function handleAjaxResponse(data, anchor, successCallback, errorCallback) {
    console.log(data);
    if (data.status == 'success') {
      successCallback(anchor);
    } else if (data.status == 'redirect') {
      window.location.replace(wwwRoot + data.url);
    } else {
      errorCallback(anchor, data.msg);
    }
  }

  init();
});
