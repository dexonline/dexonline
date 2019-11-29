$(function() {
  var stem = null;

  var lexemeSourceOptions = {
    ajax: { url: wwwRoot + 'ajax/getSources.php', delay: 500, },
    minimumInputLength: 1,
    placeholder: 'surse care atestă flexiunea',
    width: '100%',
  };
  var similarLexemeOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexemes.php', delay: 500, },
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '250px',
  };
  var fragmentOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexemes.php', delay: 500, },
    minimumInputLength: 1,
    placeholder: 'fragment',
    width: '180px',
  };

  // these needs modelDropdown
  var modelTypeDropdown = $('select[name="modelType"]');
  var modelNumberDropdown = $('select[name="modelNumber"]');
  var restriction = $('input[name=restriction]');

  function init() {
    stem = $('#stem').detach();

    checkLexemeWikiPage();

    initSelect2('[id^=entryIds]', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      createTag: allowNewOptions,
      minimumInputLength: 1,
      tags: true,
      templateSelection: formatEntryWithEditLink,
      width: '100%',
    });

    $('.lexemeEditSaveButton').click(saveEverything);

    $('#lexemeForm').on('change input paste', showRenameDiv);

    initSelect2('#sourceIds', 'ajax/getSourcesById.php', lexemeSourceOptions);

    $('.similarLexeme')
      .select2(similarLexemeOptions)
      .on('change', similarLexemeChange);

    $('input[name="compound"]').click(function() { compoundToggle(this.checked);});
    $('#addFragmentButton').click(addFragment);
    $('#autoFragmentButton').click(autoFragment);
    $('#fragmentContainer').on('click', '.capitalized', capitalizedToggle);
    $('#fragmentContainer').on('click', '.accented', accentedToggle);
    $('#fragmentContainer').on('click', '.editFragmentButton', editFragment);
    $('#fragmentContainer').on('click', '.deleteFragmentButton', deleteFragment);

    initSelect2('.fragment', 'ajax/getLexemesById.php', fragmentOptions);

    $("#refreshButton, #refreshParadigm").click(refreshParadigm);

    // event will not bubble up to document
    // we  keep the form unsubmitted when alt+r from hotkeys.js is pressed
    $("button[name=refreshButton]").click(function(event) { event.preventDefault(); });

    $.getScript(wwwRoot + "js/restrictionMenu.js")
      .done(function() { loadRestrictionMenu(modelTypeDropdown.find(':selected').data('canonical'), restriction.val()); })
      .fail(function() { alert("Nu pot descărca scriptul."); })
  }

  function saveEverything() {
    // allow disabled selects to submit (they should have been readonly,
    // not disabled, but Select2 4.0 doesn't use readonly).
    $('input[name="stopWord"]').prop('disabled', false);
    modelTypeDropdown.prop('disabled', false);
    modelNumberDropdown.prop('disabled', false);
  }

  // this needs modelDropdown.js to be loaded
  function similarLexemeChange() {
    formData = {
      'lexemeId' : $(this).val(),
      'tagIds' : $('#tagIds option:selected').map(
        function() { return $(this).val(); }
        ).get(),
    };

    formData;
    $.ajax({
      type: "POST",
      isLocal: false,
      url: wwwRoot + "ajax/getLexemeInfo.php",
      data: formData,
      dataType: "json",
      success: function(response) {
        $('input[name=restriction]').val(response.restriction);
        modelTypeDropdown.trigger('focus');
        modelNumberDropdown.data('prevNumber', response.modelNumber);
        modelTypeDropdown.val(response.modelType).trigger('change', true);

        $('#tagIds').empty();
        $.each(response.posTags, function(index, e) {
          if (!$("#tagIds option[value='" + e.id + "']").length) {
            $('#tagIds').append(new Option(e.text, e.id, true, true));
          }
        });
        $('#tagIds').trigger('change');
      },
      error: function() { alert("Nu pot descărca modelul de lexem.") },
      timeout: 300000,
    });

  }

  function compoundToggle(status) {
    $('#modelDataSimple').slideToggle();
    $('#modelDataCompound').slideToggle();
    modelNumberDropdown.animate({width: 'toggle'}, 200);
    $('#tip').text("tip" + (status ? "" : " + număr"));
  }

  function capitalizedToggle() {
    var value = Number($(this).is(':checked'));
    $(this)
      .closest('.fragmentWrapper')
      .find('input[name="capitalized[]"]')
      .val(value);
  }

  function accentedToggle() {
    var value = Number($(this).is(':checked'));
    $(this)
      .closest('.fragmentWrapper')
      .find('input[name="accented[]"]')
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
          if (data.accented) {
            $('.accented')
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

  function editFragment() {
    fragmId = $(this)
                .closest('.fragmentWrapper')
                .find('select[name="partIds[]"]')
                .val();
    if (fragmId) {
      $(location).attr('href', wwwRoot + "editare-lexem/" + fragmId);
    }
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

  function refreshParadigm() {

    var formData = $('#paradigmOptions *[name]').serializeArray();
    // gathering other ingredients
    //var lf = {'name': 'lexemeForm', 'value' :  $('#lexemeForm').val()};
    formData.push({'name': 'lexemeForm', 'value' :  $('#lexemeForm').val()});
    if ($('input[name=needsAccent]').is(':checked')) {
      //var na = {'name': 'needsAccent', 'value' :  "1"};
      formData.push({'name': 'needsAccent', 'value' :  "1"});
    }
    $.merge(formData, $('#tagIds').serializeArray())

    $.ajax({
      type: "POST",
      context: $(this),
      isLocal: true,
      url: wwwRoot + "ajax/getParadigm.php",
      data: formData,
      dataType: "html",
      success: function(response) {
        $('#paradigmContent').html(response);
      },
      error: function() { alert("Nu pot descărca paradigma."); },
      timeout: 30000,
    });
  }

  $(document).on('click', '#restrictionMenu.dropdown-menu', function (e) {
    e.stopPropagation();
  });

  $('#restrictionMenu').on('click', '#constraintAccept', function (e) {
    var b = $('#restrictionMenu :checkbox:checked')
    .map(function() {
      return this.value;
    })
    .get()
    .join('');
    $('input[name=restriction]').val(b);
    $('#load.dropdown-toggle').dropdown('toggle');
  });

  init();
});
