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
    $('#tr_description').show();

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
  var lexWidth    = 100;
  var sourceWidth =  60;
  var htmlWidth   = 350;
  var dateWidth   =  90;
  var userWidth   =  90;
  var priorWidth  =  40;
  var imageWidth  = 130;
  var descWidth   = screenWidth - (lexWidth + sourceWidth + htmlWidth + dateWidth + userWidth + priorWidth + imageWidth) - 40;
  var imageList = $('#imageList').detach().removeAttr('id');

  $('#wotdGrid').jqGrid({
    url: wwwRoot + 'ajax/getWotds.php',
    datatype: 'json',
    colNames: [
      'Cuvânt',
      'Sursă',
      'Definiție',
      'Data afișării',
      'Adăugată de',
      'Pr.',
      'Imagine',
      'Descriere',
      'ID-ul definiției',
      'Descriere (internă)',
    ],
    colModel: [
      {name: 'lexicon', index: 'd.lexicon', editable: true, edittype: 'select', editoptions: {value: 'x:x'}, width: lexWidth},
      {name: 'shortName', index: 's.shortName', width: sourceWidth},
      {name: 'defHtml', index: 'd.internalRep', width: htmlWidth},
      {name: 'displayDate', index: 'w.displayDate', width: dateWidth, editable: true, cellattr: function (a,b,c,d,x) {return ' title="' + x.descr + '"'}},
      {name: 'name', index: 'u.name', width: userWidth},
      {name: 'priority', index: 'w.priority', editable: true, width: priorWidth},
      {name: 'image', index: 'w.image', editable: true, edittype: 'select', editoptions: {value: ':'}, width: imageWidth},
      {name: 'wotdHtml', index: 'w.description', width: descWidth},
      {name: 'description', index:'w.description', editable: true, edittype: 'textarea', hidden: true},
      {name: 'definitionId', index: 'definitionId', editable: true, hidden: true},
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
