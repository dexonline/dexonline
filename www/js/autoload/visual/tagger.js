$(function() {

  var coords = new Object();
  var grid;
  var gridUrl = wwwRoot + 'visual-ajax';
  var visualId = $('#visualId').val();

  function init() {
    initSelect2('#entryId', 'ajax/getEntriesById.php', {
      ajax: {
        url: wwwRoot + 'ajax/getEntries.php',
      },
      minimumInputLength: 1,
      placeholder: 'caută o intrare',
      width: '300px',
    });

    $('#tagEntryId').select2({
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      placeholder: 'caută o intrare',
      width: '300px',
    }).change(copyEntryToTag);

    $('#jcrop').Jcrop({
      bgColor: '',
      boxHeight: 0,
      boxWidth: $('.imageHolder').width(),
      onSelect: setCoords,
      onChange: setCoords,
    }).error(function() {
      $('.imageHolder').html('Nu pot încărca imaginea. Te rugăm să contactezi un administrator.');
    });

    $('#setTextCoords').click(function() {
      $('#labelX').val(coords.x);
      $('#labelY').val(coords.y);
    });

    $('#setImgCoords').click(function() {
      $('#tipX').val(coords.x);
      $('#tipY').val(coords.y);
    });

    /* Validate new tag data before submitting the form. */
    $('#tag-form').submit(function(e) {
      var err = null;
      if (!$('#tagEntryId').val()) {
        err = 'Trebuie să specificați o intrare.';
      } else if (!$('#tagLabel').val()) {
        err = 'Textul de afișat nu poate fi vid.';
      } else if (!$('#labelX').val() || !$('#labelY').val()) {
        err = 'Coordonatele centrului etichetei nu pot fi vide.';
      } else if (!$('#tipX').val() || !$('#tipY').val()) {
        err = 'Coordonatele vârfului săgeții nu pot fi vide.';
      }

      if (err) {
        alert(err);
        $(this).removeData('submitted'); /* allow resubmission */
        e.preventDefault();
      }
    });

    grid = new Tabulator('#tagsGrid', {
      ajaxURL: gridUrl,
      ajaxParams: { action: 'load', visualId: visualId},
      columns:[
        {
          title: 'id',
          field: 'id',
          visible: false,
        }, {
          title: 'intrare',
          cssClass: 'col-readonly',
          field: 'description',
        }, {
          title: 'etichetă',
          editor: 'input',
          field: 'label',
        }, {
          title: 'X etichetă',
          editor: 'number',
          field: 'labelX',
        }, {
          title: 'Y etichetă',
          editor: 'number',
          field: 'labelY',
        }, {
          title: 'X vîrf',
          editor: 'number',
          field: 'tipX',
        }, {
          title: 'Y vîrf',
          editor: 'number',
          field: 'tipY',
        }, {
          title: 'acțiuni',
          cellClick: deleteRow,
          cssClass: 'col-clickable',
          formatter: printDeleteIcon,
          headerSort: false,
          hozAlign: 'center',
          width: 60,
        },
      ],
      headerSortElement: '<i class="material-icons">expand_less</i>',
      initialSort:[{ column: 'label', dir:'asc' }],
      layout: 'fitColumns',
    });
    grid.on('cellEdited', cellEdited);
  }

  function printDeleteIcon() {
    return '<i class="material-icons">delete</i>';
  }

  function deleteRow(e, cell) {
    var index = cell.getRow().getIndex();
    $.ajax({
      url: gridUrl,
      data: { action: 'delete', tagId: index }
    }).done(function(resp) {
      cell.getRow().delete();
      updateTagInfo(resp);
    }).fail(function() {
      alert('Nu am putut șterge eticheta (eroare pe server).');
    });
  }

  function cellEdited(cell) {
    $.ajax({
      url: gridUrl,
      data: {
        action: 'save',
        field: cell.getField(),
        tagId: cell.getRow().getIndex(),
        value: cell.getValue(),
      },
    }).done(function(resp) {
      updateTagInfo(resp);
    }).fail(function() {
      cell.restoreOldValue();
      alert('Nu am putut salva modificarea (eroare pe server).');
    });
  }

  function updateTagInfo(info) {
    $('#previewTags').attr('data-tag-info', JSON.stringify(info));
  }

  function setCoords(c) {
    // Calculates the centre of the selection
    coords = {
      x: Math.round(c.x + c.w / 2),
      y: Math.round(c.y + c.h / 2),
    };
  }

  /** Replaces the submit event that triggers on change, set in select2Dev.js */
  function copyEntryToTag() {
    if (!$('#tagLabel').val()) {
      var text = $(this).select2('data')[0].text;

      // Matches only the entry, without the description in brackets
      text = text.match(/^[^ \(]+/);

      $('#tagLabel').val(text);
    }
  }

  init();
});
