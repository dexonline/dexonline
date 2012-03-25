function beginEdit(id){
  $('#displayDate').datepicker({ dateFormat: 'yy-mm-dd' });
  if($('#lexicon').val()){
      $('#lexicon').attr('readonly', true);
  }
  else {
      $('#lexicon').attr('readonly', false);
      $('#lexicon').autocomplete("wotdGetDefinitions.php").result(function(event, item){
        var lexicon = item[0].replace(/^([^ | ^\-]*) -.*$/, '$1');
        var definitionId = item[0].replace(/^[^\[|\[^\{]*\[\{([0-9]+)\}\]$/, '$1');
        $('#definitionId').val(definitionId);
        $('#lexicon').val(lexicon);
      });
  }
  $('#priority')[0].style.width = '400px';
  $('#displayDate')[0].style.width = '400px';
  $('#lexicon')[0].style.width = '400px';
  if ($('#definitionId').length == 0){
    $('#lexicon').after('<input type="hidden" id="definitionId"/>');
  }
  $('#image').autocomplete('wotdGetImages.php');
}

function beforeSubmit(data){
  data.definitionId = $('#definitionId').val();
  var sel_id = $("#wotdGrid").jqGrid('getGridParam','selrow');
  var rowData = $('#wotdGrid').jqGrid('getRowData', sel_id);
  data.oldDefinitionId = rowData.definitionId;
  return [true];
}

function initGrid(){
  jQuery().ready(function (){
    $('#wotdGrid').jqGrid({
      url: 'wotdTableRows.php',
      datatype: 'xml',
      colNames: ['Lexicon', 'Sursa', 'Definiție', 'Data afișării', 'Adăugată la WotD de', 'Prioritate', 'Tipul resursei', 'Imagine', 'ID-ul definiției'],
      colModel: [
        {name: 'lexicon', index: 'lexicon', editable: true},
        {name: 'source', index: 'shortName', width: 75},
        {name: 'htmlRep', index: 'htmlRep', width: 650},
        {name: 'displayDate', index: 'displayDate', width: 100, editable: true},
        {name: 'name', index: 'u.name', width: 75},
        {name: 'priority', index: 'priority', editable: true, width: 75},
        {name: 'refType', index: 'refType', editable: true, edittype: 'select', editoptions: {value: 'Definition:Definition'}, hidden: true},
        {name: 'image', index: 'w.image', editable: true, width: 75},
        {name: 'definitionId', index: 'definitionId', editable: false, hidden: true}
      ],
      rowNum: 25,
      autoWidth: true,
      height: '100%',
      rowList: [25, 50, 100, 200],
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
        beforeSubmit: function(data){
          return beforeSubmit(data);
        },
        afterShowForm: function(id){
          beginEdit(id);
        },
      },
      {
        // Settings for add
        reloadAfterSubmit: true,
        closeAfterAdd: true,
        closeOnEscape: true,
        beforeSubmit: function(data){
          return beforeSubmit(data);
        },
        afterShowForm: function(id){
          beginEdit(id);
        },
      }
    );
    $('#wotdGrid').filterToolbar({
      stringResult: true,
    });
  });
}
