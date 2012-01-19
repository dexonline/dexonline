var dom = { ELEMENT_NODE: 1, TEXT_NODE: 3 };
var Alphabet = 'a-záàäåăâçèéëìíïîòóöșțşţùúüŭ';
var letter = '[' + Alphabet + ']';
var nonLetter = '[^' + Alphabet + ']';
var wwwRoot = getWwwRoot();

function loadAjaxContent(url, elid) {
    $.get(url, function(data) {
        $(elid).html(data);
    });
}

function friendlyRedirect() {
  action = document.frm.text.checked ? 'text' : 'definitie';
  source = document.frm.source.value;
  sourcePart = source ? '-' + source : '';
  window.location = wwwRoot + action + sourcePart + '/' + encodeURIComponent(document.frm.cuv.value);
  return false;
}

function getWwwRoot() {
  var pos = window.location.href.indexOf('/wwwbase/');
  if (pos == -1) {
    return '/';
  } else {
    return window.location.href.substr(0, pos + 9);
  }
}

function abbrevWindow() {
  window.open(wwwRoot + 'static.php?c=abrev', 'mywindow', 'menubar=no,scrollbars=yes,toolbar=no,width=400,height=400');
}

function adminHelpWindow(anchorName) {
  var url = wwwRoot + 'static.php?c=adminHelp';
  if (anchorName) {
    url += '#' + anchorName;
  }
  window.open(url, 'adminHelpWindow', 'menubar=no,scrollbars=yes,toolbar=no,width=400,height=400');
  return false;
}

function showTypoForm(evt) {
  definitionId = evt.target.id.substr(9); // Skip typoLink- prefix
  $.get(wwwRoot + 'ajax/typo.php?definitionId=' + definitionId, function(data) {
    $('#typoDiv').html(data).css({
      top: evt.pageY + 10,
      left: evt.pageX,
    }).toggle();
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

function myEncodeURI(s) {
  var encoded = encodeURI(s);
  encoded = encoded.replace(/\+/g, '%2B');
  encoded = encoded.replace(/\&/g, '%26');
  return encoded;
}

function contribBodyLoad() {
  document.frmContrib['lexemNames[]'][1].focus();
  contribUpdatePreviewDiv();
  $('#frmContrib').find('.autocompletable').autocomplete(wwwRoot + 'ajax/getLexems.php');
}

function contribKeyPressed() {
  var previewDiv = document.getElementById('previewDiv');
  previewDiv.keyWasPressed = true;
}

function contribUpdatePreviewDiv() {
  var previewDiv = document.getElementById('previewDiv');
  if (previewDiv.keyWasPressed) {
    var internalRep = document.frmContrib.def.value;
    makePostRequest(wwwRoot + 'ajax/htmlize.php',
                    'internalRep=' + myEncodeURI(internalRep) + '&sourceId=' + document.frmContrib.source.value,
                    contribPostRequestCallback, null);
    previewDiv.keyWasPressed = false;
  }
  setTimeout('contribUpdatePreviewDiv()', 5000);
}

// Kudos http://www.captain.at/howto-ajax-form-post-get.php
// and http://www.captain.at/howto-ajax-form-post-request.php
function instantiateRequest() {
  var request = false;
  if (window.XMLHttpRequest) { // Mozilla, Safari,...
    request = new XMLHttpRequest();
    if (request.overrideMimeType) {
      request.overrideMimeType('text/html');
    }
  } else if (window.ActiveXObject) { // IE
    try {
      request = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        request = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {}
    }
  }

  if (!request) {
    alert('Nu am putut crea obiectul de tip XMLHttp/ActiveX');
  }
  return request;
}

function makePostRequest(url, parameters, callback, argument) {
  var httpRequest = instantiateRequest();
  if (!httpRequest) {
    return false;
  }
  
  httpRequest.onreadystatechange = function() {
    callback(httpRequest, argument);
  };
  httpRequest.open('POST', url, true);
  httpRequest.setRequestHeader("Content-type",
                                "application/x-www-form-urlencoded");
  httpRequest.setRequestHeader("Content-length", parameters.length);
  httpRequest.setRequestHeader("Connection", "close");
  httpRequest.send(parameters);
  return false;
}

function makeGetRequest(url, callback, argument) {
  var httpRequest = instantiateRequest();
  if (!httpRequest) {
    return false;
  }
  
  httpRequest.onreadystatechange = function() {
    callback(httpRequest, argument);
  }
  httpRequest.open('GET', url, true);
  httpRequest.send(null);
  return false;
}

function contribPostRequestCallback(httpRequest, argument /* ignored */) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      result = httpRequest.responseText;
    } else {
      result = 'Este o problemă la comunicarea cu serverul. ' +
        'Nu pot afișa rezultatul, dar voi reîncerca în 5 secunde.';
      contribKeyPressed();   // Force another attempt in 5 seconds.
    }
    document.getElementById('previewDiv').innerHTML = result;
  }
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
  var div = document.getElementById('paradigmDiv');
  if (trim(div.innerHTML) == '') {
	  param = (lexem ? 'lexemId' : 'cuv') + '=' + value;
	  makeGetRequest(wwwRoot + 'paradigm.php?ajax=1&' + param, getParadigmCallback, null);
  }
  var arrow = document.getElementById('inflArrow');
  if (div.className == 'paradigmHide') {
	div.className = 'paradigmShow';
    arrow.innerHTML = '&#x25bd;';
  }
  else {
	div.className = 'paradigmHide';
    arrow.innerHTML = '&#x25b7;';
  }
  return false;
}

