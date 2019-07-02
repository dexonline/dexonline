var Alphabet = 'a-záàäåăâçèéëìíïĭîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

$(function() {
  $('.def').click(searchClickedWord);
  $('#typoModal').on('shown.bs.modal', shownTypoModal);

  $('#searchField').select();
  $('#searchClear').click(function(){
    $('#searchField').val('').focus();
  });
  if ($('#searchField').val()) {
    // Make it visible all the time. Otherwise it's only visible when the field loses focus,
    // which is bad for us, because we focus the field on page load.
    $('#searchClear').css('z-index', 3);
  }

  $('.sourceDropDown').select2({
    templateResult: formatSource,
    templateSelection: formatSource,
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

  $('.doubleText').click(function() {
    var tmp = $(this).text();
    $(this).text($(this).attr('data-other-text'));
    $(this).attr('data-other-text', tmp);
  });

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

    $('sup.footnote').tooltip({ html: true});

    $('.mention').hover(mentionHoverIn, mentionHoverOut);
  });
}

function formatSource(item) {
  return $('<span>' +
           item.text.replace(/(\(([^)]+)\))/, '<strong>$2</strong>') +
           '</span>');
}

function reviveInit() {
  if ($('#theZone').length) {
    var w = $(window).width(), zoneId;
    if (w > reviveBreakpoint1) {
      zoneId = reviveZoneId1;
      // single zone invocation since we have no banners for other resolutions
      // TODO move these lines outside the if once we have more banners
      $('#theZone').attr('data-revive-zoneid', zoneId);
      $.getScript(reviveUrl);
    } else if (w > reviveBreakpoint2) {
      zoneId = reviveZoneId2;
    } else {
      zoneId = reviveZoneId3;
    }
  }
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
  var searchInput = $('#searchField');
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

function mentionHoverIn() {
  var elem = $(this);

  if (elem.data('loaded')) {
    $(this).popover('show');
  } else {
    var meaningId = elem.attr('title');
    $.getJSON(wwwRoot + 'ajax/getMeaningById', { id: meaningId })
      .done(function(resp) {
        elem.removeAttr('title');
        elem.data('loaded', 1);
        elem.popover({
          content: resp.html,
          title: resp.description + ' (' + resp.breadcrumb + ')',
        }).popover('show');
      });
  }
}

function mentionHoverOut() {
  $(this).popover('hide');
}

function trim(str) {
	var	str = str.replace(/^\s\s*/, ''),
    ws = /\s/,
		i = str.length;
	while (ws.test(str.charAt(--i)));
	return str.slice(0, i + 1);
}
