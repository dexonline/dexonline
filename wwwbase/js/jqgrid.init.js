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
      colNames: ['Source name', 'Lexicon', 'Defition HTML', 'User\'s name', 'Display data', 'Priority', 'WotD type', 'Definition ID'],
      colModel: [
        {name: 'source', index: 'source', width: 100},
        {name: 'lexicon', index: 'lexicon', editable: true},
        {name: 'htmlRep', index: 'htmlRep', width: 600},
        {name: 'name', index: 'u.name'},
        {name: 'displayDate', index: 'displayDate', width: 100, editable: true},
        {name: 'priority', index: 'priority', editable: true, width: 80},
        {name: 'refType', index: 'refType', width: 100, editable: true, edittype: 'select', editoptions: {value: 'Definition:Definition'}}, 
        {name: 'definitionId', index: 'definitionId', editable: false, hidden: true}
      ],
      rowNum: 30,
      autoWidth: true,
      height: '100%',
      rowList: [30, 60, 100, 200],
      sortname: 'displayDate',
      pager: $('#wotdPaging'),
      viewRecords: true,
      sortOrder: 'desc',
      caption: 'Word of the Day',
      editurl: 'wotdSave.php'
    });
    $('#wotdGrid').navGrid('#wotdPaging', {},
      {
        reloadAfterSubmit: true,
        beforeSubmit: function(data){
          return beforeSubmit(data);
        },
        afterShowForm: function(id){
          beginEdit(id);
        }
      },
      {
        reloadAfterSubmit: true,
        beforeSubmit: function(data){
          return beforeSubmit(data);
        },
        afterShowForm: function(id){
          beginEdit(id);
        }
      }
    );
  });
}