function getParadigmCallback(httpRequest) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var paradigmDiv = document.getElementById("paradigmDiv");
      paradigmDiv.innerHTML = httpRequest.responseText;
    } else {
      alert('A apărut o problemă la comunicarea cu serverul. Nu pot afișa paradigma.');
    }
  }
}

function loadWotd() {
    makeGetRequest(wwwRoot + 'da-mi/cuvantul-zilei', getWotdCallback, null);
}

function getWotdCallback (httpRequest){
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
        var wotd = document.getElementById('wotd');
        wotd.innerHTML = 'Cuvântul zilei: ' + httpRequest.responseText;
        wotd.style.display = 'block';
    }
    else {
        //do something in case of error
    }
  }
}

function addToEngines() {
  if ((typeof window.sidebar == 'object') &&
      (typeof window.sidebar.addSearchEngine == 'function')) {
    window.sidebar.addSearchEngine('http://dexonline.ro/download/dexonline.src',
                                   'http://dexonline.ro/download/dexonline.png',
                                   'dexonline',
                                   'DEX online');
  } else {
    alert('Este nevoie de Netscape 6, Mozilla sau Firefox ' +
          'pentru a instala această extensie.');
  }
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

/**
 * getWordFromEvent() and expandRangeToWord()
 * Copyright (C) Martin Honnen
 * See http://www.faqts.com/knowledge_base/view.phtml/aid/33674/fid/145
 **/
function getWordFromEvent(evt) {
  var range = null;
  if (document.body && document.body.createTextRange) {
    /* IE */
    range = document.body.createTextRange();
    range.moveToPoint(evt.clientX, evt.clientY);
    range.expand('word');
    return range.text;
  } else if (evt.rangeParent && document.createRange) {
    /* Mozilla */
    range = document.createRange();
    range.setStart(evt.rangeParent, evt.rangeOffset);
    range.setEnd(evt.rangeParent, evt.rangeOffset);
    expandRangeToWord(range);
    var word = range.toString();
    range.detach();
    return word;    
  } else {
    /* Opera, Safari or any other W3DOM compatible browser */
    if (evt.target.nodeType == dom.ELEMENT_NODE) {
      markWords(evt.target);
      return wordUnderMouse(evt.target, evt);
    }
    return null;
  }
}

/* Recursively mark words in custom <span> tags */
function markWords(elem) {
  var wordRe = new RegExp('([^'+Alphabet+']*)(['+Alphabet+']*)', 'gim');

  for (var i = 0; i < elem.childNodes.length; ++i) {
    var child = elem.childNodes.item(i);
    if (child.nodeType == dom.ELEMENT_NODE) {
      markWords(child);
    } else if (child.nodeType == dom.TEXT_NODE) {
      var newChild = document.createElement('span');

      for (wordRe.lastIndex = 0; wordRe.lastIndex < child.data.length; ) {
        var match = wordRe.exec(child.data);
        /* there's always a match, wordRe accepts empty string */
        if (match[1]) {
          /* Wrap the non-word string in a TextNode object since it
           * it may contain invalid or harmful XHTML characters. */
          newChild.appendChild(document.createTextNode(match[1]));
        }
        if (match[2]) {
          /* Wrap the word string a <span class="_mw"> tag */
          var wordSpan = document.createElement('span');
          wordSpan.setAttribute('class', '_mw');
          wordSpan.className = '_mw';
          wordSpan.appendChild(document.createTextNode(match[2]));
          newChild.appendChild(wordSpan);
        }
      }

      elem.replaceChild(newChild, child);
    }
  }
}

/* Recursively search for a <span> element under the mouse pointer */
function wordUnderMouse(elem, evt) {
  for (var i = 0; i < elem.childNodes.length; ++i) {
    var child = elem.childNodes.item(i);
    if (child.nodeType != dom.ELEMENT_NODE) {
      continue;
    }
    if ('_mw' == child.className) {
      if (evt.clientX >= child.offsetLeft && evt.clientY >= child.offsetTop
          && evt.clientX < child.offsetLeft + child.offsetWidth
          && evt.clientY < child.offsetTop + child.offsetHeight) {
        return child.textContent;
      }
    } else {
      var childWord = wordUnderMouse(child, evt);
      if (null != childWord) return childWord;
    }
  }
  return null;
}

function expandRangeToWord(range) {
  var startOfWord = new RegExp('^' + nonLetter + letter + '+$', 'i');
  var endOfWord = new RegExp('^' + letter + '+' + nonLetter + '$', 'i');
  var whitespace = new RegExp('^' + nonLetter + '+$', 'i');
  // if offset is inside whitespace
  range.setStart(range.startContainer, range.startOffset - 1);

  if (whitespace.test(range.toString())) {
    return null;
  }
  while (!startOfWord.test(range.toString()) && range.startOffset > 0) {
    range.setStart(range.startContainer, range.startOffset - 1);
  }
  if (startOfWord.test(range.toString())) {
    range.setStart(range.startContainer, range.startOffset + 1);
  }
  var maxOffset = 1000;
  if (range.endContainer.nodeType == 3) { // text
    maxOffset = range.endContainer.nodeValue.length;
  }
  while (!endOfWord.test(range.toString()) &&
         range.endOffset < maxOffset) {
    range.setEnd(range.endContainer, range.endOffset + 1);
  }
  if (endOfWord.test(range.toString())) {
    range.setEnd(range.endContainer, range.endOffset - 1);
  }
  return range.toString();
}

function isValidWord(word) {
	var reWord = new RegExp('^' + letter + '+$', 'i');
	if ((word != null) && reWord.test(word)) {
		return true;
	}
	return false;
}

function searchClickedWord(evt) {
  var st = getSelectedText();
  if (st && st != '') {
    // Allow text selection without redirecting.
    return false;
  }
  var word = getWordFromEvent(evt);
  if ( isValidWord(word) ) {
    source = document.frm.source.value;
    sourcePart = source ? '-' + source : '';
    loc = wwwRoot + 'definitie' + sourcePart + '/' + encodeURIComponent(word);
    window.location = loc;
  }
  else {
	  return false;
  }
  return true;
}

function hideSubmitButton(button) {
  button.disabled = true;
  button.form.submit();
  return false;
}

/**
 * getSelectedText() Copyright (C) Quirksmode
 * See http://www.quirksmode.org/dom/range_intro.html
 */
function getSelectedText() {
  var st = null;
  if (window.getSelection) {
    st = window.getSelection();
  } else if (document.selection) { // should come last; Opera!
    st = document.selection.createRange();
  }

  if (st != null && st.text != null) {
    st = st.text;
  }
  return st;
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
  makePostRequest(wwwRoot + 'ajax/ignoreTypo.php', 'id=' + typoId, ignoreTypoCallback, typoDivId);
  return false;
}

function ignoreTypoCallback(httpRequest, typoDivId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var typoDiv = document.getElementById(typoDivId);
      typoDiv.style.display = 'none';
    } else {
      alert('A apărut o problemă la comunicarea cu serverul. Greșeala de tipar nu a fost încă ștearsă.');
    }
  }
}

