$(function() {

  var stem = null;

  var lexemeSourceOptions = {
    ajax: { url: wwwRoot + 'ajax/getSources.php' },
    minimumInputLength: 1,
    placeholder: 'surse care atestă flexiunea',
    width: '100%',
  };
  var similarLexemeOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexemes.php' },
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '250px',
  };
  var fragmentOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexemes.php' },
    minimumInputLength: 1,
    placeholder: 'fragment',
    width: '180px',
  };

  function init() {
    stem = $('#stem').detach();

    checkLexemeWikiPage();

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
      width: '100%',
    });

    $('.lexemeEditSaveButton').click(saveEverything);

    $('#lexemeForm').on('change input paste', showRenameDiv);

    initSelect2('#sourceIds', 'ajax/getSourcesById.php', lexemeSourceOptions);

    $('.similarLexeme')
      .select2(similarLexemeOptions)
      .on('change', similarLexemeChange);

    $('input[name="compound"]').click(compoundToggle);
    $('#addFragmentButton').click(addFragment);
    $('#autoFragmentButton').click(autoFragment);
    $('#fragmentContainer').on('click', '.capitalized', capitalizedToggle);
    $('#fragmentContainer').on('click', '.deleteFragmentButton', deleteFragment);

    initSelect2('.fragment', 'ajax/getLexemesById.php', fragmentOptions);
  }

  function saveEverything() {
    // allow disabled selects to submit (they should have been readonly,
    // not disabled, but Select2 4.0 doesn't use readonly).
    $('input[name="stopWord"]').prop('disabled', false);
    $('select[name="modelType"]').prop('disabled', false);
    $('select[name="modelNumber"]').prop('disabled', false);
  }

  function similarLexemeChange() {
    var lexemeId = $(this).find('option:selected').val();
    var url = wwwRoot + 'ajax/getLexemeInfo.php?id=' + lexemeId;
    $.get(url)
      .done(function(data) {
        $('select[name="modelType"]').val(data.modelType);
        $('select[name="modelNumber"]').data('selected', data.modelNumber);
        updateModelList($('*[data-model-dropdown]'));
        $('input[name="restriction"]').val(data.restriction);

        // copy part-of-speech tags (skip already existing ones)
        $.each(data.posTags, function(index, e) {
          if (!$("#tagIds option[value='" + e.id + "']").length) {
            $('#tagIds').append(new Option(e.text, e.id, true, true));
          }
        });
        $('#tagIds').trigger('change');
      });
  }

  function compoundToggle() {
    $('#modelDataSimple').slideToggle();
    $('#modelDataCompound').slideToggle();
  }

  function capitalizedToggle() {
    var value = Number($(this).is(':checked'));
    $(this)
      .closest('.fragmentWrapper')
      .find('input[name="capitalized[]"]')
      .val(value);
  }

  function autoFragment() {
    var parts = $('#lexemeForm').val().split(/[-\s]+/);

    // sync the needsAccent checkbox with the lexeme form
    var hasAccent = $('#lexemeForm').val().indexOf("'") != -1;
    $('input[name="needsAccent"]').prop('checked', hasAccent);

    // remove all fragments and add parts.length new ones
    $('#fragmentContainer').empty();
    for (var i = 0; i < parts.length; i++) {
      addFragment();
    }

    // look up the fragments
    parts.forEach(function(form, i) {
      $.ajax({
        url: wwwRoot + 'ajax/getLexemeByInflectedForm.php?form=' + form,
      }).done(function(data) {
        if (data) {
          $('.fragment')
            .eq(i)
            .append(new Option(data.text, data.id, true, true))
            .trigger('change');
          if (data.capitalized) {
            $('.capitalized')
              .eq(i)
              .trigger('click');
          }
        }
      });
    });
  }

  function addFragment() {
    var t = stem.clone(true).appendTo('#fragmentContainer');
    t.find('.fragment').select2(fragmentOptions);
  }

  function deleteFragment() {
    $(this).closest('.fragmentWrapper').remove();
  }

  function showRenameDiv() {
    $('#renameDiv').removeClass('hidden');
  }

  function checkLexemeWikiPage() {
    var lexemeId = $('input[name="lexemeId"]').val();
    ifWikiPageExists('Lexem:' + lexemeId, function() {
      $('#wikiLink')
        .attr('title', 'lexemul are o pagină wiki')
        .toggleClass('btn-default btn-warning');
    });
  }

  init();
});
