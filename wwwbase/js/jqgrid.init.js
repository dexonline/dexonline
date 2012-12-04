function beginEdit(id) {
  $('#displayDate').datepicker({ dateFormat: 'yy-mm-dd' });
  $('#displayDate')[0].style.width = '400px';

  if ($('#lexicon').val()) {
    $('#lexicon').attr('readonly', true);
  } else {
    $('#lexicon').attr('readonly', false);
    $('#lexicon').autocomplete({
      source: wwwRoot + 'ajax/wotdGetDefinitions.php',
      select: function(event, ui) {
        var matches = ui.item.value.match(/^\[([^\]]+)\].+\[([0-9]+)\]$/);
        $('#definitionId').val(matches[2]);
        $('#lexicon').val(matches[1]);
        return false;
      },
    });
  }
  $('#lexicon')[0].style.width = '400px';
  $('#priority')[0].style.width = '400px';
  $('#description')[0].style.width = '400px';
  $('#image').autocomplete({ source: wwwRoot + 'ajax/wotdGetImages.php' });
}

function endEdit(data) {
  data.definitionId = $('#definitionId').val();
  return [true];
}

function checkServerResponse(response, postData) {
  if (response.responseText) {
    return [false, response.responseText];
  } else {
    return [true];
  }
}

jQuery().ready(function (){
  var editOptions = {
    reloadAfterSubmit: true,
    closeAfterEdit: true,
    closeOnEscape: true,
    beforeSubmit: endEdit,
    afterShowForm: beginEdit,
    afterSubmit: checkServerResponse,
    width: 500,
  };

  var addOptions = {
    reloadAfterSubmit: true,
    closeAfterAdd: true,
    closeOnEscape: true,
    beforeSubmit: endEdit,
    afterShowForm: beginEdit,
    afterSubmit: checkServerResponse,
    width: 500,
  };

  var deleteOptions = {
    afterSubmit: checkServerResponse,
    closeOnEscape: true,
    reloadAfterSubmit: true,
  };

  $('#wotdGrid').jqGrid({
    url: wwwRoot + 'ajax/wotdTableRows.php',
    datatype: 'xml',
    colNames: ['Cuvînt', 'Sursă', 'Definiție', 'Data afișării', 'Adăugată de', 'Pr.', 'Tipul resursei', 'Imagine', 'Descriere', 'ID-ul definiției'],
    colModel: [
      {name: 'lexicon', index: 'lexicon', editable: true},
      {name: 'source', index: 'shortName', width: 60},
      {name: 'htmlRep', index: 'htmlRep', width: 450},
      {name: 'displayDate', index: 'displayDate', width: 90, editable: true},
      {name: 'name', index: 'u.name', width: 90},
      {name: 'priority', index: 'priority', editable: true, width: 40},
      {name: 'refType', index: 'refType', editable: true, edittype: 'select', editoptions: {value: 'Definition:Definition'}, hidden: true},
      {name: 'image', index: 'w.image', editable: true, width: 75},
      {name: 'description', index: 'description', editable: true, edittype: 'textarea', hidden: false, width: 250},
      {name: 'definitionId', index: 'definitionId', editable: true, hidden: true}
    ],
    rowNum: 20,
    autoWidth: true,
    height: '100%',
    rowList: [20, 50, 100, 200],
    sortname: 'displayDate',
    pager: $('#wotdPaging'),
    viewrecords: true,
    sortorder: 'desc',
    caption: 'Cuvântul zilei',
    editurl: wwwRoot + 'ajax/wotdSave.php',
    ondblClickRow: function(rowid) { $(this).jqGrid('editGridRow', rowid, editOptions); }
  });
  $('#wotdGrid').navGrid('#wotdPaging',
    {
      search: false,
      addtext: 'adaugă',
      deltext: 'șterge',
      edittext: 'editează',
      refreshtext: 'reîncarcă',
    }, editOptions, addOptions, deleteOptions
  );
  $('#wotdGrid').filterToolbar({
    stringResult: true,
  });
});