function deleteDefinition(defDivId, defId) {
  makePostRequest(wwwRoot + 'ajax/deleteDefinition.php', 'id=' + defId, deleteDefinitionCallback, defDivId);
  return false;
}

function deleteDefinitionCallback(httpRequest, defDivId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var defDiv = document.getElementById(defDivId);
      defDiv.style.display = 'none';
    } else {
      alert('A apărut o problemă la comunicarea cu serverul. Definiția nu a fost încă ștearsă.');
    }
  }
}

function startReportCounters() {
  reports = ['unassociatedLexems', 'unassociatedDefinitions', 'definitionsWithTypos', 'temporaryDefinitions', 'temporaryLexems', 'lexemsWithComments',
             'lexemsWithoutAccents', 'definitionsWithAmbiguousAbbrev', 'wotd'];
  for (var i = 0; i < reports.length; i++) {
    makePostRequest(wwwRoot + 'ajax/reportCounter.php', 'report=' + reports[i], startReportCountersCallback, 'span_' + reports[i]);
  }
}

function startReportCountersCallback(httpRequest, spanId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var span = document.getElementById(spanId);
      if (span) {
        span.innerHTML = httpRequest.responseText;
      }
    } else {
        span.innerHTML = 'eroare la încărcare';
    }
  }
}

