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
      $('#labelX').val(coords.cx);
      $('#labelY').val(coords.cy);
    });

    $('#setImgCoords').click(function() {
      $('#tipX').val(coords.cx);
      $('#tipY').val(coords.cy);
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

    $('#tagsGrid').jqGrid({
      url: wwwRoot + 'ajax/visualGetImageTags.php',
      postData: { visualId: $('#visualId').val() },
      datatype: 'json',
      cmTemplate: {sortable: false},
      colNames: ['Id', 'Intrare', 'Text afișat', 'X Etichetă', 'Y Etichetă', 'X Imagine', 'Y Imagine'],
      colModel: [
        {name: 'id', index: 'id', hidden: true},
        {name: 'entry', index: 'entry', width: 80, align: 'center'},
        {name: 'label', index: 'label', width: 120, align: 'center', editable: true},
        {name: 'labelX', index: 'labelX', width: 55, align: 'center', editable: true},
        {name: 'labelY', index: 'labelY', width: 55, align: 'center', editable: true},
        {name: 'tipX', index: 'tipX', width: 55, align: 'center', editable: true},
        {name: 'tipY', index: 'tipY', width: 55, align: 'center', editable: true}
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
               { // edit options
                 afterSubmit: checkServerResponse,
               },
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
    if (response.responseJSON.success) {
      $('#previewTags').attr('data-tag-info', response.responseJSON.tagInfo);
      return [ true ];
    } else {
      return [false, response.responseJSON.msg];
    }
  }

  init();
});
