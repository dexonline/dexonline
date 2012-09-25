function beginEdit(id){
  $('#displayDate').datepicker({ dateFormat: 'yy-mm-dd' });
  $('#displayDate')[0].style.width = '200px';

  if ($('#lexicon').val()) {
    $('#lexicon').attr('readonly', true);
  } else {
    $('#lexicon').attr('readonly', false);
    $('#lexicon').autocomplete("wotdGetDefinitions.php").result(function(event, item){
      var matches = item[0].match(/^\[([^\]]+)\].+\[([0-9]+)\]$/);
      $('#definitionId').val(matches[2]);
      $('#lexicon').val(matches[1]);
    });
  }
  $('#lexicon')[0].style.width = '200px';
  $('#priority')[0].style.width = '200px';
  $('#description')[0].style.width = '200px';
  $('#image').autocomplete('wotdGetImages.php');
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

function initGrid(){
  jQuery().ready(function (){
    $('#wotdGrid').jqGrid({
      url: 'wotdTableRows.php',
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
      viewRecords: true,
      sortOrder: 'desc',
      caption: 'Cuvântul zilei',
      editurl: 'wotdSave.php',
    });
    $('#wotdGrid').navGrid('#wotdPaging',
      {
        search: false,
        addtext: 'adaugă',
        deltext: 'șterge',
        edittext: 'editează',
        refreshtext: 'reîncarcă',
      },
      {
        // Settings for edit
        reloadAfterSubmit: true,
        closeAfterEdit: true,
        closeOnEscape: true,
        beforeSubmit: endEdit,
        afterShowForm: beginEdit,
        afterSubmit: checkServerResponse, 
      },
      {
        // Settings for add
        reloadAfterSubmit: true,
        closeAfterAdd: true,
        closeOnEscape: true,
        beforeSubmit: endEdit,
        afterShowForm: beginEdit,
        afterSubmit: checkServerResponse,
      },
      {
        // Setings for delete
        afterSubmit: checkServerResponse,
      }
    );
    $('#wotdGrid').filterToolbar({
      stringResult: true,
    });
  });
}
