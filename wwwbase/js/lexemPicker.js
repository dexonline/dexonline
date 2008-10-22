function addLexemRow(tableId) {
  var tbl = document.getElementById(tableId);
  var tbody = findChildWithNodeName(tbl, 'TBODY');
  var prototypeRow = tbl.rows[0];
  var clone = prototypeRow.cloneNode(true);
  clone.id = '';
  clone.style.display = 'table-row';
  tbody.appendChild(clone);
  var cellLeft = findChildWithNodeName(clone, 'TD');
  var cellRight = findNextSiblingWithNodeName(cellLeft, 'TD');
  var input = findChildWithNodeName(cellRight, 'INPUT');
  input.focus();
}

function deleteLexemRow(anchor) {
  var row = anchor.parentNode.parentNode;
  var tbody = row.parentNode;
  tbody.removeChild(row);
  return false;
}

function lexemPickerKeyEvent(input, e) {
  if (!e) {
    var e = window.event;
  }
  code = e.keyCode;

  if (code == 38) { // up arrow
    scrollLexem(input, -1);
    return false;
  } else if (code == 40) { // down arrow
    scrollLexem(input, 1);
    return false;
  } else if (code == 27) { // escape
    hideLexemTable(input);
  } else if (code == 9) { // tab
  } else if (code == 13) { // enter
    if (isSelectionBarVisible(input)) {
      selectHighlightedLexem(input);
      hideLexemTable(input);
      return false; // prevent form submission
    } else {
      return true; // allow form submission
    }
  } else {
    // Perform the search with a slight delay, to make sure the new symbol
    // has been added to the search string.
    setTimeout( function(){ requestLexemSuggestions(input); }, 10);
  }

  return true;
}

function lexemPickerBlur(input) {
  hideLexemTable(input);
  return true;
}

function lexemPickerChange(input) {
  // hide the "edit" link, since the lexem was changed.
  var link = findNextSiblingWithNodeName(input, 'A');
  if (link && link.innerHTML == 'editează') {
    link.style.display = 'none';
  }
}

function scrollLexem(input, step) {
  var table = findNextSiblingWithNodeName(input, 'TABLE');
  var numRows = table.rows.length;
  if (table.style.display != 'table') {
    return; // no rows to scroll
  }

  if (typeof table.selectedLexem != 'undefined') {
    table.rows[table.selectedLexem].className = '';
  }

  if (typeof table.selectedLexem == 'undefined') {
    table.selectedLexem = (step == 1) ? 0 : (numRows - 1);
  } else {
    table.selectedLexem = (table.selectedLexem + step + numRows) % numRows;
  }

  table.rows[table.selectedLexem].className = 'selected';
}

function requestLexemSuggestions(input) {
  makeGetRequest('../ajax/getLexems.php?query=' + input.value,
                 showLexemTable, input);
}

function showLexemTable(httpRequest, input) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      result = httpRequest.responseText;

      var table = findNextSiblingWithNodeName(input, 'TABLE');
      while (table.rows.length) {
        table.deleteRow(0);
      }
      delete table.selectedLexem;

      var splitResults = result.split('\n');

      // Compare to 1, not to 0, because there is an extra blank line at the
      // end of the file.
      if (splitResults.length > 1) {
        table.style.display = 'table';
        for (var i = 0; i < splitResults.length && splitResults[i]; i+= 2) {
          var row = table.insertRow(-1);
          // Cell holding ID
          var cell = row.insertCell(-1);
          cell.innerHTML = splitResults[i];
          cell.style.display = 'none';
          // Cell holding extended name
          cell = row.insertCell(-1);
          cell.innerHTML = splitResults[i + 1];
          row.onmousedown = selectClickedLexem;
        }
      } else {
        hideLexemTable(input);
      }
    } else {
      alert('Nu pot descărca lista de sugestii pentru lexeme!');
    }
  }
}

function isSelectionBarVisible(input) {
  var table = findNextSiblingWithNodeName(input, 'TABLE');
  return typeof table.selectedLexem != 'undefined';
}

function hideLexemTable(input) {
  var table = findNextSiblingWithNodeName(input, 'TABLE');
  table.style.display = 'none';
  delete table.selectedLexem;
}

function selectClickedLexem() {
  var table = this.parentNode.parentNode;
  var index = this.rowIndex;
  var hiddenInput = findPreviousSiblingWithNodeName(table, 'INPUT');
  var input = findPreviousSiblingWithNodeName(hiddenInput, 'INPUT');
  hiddenInput.value = table.rows[index].cells[0].innerHTML;
  input.value = table.rows[index].cells[1].innerHTML;
  setTimeout( function(){ input.focus(); }, 10);
  return true;
}

function selectHighlightedLexem(input) {
  var table = findNextSiblingWithNodeName(input, 'TABLE');
  if (typeof table.selectedLexem == 'undefined') {
    return; // there is no highlighted suggestion
  }
  var index = table.selectedLexem;
  var hiddenInput = findNextSiblingWithNodeName(input, 'INPUT');
  input.value = table.rows[index].cells[1].innerHTML;
  hiddenInput.value = table.rows[index].cells[0].innerHTML;
}
