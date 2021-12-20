$(function (){

  // I couldn't make Tabulator's table layout play nice.
  const W_CONT = $('.container').width();
  const W_LEXICON    = 100;
  const W_SOURCE     =  60;
  const W_DATE       =  90;
  const W_USER       =  90;
  const W_PRIORITY   =  40;
  const W_IMAGE      = 130;
  const W_TOTAL      = W_LEXICON + W_SOURCE + W_DATE + W_USER + W_PRIORITY + W_IMAGE;
  const W_LEFTOVER   = W_CONT - W_TOTAL - 40;

  const GRID_COLUMNS = [
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
      field: 'lexicon',
      width: W_LEXICON,
    }, {
      title: 'definiție',
      field: 'defHtml',
      formatter: 'html',
      headerSort: false,
      tooltip: htmlTooltip,
      width: W_LEFTOVER / 2,
    }, {
      title: 'sursă',
      field: 'shortName',
      width: W_SOURCE,
    }, {
      title: 'dată',
      field: 'displayDate',
      width: W_DATE,
    }, {
      title: 'adăugată de',
      field: 'userName',
      width: W_USER,
    }, {
      title: 'pr.',
      field: 'priority',
      width: W_PRIORITY,
    }, {
      title: 'imagine',
      field: 'image',
      width: W_IMAGE,
    }, {
      title: 'description',
      field: 'description',
      visible: false,
    }, {
      title: 'motiv',
      field: 'descHtml',
      formatter: 'html',
      tooltip: htmlTooltip,
      width: W_LEFTOVER / 2,
    },
  ];

  const GRID_URL = wwwRoot + 'wotd-ajax';

  const TABULATOR_TRANSLATIONS = {
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
  };

  var editingRow; // row currently being edited in the modal
  var grid;
  var modal;

  function init() {
    initGrid();

    var modalEl = document.getElementById('edit-modal');
    modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalEl.addEventListener('hidden.bs.modal', modalHidden)

    $('#add-btn').click(beginEdit);
    $('#delete-btn').click(deleteRow);
    $('#save-btn').click(saveRow);
  }

  function initGrid() {
    grid = new Tabulator('#wotd-grid', {
      addRowPos: 'top',
      ajaxURL: GRID_URL,
      ajaxParams: { action: 'load' },
      columns: GRID_COLUMNS,
      columnDefaults:{
        headerFilter: 'input',
      },
      filterMode: 'remote',
      headerSortElement: '<i class="material-icons">expand_less</i>',
      initialSort:[
        {column: 'displayDate', dir:'desc'},
      ],
      keybindings: false,
      langs: TABULATOR_TRANSLATIONS,
      locale: 'ro-ro',
      pagination: true,
      paginationMode: 'remote',
      paginationSize: 50,
      paginationSizeSelector: [20, 50, 100, 200],
      sortMode: 'remote',
    });

    // move footer before table rows; move add button to footer
    grid.on('tableBuilt', function() {
      $('.tabulator-footer').prependTo($('#wotd-grid'));
      $('#add-btn').prependTo($('.tabulator-footer'));
    });

    grid.on('rowClick', beginEdit);
  }

  function htmlTooltip(cell) {
    // for some reason, this also gets called by the ColumnComponent
    return $(cell.getElement()).text();
  }

  // called for editing OR adding a row (in which case row is null)
  function beginEdit(e, row = null) {
    editingRow = row;

    if (row) {
      // prepare the definitionId
      var option = sprintf(
        '<option value="%d" selected>%s</option>',
        row.getCell('definitionId').getValue(),
        row.getCell('defHtml').getValue());
      $('#edit-definitionId').html(option);

      // prepare the displayDate
      var dd = shortenDisplayDate(row.getCell('displayDate').getValue());
      $('#edit-displayDate').val(dd);

      // prepare other fields
      $('#edit-priority').val(row.getCell('priority').getValue());
      $('#edit-image').val(row.getCell('image').getValue());
      $('#edit-description').val(row.getCell('description').getValue());
    }

    // initialize Select2's
    $('#edit-definitionId').select2({
      ajax: { url: wwwRoot + 'ajax/getDefinitions.php' },
      allowClear: true,
      dropdownParent: $('#edit-modal'),
      minimumInputLength: 2,
      placeholder: 'caută o definiție...',
      templateResult: formatDefinition,
      templateSelection: formatDefinition,
      width: '100%',
    });

    $('#edit-image').select2({
      allowClear: true,
      dropdownParent: $('#edit-modal'),
      minimumInputLength: 1,
      placeholder: 'caută o imagine...',
      width: '100%',
    });

    // hide the delete button when adding a row
    $('#delete-btn').toggle(row != null);

    // show the modal
    modal.show();
    if (row) {
      $('#edit-displayDate').focus();
    } else {
      $('#edit-definitionId').select2('open');
    }
  }

  // takes a string formatted as YYYY-MM-DD and shortens it, omitting zero values
  function shortenDisplayDate(s) {
    if (s == '0000-00-00') {
      return '';
    } else if (s.startsWith('0000-')) {
      return s.substring(5);
    } else {
      return s;
    }
  }

  function saveRow() {
    $.get(GRID_URL, {
      action: 'save',
      definitionId: $('#edit-definitionId').val(),
      description: $('#edit-description').val(),
      displayDate: $('#edit-displayDate').val(),
      image: $('#edit-image').val(),
      priority: $('#edit-priority').val(),
      wotdId: editingRow ? editingRow.getIndex() : 0,
    }).done(function(resp) {
      if (resp.error) {
        alert('Eroare: ' +  resp.error);
      } else {

        // propagate changes back into the table
        if (editingRow) {
          populateRow(resp.data);
        } else {
          grid.addRow({}).then(function(row) {
            editingRow = row;
            populateRow(resp.data);
          });
        }

        modal.hide();
      }
    }).fail(function() {
      alert('Eroare: Serverul nu răspunde.');
    });
  }

  // populates a table row with data from an Ajax save
  function populateRow(data) {
    editingRow.getCells().forEach(function(cell) {
      var value = data[cell.getField()];
      cell.setValue(value);
    });
  }

  function deleteRow() {
    if (!confirm('Confirmi ștergerea înregistrării?')) {
      return;
    }

    $.get(GRID_URL, {
      action: 'delete',
      wotdId: editingRow.getIndex(),
    }).done(function(resp) {
      if (resp.error) {
        alert('Eroare: ' +  resp.error);
      } else {
        editingRow.delete();
        modal.hide();
      }
    }).fail(function() {
      alert('Eroare: Serverul nu răspunde.');
    });
  }

  function modalHidden() {
    $('#edit-definitionId').select2('destroy');
    $('#edit-image').select2('destroy');
    $(this).find('input, select, textarea').val('');
  }

  init();
});
