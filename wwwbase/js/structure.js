$(function() {
  $('#meaningsTable').jqGrid({
    caption: 'Sensuri',
    colModel: [
      {name: 'hierarchy', index: 'hierarchy', width: 20, sortable: false},
      {name: 'html', index: 'html', width: 550, sortable: false},
      {name: 'text', index: 'text', hidden: true},
    ],
        colNames: ['nr', 'sens', 'text'],
    datatype: 'xml',
    height: 400,
    hidegrid: false,
    url: wwwRoot + 'ajax/structureLoad.php?id=' + defId,
  });

  $('#moveLink').click(moveLinkClick);
  $('#moveAllLink').click(moveAllLinkClick);
  $('.unindent').click(indentClick);
  $('.indent').click(indentClick);
  $('#lexemTabs').tabs();
  $(".existingMeanings").find('tbody').sortable();
});

function moveLinkClick() {
  var grid = $('#meaningsTable');
  var rowId = grid.getGridParam('selrow');
  if (rowId == null) {
    alert('Trebuie să selectați o linie din tabel.');
  } else {
    addRow(grid.getCell(rowId, 'text'), grid.getCell(rowId, 'hierarchy'));
    var nextRowId = getNextRowId(grid, rowId);
    grid.delRowData(rowId);
    if (nextRowId) {
      grid.setSelection(nextRowId);
    }
  }
  return false;
}

function moveAllLinkClick() {
  var grid = $('#meaningsTable');
  var rowIds = grid.getDataIDs();
  for (var i = 0; i < rowIds.length; i++) {
    var rowId = rowIds[i];
    addRow(grid.getCell(rowId, 'text'), grid.getCell(rowId, 'hierarchy'));
    grid.delRowData(rowId);
  }
  return false;
}

function indentClick() {
  var step = 10;
  var td = $(this).parent('.actions').siblings('td:last');
  var padding = parseInt(td.css('padding-left').replace('px', ''));
  var cl = $(this).attr('class');
  if (cl == 'indent' && padding <= 10 * step) {
    padding += step;
  } else if (cl == 'unindent' && padding >= step) {
    padding -= step;
  }
  td.css('padding-left', padding);
  return false;
}

function getNextRowId(grid, rowId) {
  var rowIds = grid.getDataIDs();
  var index = grid.getInd(rowId) - 1; // getInd is 1-based

  if (rowIds.length == 1) {
    return false;
  } else if (index == rowIds.length - 1) {
    return rowIds[index - 1];
  } else {
    return rowIds[index + 1];
  }
}

function addRow(text, hierarchy) {
  var activeTabIndex = $('#lexemTabs').tabs('option', 'active');
  var activeTable = $('#existingMeanings' + activeTabIndex);
  var row = activeTable.find('tr:first').clone(true);
  row.find('.hierarchy').html(hierarchy);
  var textarea = row.find('.text');
  textarea.val(text);
  setTimeout(function() {
    textarea.height(textarea[0].scrollHeight);
  }, 100);
  row.insertAfter(activeTable.find('tr:last'));
  row.css('display', 'table-row');
  row.get(0).scrollIntoView();
}
