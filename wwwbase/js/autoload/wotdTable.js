$(function (){

  function formInit(id) {
    $('#image').html('').append(imageList.find('option').clone());
  }

  function beginEdit(id, op) {
    var rowId = $('#wotdGrid').jqGrid('getGridParam', 'selrow');

    $('#displayDate').datepicker({ dateFormat: 'yy-mm-dd' });
    $('#displayDate')[0].style.width = '400px';

    $('#lexicon').html('');
    if (op == 'edit') {
      var lexicon = $('#wotdGrid').getCell(rowId, 'lexicon');
      $('#lexicon').append(new Option(lexicon, lexicon, true, true));
    }
    $('#lexicon').select2({
      ajax: {
        url: wwwRoot + 'ajax/wotdGetDefinitions.php',
      },
      templateResult: function(item) {
        return item.text + ' (' + item.source + ') [' + item.id + ']';
      },
      minimumInputLength: 1,
      placeholder: 'caută un cuvânt...',
      width: '410px',
    }).on('change', function(e) {
      var data = $('#lexicon').select2('data')[0];
      $('#definitionId').val(data.id);
      $('#lexicon').val(data.lexicon);
    });
    $('#lexicon').prop('disabled', op == 'edit');

    $('#priority')[0].style.width = '400px';
    $('#description')[0].style.width = '400px';

    // This needs to be selected explicitly sometimes -- don't know why
    var value = $('#wotdGrid').getCell(rowId, 'image');
    $('#image option[value="' + value + '"]').prop('selected', true);

    $('#image').select2({
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o imagine...',
      width: '410px',
    });
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

  var editOptions = {
    reloadAfterSubmit: true,
    closeAfterEdit: true,
    closeOnEscape: true,
    beforeSubmit: endEdit,
    afterShowForm: beginEdit,
    afterSubmit: checkServerResponse,
    onInitializeForm: formInit,
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

  var screenWidth = $(window).width();
  var lexWidth    = 120;
  var sourceWidth =  60;
  var htmlWidth   = 450;
  var dateWidth   =  90;
  var userWidth   =  90;
  var priorWidth  =  50;
  var imageWidth  = 130;
  var descWidth   = screenWidth - (lexWidth + sourceWidth + htmlWidth + dateWidth + userWidth + priorWidth + imageWidth) - 40;
  var imageList = $('#imageList').detach().removeAttr('id');

  $('#wotdGrid').jqGrid({
    url: wwwRoot + 'ajax/wotdTableRows.php',
    datatype: 'xml',
    colNames: ['Cuvînt', 'Sursă', 'Definiție', 'Data afișării', 'Adăugată de', 'Pr.', 'Tipul resursei', 'Imagine', 'Descriere', 'ID-ul definiției'],
    colModel: [
      {name: 'lexicon', index: 'lexicon', editable: true, edittype: 'select', editoptions: {value: 'x:x'}, width: lexWidth},
      {name: 'source', index: 'shortName', width: sourceWidth},
      {name: 'htmlRep', index: 'htmlRep', width: htmlWidth},
      {name: 'displayDate', index: 'displayDate', width: dateWidth, editable: true},
      {name: 'name', index: 'u.name', width: userWidth},
      {name: 'priority', index: 'priority', editable: true, width: priorWidth},
      {name: 'refType', index: 'refType', editable: true, edittype: 'select', editoptions: {value: 'Definition:Definition'}, hidden: true},
      {name: 'image', index: 'w.image', editable: true, edittype: 'select', editoptions: {value: 'x:x'}, width: imageWidth},
      {name: 'description', index: 'description', editable: true, edittype: 'textarea', hidden: false, width: descWidth},
      {name: 'definitionId', index: 'definitionId', editable: true, hidden: true}
    ],
    rowNum: 50,
    recreateForm: true,
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
