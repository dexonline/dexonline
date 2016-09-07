$(function() {
  
  var jcrop_api;
  var coords = new Object();

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
      onChange: setCoords
    }, function() {
      jcrop_api = this;
    });

    $('#jcrop').error(function() {
      $('.imageHolder').html('Nu pot încărca imaginea. Te rugăm să contactezi un administrator.');
    });

    $('#setTextCoords').click(function() {
      $('#textXCoord').val(coords.cx);
      $('#textYCoord').val(coords.cy);
    });

    $('#setImgCoords').click(function() {
      $('#imgXCoord').val(coords.cx);
      $('#imgYCoord').val(coords.cy);
    });

    $('#previewTags').click(function() {
      img = $('#jcrop');
      $.colorbox({
        href: img.attr('src'),
        title: img.attr('title'),
        maxWidth: '84%', maxHeight: '84%',
        onComplete: function() {
          var visualId = $('#visualId').val();
          addCanvas();
          drawOnCanvas(visualId);
        },
        onCleanup: function() {
          removeCanvas();
        }
      });
    });

    /* Validate new tag data before submitting the form. */
    $('#addTagButton').click(function() {
      if (!$('#tagEntryId').val()) {
        alert('Trebuie să specificați o intrare.');
        return false;
      } else if (!$('#tagLabel').val()) {
        alert('Textul de afișat nu poate fi vid.');
        return false;
      } else if (!$('#textXCoord').val() || !$('#textYCoord').val()) {
        alert('Coordonatele centrului etichetei nu pot fi vide.');
        return false;
      } else if (!$('#imgXCoord').val() || !$('#imgYCoord').val()) {
        alert('Coordonatele vârfului săgeții nu pot fi vide.');
        return false;
      }
      return true;
    });

    $('#tagsGrid').jqGrid({
      url: wwwRoot + 'ajax/visualGetImageTags.php',
      postData: { visualId: $('#visualId').val(), usage: 'table' },
      datatype: 'json',
      cmTemplate: {sortable: false},
      colNames: ['Id', 'Intrare', 'Text afișat', 'X Etichetă', 'Y Etichetă', 'X Imagine', 'Y Imagine'],
      colModel: [
        {name: 'id', index: 'id', hidden: true},
        {name: 'entry', index: 'entry', width: 80, align: 'center'},
        {name: 'label', index: 'label', width: 120, align: 'center', editable: true},
        {name: 'textXCoord', index: 'textXCoord', width: 55, align: 'center', editable: true},
        {name: 'textYCoord', index: 'textYCoord', width: 55, align: 'center', editable: true},
        {name: 'imgXCoord', index: 'imgXCoord', width: 55, align: 'center', editable: true},
        {name: 'imgYCoord', index: 'imgYCoord', width: 55, align: 'center', editable: true}
      ],
      rowNum: 20,
      recreateForm: true,
      width: 900,
      height: '100%',
      rowList: [20, 50, 100, 200],
      pager: $('#tagsPaging'),
      viewrecords: true,
      caption: 'Etichete existente',
      editurl: wwwRoot + 'ajax/visualTagsEdit.php',
      ondblClickRow: function(rowid) {
        $(this).jqGrid('editGridRow', rowid, {
          // edit options
          closeAfterEdit: true,
          closeOnEscape: true,
          afterSubmit: checkServerResponse
        });
      }
    })
      .navGrid('#tagsPaging',
               { // grid options
                 add: false,
                 search: false,
                 edittitle: 'editează',
                 deltitle: 'șterge',
                 refreshtitle: 'reîncarcă'
               },
               { }, // edit options
               { }, // add options
               { // delete options
                 afterSubmit: checkServerResponse,
                 closeOnEscape: true,
               });
  }

  function setCoords(c) {
    // Calculates the centre of the selection
    coords.cx = Math.round(c.x + c.w / 2);
    coords.cy = Math.round(c.y + c.h / 2);
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

  function checkServerResponse(response, postData) {
    if (response.responseText) {
      return [false, response.responseText];
    } else {
      return [true];
    }
  }

  init();
});
