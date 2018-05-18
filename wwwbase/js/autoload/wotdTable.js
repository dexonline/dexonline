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
        url: wwwRoot + 'ajax/getDefinitions.php',
      },
      templateResult: formatDefinition,
      templateSelection: formatDefinition,
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

  // by default free jqGrid displays HTML as raw text; this seems to do the trick
  function htmlFormatter(cellValue, options, rowObject) {
    return cellValue;
  }

  var editOptions = {
    afterShowForm: beginEdit,
    afterSubmit: checkServerResponse,
    beforeSubmit: endEdit,
    closeAfterEdit: true,
    closeOnEscape: true,
    onInitializeForm: formInit,
    reloadAfterSubmit: true,
    width: 500,
  };

  var addOptions = {
    afterShowForm: beginEdit,
    afterSubmit: checkServerResponse,
    beforeSubmit: endEdit,
    closeAfterAdd: true,
    closeOnEscape: true,
    onInitializeForm: formInit,
    reloadAfterSubmit: true,
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
  var descWidth   = screenWidth - (lexWidth + sourceWidth + htmlWidth + dateWidth +
                                   userWidth + priorWidth + imageWidth) - 40;
  var imageList = $('#imageList').detach().removeAttr('id');

  // remove the "clear search" button and left align the search cursor for all columns
  jQuery.extend(jQuery.jgrid.defaults, {
    cmTemplate: {
      searchoptions: {
        attr: { style: "text-align: left" },
        clearSearch: false,
      }
    }
  });

  var colModels = [
    {
      label: 'Cuvânt',
      name: 'lexicon',
      index: 'd.lexicon',
      editable: true,
      edittype: 'select',
      editoptions: {value: 'x:x'},
      width: lexWidth,
    },
    {
      label: 'Sursă',
      name: 'shortName',
      index: 's.shortName',
      width: sourceWidth,
    },
    {
      label: 'Definiție',
      name: 'defHtml',
      index: 'd.internalRep',
      formatter: htmlFormatter,
      width: htmlWidth,
    },
    {
      label: 'Data afișării',
      name: 'displayDate',
      index: 'w.displayDate',
      width: dateWidth,
      editable: true,
    },
    {
      label: 'Adăugată de',
      name: 'name',
      index: 'u.name',
      width: userWidth,
    },
    {
      label: 'Pr.',
      name: 'priority',
      index: 'w.priority',
      editable: true,
      width: priorWidth,
    },
    {
      label: 'Imagine',
      name: 'image',
      index: 'w.image',
      editable: true,
      edittype: 'select',
      editoptions: {value: ':'},
      width: imageWidth,
    },
    { // HTML, visible as column header
      label: 'Motiv',
      name: 'wotdHtml',
      index: 'w.description',
      formatter: htmlFormatter,
      width: descWidth,
    },
    {
      name: 'definitionId',
      index: 'definitionId',
      editable: true,
      hidden: true,
    },
    { // internal, visible as label in the edit form
      name: 'description',
      index:'w.description',
      editable: true,
      edittype: 'textarea',
      editrules: { edithidden: true },
      hidden: true,
    },
  ];

  $('#wotdGrid').jqGrid({
    colModel: colModels,
    datatype: 'json',
    editurl: wwwRoot + 'ajax/saveWotd.php',
    height: '100%',
    ondblClickRow: function(rowid) { $(this).jqGrid('editGridRow', rowid, editOptions); },
    pager: $('#wotdPaging'),
    recreateForm: true,
    rowList: [20, 50, 100, 200],
    rowNum: 50,
    sortname: 'w.displayDate',
    sortorder: 'desc',
    url: wwwRoot + 'ajax/getWotds.php',
    viewrecords: true,
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