function addLexemRow() {
  var tr = $('#blankRow').clone().appendTo('#lexemTable tbody').removeAttr('id').css('display', 'table-row');
  tr.find("input").autocomplete(wwwRoot + "ajax/getLexems.php");
  tr.find("input").focus();
  return false;
}

function contribAddLexemRow() {
  var div = $('#blankDiv').clone().appendTo('#lexemHolder').removeAttr('id').css('display', 'block');
  div.find("input").autocomplete(wwwRoot + "ajax/getLexems.php");
  div.find("input").focus();
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
  if(data.status == 'success') {
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

/** http://javascript-array.com/scripts/jquery_simple_drop_down_menu/ **/
var jsddm_timeout = 500;
var jsddm_closetimer = 0;
var jsddm_menuitem = 0;

function jsddm_open() {
  jsddm_canceltimer();
  jsddm_close();
  jsddm_menuitem = $(this).find('ul').eq(0).css('visibility', 'visible');
}

function jsddm_close() {
  if (jsddm_menuitem) {
    jsddm_menuitem.css('visibility', 'hidden');
  }
}

function jsddm_timer() {
  jsddm_closetimer = window.setTimeout(jsddm_close, jsddm_timeout);
}

function jsddm_canceltimer() {
  if (jsddm_closetimer) {
    window.clearTimeout(jsddm_closetimer);
    jsddm_closetimer = null;
  }
}

if (typeof jQuery != 'undefined') { 
  $(document).ready(function() {
    $('#mainMenu > li').bind('mouseover', jsddm_open);
    $('#mainMenu > li').bind('mouseout',  jsddm_timer);
    $('#userMenu > li').bind('mouseover', jsddm_open);
    $('#userMenu > li').bind('mouseout',  jsddm_timer);
  });
}

document.onclick = jsddm_close;
