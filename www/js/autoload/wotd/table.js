$(function (){

  var grid;
  var gridUrl = wwwRoot + 'wotd-ajax';
  var imageList; // a select2 for WotD images

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
        validator: remoteValidator,
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

    imageList = $('#imageList').detach().removeAttr('id');
  }

  function htmlTooltip(cell) {
    // for some reason, this also gets called by the ColumnComponent
    var e = cell.getElement();
    if (e.classList.contains('tabulator-cell')) {
      return $(e).text();
    }
  }

  /**
   * Select2 editor. Jump through some hoops to prevent (1) calling both
   * success() and cancel and (2) reopening the select on clear.
   */
  function defEditor(cell, onRendered, success, cancel, editorParams) {
    var done = false;
    var plainText = $(cell.getElement()).text();
    var opt = new Option(plainText, plainText, true, true);
    var editor = document.createElement('select');
    editor.appendChild(opt);

    onRendered(function() {
      $(editor).select2({
        ajax: {
          url: wwwRoot + 'ajax/getDefinitions.php',
        },
        allowClear: true,
        minimumInputLength: 2,
        placeholder: 'caută o definiție...',
        templateResult: formatDefinition,
        templateSelection: formatDefinition,
        width: '100%',
      }).on('change', function(e) {
        // propagate change to related fields
        var d = $(editor).select2('data')[0];
        if (typeof d !== 'undefined') {
          successFunc(d.id, d.html, d.lexicon, d.source);
        }
      }).on('select2:clear', function() {
        successFunc(0, '', '', '');
      }).on('select2:close', function() {
        cancelFunc();
      }).on('select2:opening', function(e) {
        if (done) {
          e.preventDefault();
        }
      }).select2('open');
    });

    function successFunc(definitionId, html, lexicon, shortName) {
      if (!done) {
        done = true;
        cell.getRow().getCell('definitionId').setValue(definitionId);
        cell.getRow().getCell('lexicon').setValue(lexicon);
        cell.getRow().getCell('shortName').setValue(shortName);
        success(html);
      }
    }

    function cancelFunc() {
      if (!done) {
        done = true;
        cancel();
      }
    }

    return editor;
  }

  /**
   * Same as defEditor, but with local data.
   */
  function imageEditor(cell, onRendered, success, cancel, editorParams) {
    var done = false;
    var editor = imageList.clone()[0];
    editor.value = cell.getValue();

    onRendered(function() {
      $(editor).select2({
        allowClear: true,
        minimumInputLength: 1,
        placeholder: 'caută o imagine...',
        width: '100%',
      }).on('change', function() {
        var d = $(editor).select2('data')[0];
        successFunc(d.text);
      }).on('select2:clear', function() {
        successFunc('');
      }).on('select2:close', function() {
        cancelFunc();
      }).on('select2:opening', function(e) {
        if (done) {
          e.preventDefault();
        }
      }).select2('open');
    });

    function successFunc(val) {
      if (!done) {
        done = true;
        success(val);
      }
    }

    function cancelFunc() {
      if (!done) {
        done = true;
        cancel();
      }
    }

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

  /**
   * Tries to actually save the new cell value. The server will respond with
   * an error message if validation fails.
   */
  async function remoteValidator(cell, value, parameters) {
    // these are display-only fields; don't call the server when they change
    var displayFields = [ 'defHtml', 'descHtml', 'lexicon', 'shortName' ];

    var field = cell.getField();
    if (displayFields.includes(field)) {
      return true;
    }

    // TODO: Does Tabulator support asynchronous validators?
    var errorMsg = null;
    await new Promise(function(resolve, reject) {
      $.ajax({
        url: gridUrl,
        data: {
          action: 'save',
          field: field,
          value: value,
          wotdId: cell.getRow().getIndex(),
        },
      }).done(function(resp) {
        if (resp) {
          reject(new Error(resp));
        } else {
          resolve(null);
        }
      }).fail(function(resp) {
        console.log(resp);
        reject(new Error('Eroare la comunicarea cu serverul.'));
      });
    }).then(
      function() {},
      function(error) {
        errorMsg = error.message;
      }
    );

    if (errorMsg) {
      alert(errorMsg);
      return false;
    } else {
      return true;
    }
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
