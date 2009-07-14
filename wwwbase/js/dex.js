var dom = { ELEMENT_NODE: 1, TEXT_NODE: 3 };
var Alphabet = 'a-záàäåăâçèéëìíïîòóöșțùúüŭ';

function abbrevwindow() {
  window.open('html/abrev.html', 'mywindow',
              'menubar=no,scrollbars=yes,toolbar=no,width=400,height=400');
}

function helpWindow() {
  window.open('html/search_help.html', 'helpWindow',
              'menubar=no,scrollbars=yes,toolbar=no,width=400,height=200');
}

function adminHelpWindow(anchorName) {
  var url = '../html/admin_help.html';
  if (anchorName) {
    url += '#' + anchorName;
  }
  window.open(url, 'adminHelpWindow',
              'menubar=no,scrollbars=yes,toolbar=no,width=400,height=400');
  return false;
}

function typoWindow(definitionId) {
  window.open('flag_typo.php?definitionId=' + definitionId,
              'typoWindow',
              'menubar=no,scrollbars=yes,toolbar=no,width=500,height=400');
}

// Functions for the contribution page
function formatwindow() {
  window.open('html/formatting.html','formatwindow',
              'menubar=no,scrollbars=yes,toolbar=no,width=500,height=400');
}

function myEncodeURI(s) {
  var encoded = encodeURI(s);
  encoded = encoded.replace(/\+/g, '%2B');
  return encoded;
}

function contribBodyLoad() {
  document.frm.wordName.focus();
  contribUpdatePreviewDiv();
}

function contribKeyPressed() {
  var previewDiv = document.getElementById('previewDiv');
  previewDiv.keyWasPressed = true;
}

function contribUpdatePreviewDiv() {
  var previewDiv = document.getElementById('previewDiv');
  if (previewDiv.keyWasPressed) {
    var internalRep = document.frm.def.value;
    makePostRequest('ajax/htmlize.php',
                    'internalRep=' + myEncodeURI(internalRep),
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

function findChildWithNodeName(node, name) {
  for (var i = 0; i < node.childNodes.length; i++) {
    if (node.childNodes[i].nodeName == name) {
      return node.childNodes[i];
    }
  }
  return null;
}

function findNextSiblingWithNodeName(node, name) {
  if (!node) {
    return null;
  }
  do {
    node = node.nextSibling;
  } while (node && node.nodeName != name);
  return node;
}

function findPreviousSiblingWithNodeName(node, name) {
  if (!node) {
    return null;
  }
  do {
    node = node.previousSibling;
  } while (node && node.nodeName != name);
  return node;
}

function flexAddRow(tableId) {
  var tbl = document.getElementById(tableId);
  var tbody = findChildWithNodeName(tbl, 'TBODY');
  var prototypeRow = tbl.rows[1];
  var clone = prototypeRow.cloneNode(true);
  clone.id = '';
  clone.style.display = 'table-row';
  tbody.appendChild(clone);
}

function flexDeleteRow(anchor) {
  var row = anchor.parentNode.parentNode;
  var tbody = row.parentNode;
  tbody.removeChild(row);
  return false;
}

function findPreviousRow(row) {
  row = row.previousSibling;
  while (row && row.nodeName != 'TR') {
    row = row.previousSibling;
  }
  return row;
}

function findNextRow(row) {
  row = row.nextSibling;
  while (row && row.nodeName != 'TR') {
    row = row.nextSibling;
  }
  return row;
}

function removeAllTableRowsExceptFirst(table) {
  while (table.rows.length > 1) {
    table.deleteRow(-1);
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

function toggleInflVisibility() {
  var div = document.getElementById('paradigmDiv');
  var arrow = document.getElementById('inflArrow');
  if (div.style.display == 'block') {
    div.style.display = 'none';
    arrow.innerHTML = '&#x25b7;';
  } else {
    div.style.display = 'block';
    arrow.innerHTML = '&#x25bd;';
  }
  return false;
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
    if (!startsWith(obj[prop].toString(), 'function ')) {
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
  if (document.body && document.body.createTextRange) {
    /* IE */
    var range = document.body.createTextRange();
    range.moveToPoint(evt.clientX, evt.clientY);
    range.expand('word');
    return range.text;
  } else if (evt.rangeParent && document.createRange) {
    /* Mozilla */
    var range = document.createRange();
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
  var letter = '[' + Alphabet + ']';
  var nonLetter = '[^' + Alphabet + ']';
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

function searchClickedWord(evt) {
  var st = getSelectedText();
  if (st && st != '') {
    // Allow text selection without redirecting.
    return false;
  }
  var word = getWordFromEvent(evt);
  if (word != null && word != '') {
    source = document.getElementById('sourceDropDown').value;    
    loc = 'search.php?cuv=' + word;
    if (source) {
      loc += '&source=' + source;
    }
    window.location = loc;
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
