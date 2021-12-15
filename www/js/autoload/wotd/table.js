$(function (){

  var grid;
  var gridUrl = wwwRoot + 'wotd-ajax';
  var imageList; // a select2 for WotD images

  function formInit(id) {
    $('#image').html('').append(imageList.find('option').clone());
  }

  function doubleClickRow(rowId) {
    $(this).jqGrid('setSelection', rowId);
    $(this).jqGrid('editGridRow', rowId, editOptions);
  }

  function beginEdit(id, op) {
    var rowId = $('#wotdGrid').jqGrid('getGridParam', 'selrow');

    $('#displayDate').datepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      keyboardNavigation: false,
      language: 'ro',
      todayBtn: 'linked',
      todayHighlight: true,
      weekStart: 1,
    });

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
    $('#lexicon').prop('disabled', $('#lexicon').val());

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

  function init() {
    // I couldn't make Tabulator's table layout play nice.
    var contW = $('.container').width();
    var lexiconW    = 100;
    var sourceW     =  60;
    var dateW       =  90;
    var userW       =  90;
    var priW        =  40;
    var imageW      = 130;
    var actionW     =  60;
    var leftover    = contW - (lexiconW + sourceW + dateW + userW + priW + imageW + actionW + 40);

    grid = new Tabulator('#wotdGrid', {
      ajaxURL: gridUrl,
      ajaxParams: { action: 'load' },
      columns: [
        {
          title: 'id',
          field: 'id',
          visible: false,
        }, {
          title: 'definitionId',
          field: 'definitionId',
          visible: false,
        }, {
          title: 'cuvânt',
          cssClass: 'col-readonly',
          field: 'lexicon',
          width: lexiconW,
        }, {
          title: 'definiție',
          editor: defEditor,
          field: 'defHtml',
          formatter: 'html',
          headerSort: false,
          tooltip: htmlTooltip,
          width: leftover / 2,
        }, {
          title: 'sursă',
          cssClass: 'col-readonly',
          field: 'shortName',
          width: sourceW,
        }, {
          title: 'dată',
          editor: 'input',
          field: 'displayDate',
          width: dateW,
        }, {
          title: 'adăugată de',
          cssClass: 'col-readonly',
          field: 'userName',
          width: userW,
        }, {
          title: 'pr.',
          editor: 'number',
          field: 'priority',
          validator: [ 'required', 'min:0', 'max:10'],
          width: priW,
        }, {
          title: 'imagine',
          editor: imageEditor,
          field: 'image',
          width: imageW,
        }, {
          title: 'description',
          field: 'description',
          visible: false,
        }, {
          title: 'motiv',
          editor: descriptionEditor,
          field: 'descHtml',
          formatter: 'html',
          tooltip: htmlTooltip,
          width: leftover / 2,
        }, {
          title: 'acțiuni',
          cellClick: deleteRow,
          cssClass: 'col-clickable',
          formatter: printDeleteIcon,
          headerFilter: false,
          headerSort: false,
          hozAlign: 'center',
          width: actionW,
        },
      ],
      columnDefaults:{
        headerFilter: 'input',
      },
      filterMode: 'remote',
      headerSortElement: '<i class="material-icons">expand_less</i>',
      initialSort:[
        {column: 'displayDate', dir:'desc'},
      ],
      keybindings: false,
      langs: { /* for localized pagination */
        'ro-ro': {
          'data': {
            'loading': 'încarc...',
            'error': 'eroare',
          },
          'pagination': {
            'page_size': 'per pagină',
            'page_title': 'sari la pagina',
            'first': '<i class="material-icons">first_page</i>',
            'first_title': 'prima pagină',
            'last': '<i class="material-icons">last_page</i>',
            'last_title': 'ultima pagină',
            'prev': '<i class="material-icons">navigate_before</i>',
            'prev_title': 'pagina anterioară',
            'next': '<i class="material-icons">navigate_next</i>',
            'next_title': 'pagina următoare',
          },
        },
      },
      locale: 'ro-ro',
      pagination: true,
      paginationMode: 'remote',
      paginationSize: 50,
      paginationSizeSelector: [20, 50, 100, 200],
      sortMode: 'remote',
    });
    grid.on('cellEdited', cellEdited);

    imageList = $('#imageList').detach().removeAttr('id');
  }

  function htmlTooltip(cell) {
    // for some reason, this also gets called by the ColumnComponent
    var e = cell.getElement();
    if (e.classList.contains('tabulator-cell')) {
      return $(e).text();
    }
  }

  function defEditor(cell, onRendered, success, cancel, editorParams) {
    var editor = document.createElement('select');

    onRendered(function() {
      $(editor).select2({
        ajax: {
          url: wwwRoot + 'ajax/getDefinitions.php',
        },
        templateResult: formatDefinition,
        templateSelection: formatDefinition,
        minimumInputLength: 2,
        placeholder: 'caută un cuvânt...',
        width: '100%',
      }).on('change', function(e) {
        // propagate change to related fields
        var d = $(editor).select2('data')[0];
        cell.getRow().getCell('definitionId').setValue(d.id);
        cell.getRow().getCell('lexicon').setValue(d.lexicon);
        cell.getRow().getCell('shortName').setValue(d.source);
        success(d.html);
      }).on('select2:close', function() {
        cancel();
      }).select2('open');
    });

    editor.addEventListener('blur', function() {
      $(editor).select2('destroy');
      cancel();
    });

    return editor;
  }

  function imageEditor(cell, onRendered, success, cancel, editorParams) {
    var editor = imageList.clone()[0];
    editor.value = cell.getValue();

    onRendered(function() {
      $(editor).select2({
        allowClear: true,
        minimumInputLength: 1,
        placeholder: 'caută o imagine...',
        width: '100%',
      }).on('change', function(e) {
        var d = $(editor).select2('data')[0];
        successFunc(d.text);
      }).on('select2:clear', function() {
        successFunc('');
      }).select2('open');
    });

    function destroy() {
      $(editor).off('change select2:clear');
      $(editor).select2('destroy');
    }

    function successFunc(val) {
      console.log('success', val);
      destroy();
      success(val);
    }

    function cancelFunc() {
      console.log('cancel');
      destroy();
      cancel();
    }

    editor.addEventListener('blur', function() {
      console.log('blur');
      cancelFunc();
    });

    return editor;
  }

  // switch to the internal description when editing
  function descriptionEditor(cell, onRendered, success, cancel, editorParams) {
    var editor = document.createElement('input');
    editor.style.width = '100%';
    editor.value = cell.getRow().getCell('description').getValue();

    onRendered(function() {
      editor.focus();
    });

    editor.addEventListener('change', function() {
      cell.getRow().getCell('description').setValue(editor.value);
      success(editor.value);
    });
    editor.addEventListener('blur', cancel);

    return editor;
  }

  function cellEdited(cell) {
    // these are display-only fields; don't call the server when they change
    var displayFields = [ 'defHtml', 'descHtml', 'lexicon', 'shortName' ];

    var field = cell.getField();
    if (displayFields.includes(field)) {
      return;
    }

    $.ajax({
      url: gridUrl,
      data: {
        action: 'save',
        field: field,
        value: cell.getValue(),
        wotdId: cell.getRow().getIndex(),
      },
    }).fail(function() {
      cell.restoreOldValue();
      alert('Nu am putut salva modificarea (eroare pe server).');
    });
  }

  function printDeleteIcon() {
    return '<i class="material-icons">delete</i>';
  }

  function deleteRow(e, cell) {
    var row = cell.getRow();
    var msg = sprintf('Confirmi ștergerea înregistrării [%s] pentru data [%s]?',
                      row.getCell('lexicon').getValue(),
                      row.getCell('displayDate').getValue());
    if (!confirm(msg)) {
      return;
    }

    $.ajax({
      url: gridUrl,
      data: { action: 'delete', wotdId: row.getIndex() }
    }).done(function(resp) {
      cell.getRow().delete();
    }).fail(function() {
      alert('Nu am putut șterge înregistrarea (eroare pe server).');
    });
  }

  init();
});
