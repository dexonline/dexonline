$(function() {

  function init() {

    checkEntryWikiPage();

    initSelect2('#mainLexemeIds, #variantLexemeIds', 'ajax/getLexemesById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexemes.php' },
      minimumInputLength: 1,
      templateSelection: formatLexemeWithEditLink,
    });

    var entryAjax = {
      url: wwwRoot + 'ajax/getEntries.php',
      data: function(params) {
        params['exclude'] = $('#entryId').val();
        return params;
      },
    }

    $('#mergeEntryId').select2({
      ajax: entryAjax,
      minimumInputLength: 1,
      placeholder: 'alegeți o intrare',
      width: '100%',
    });

    $('#associateEntryIds').select2({
      ajax: entryAjax,
      minimumInputLength: 1,
      placeholder: 'alegeți una sau mai multe intrări',
      width: '100%',
    });

    $('#description').on('change input paste', showRenameDiv);

    $('.toggleRepLink').click(toggleRepClick);
    $('.toggleRepSelect').click(toggleRepChange);
    $('.toggleStructuredLink').click(toggleStructuredClick);
    $('#defFilterSelect, #structurableFilter').click(defFilterChange);
    $('#treeFilterSelect').change(treeFilterChange);

    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestei intrări?');
    });

    $('#mergeModal').on('shown.bs.modal', function () {
      $('#mergeEntryId').select2('open');
    });

    $('#associateModal').on('shown.bs.modal', associateModalShown);

    $('#dissociateButton').click(function () {
      return confirm('Confirmați disocierea?');
    });

    $('#moveLexemesUp').click(function() { moveLexemes('#variantLexemeIds', '#mainLexemeIds'); });
    $('#moveLexemesDown').click(function() { moveLexemes('#mainLexemeIds', '#variantLexemeIds'); });
  }

  function showRenameDiv() {
    $('#renameDiv').removeClass('hidden');
  }

  /* Definitions can be shown as internal or HTML notation, with abbreviations expanded
   * or collapsed. This gives rise to four combinations, coded on two bits each.
   * Clicking on the "show / hide HTML" and show / hide abbreviations" fiddles some bits
   * and sets the "visible" class on the appropriate element. */
  function toggleRepClick() {
    // Hide the old definition
    var oldActive = $(this).closest('.defWrapper').find('[data-active]');
    oldActive.stop().hide().removeAttr('data-active');

    // Recalculate the code and show the new definition
    var code = oldActive.attr('data-code') ^ $(this).attr('data-order');
    var newActive = $(this).closest('.defWrapper').find('[data-code=' + code + ']');
    newActive.stop().show().attr('data-active', '');

    // Toggle the link text and data-value attribute
    var tmp = $(this).text();
    $(this).text($(this).attr('data-other-text'));
    $(this).attr('data-other-text', tmp);
    $(this).attr('data-value', 1 - $(this).attr('data-value'));
    return false;
  }

  /* User has selected a value from the text/html select. Toggle all definitions that
   * aren't in that state already. */
  function toggleRepChange() {
    var order = $(this).attr('data-order');
    var value = 1 - $(this).val(); // Links that have this BAD value need to be clicked.
    $('.toggleRepLink[data-order=' + order + '][data-value=' + value + ']').click();
  }

  function toggleStructuredClick() {
    var parent = $(this).closest('.defWrapper');
    var id = parent.attr('id').split('_')[1];
    if (parent.hasClass('structured')) {
      parent.removeClass('structured');
      var value = 0;
    } else {
      parent.addClass('structured');
      var value = 1;
    }
    $.get(wwwRoot + 'ajax/setDefinitionStructured.php?id=' + id + '&value=' + value);

    $(this).siblings().toggle();
    $(this).toggle();

    return false;
  }

  function treeFilterChange() {
    $('.tree').stop().slideDown();
    if ($(this).val() != -1) {
      $('.tree:not(.tree-status-' + $(this).val()).stop().slideUp();
    }
  }

  function defFilterChange() {
    var status = $('#defFilterSelect').val();
    var structurable = $('#structurableFilter').is(':checked');

    $('.defWrapper').each(function() {
      var show = true;
      if (((status == 'structured') && $(this).is(':not(.structured)')) ||
          ((status == 'unstructured') && $(this).is('.structured')) ||
          (structurable && !$(this).is('.structurable'))) {
        show = false;
      }

      $(this).toggle(show);
    });
  }

  function associateModalShown() {
    // copy definition ids from checked checkboxes
    var checkboxes = $('input[name="selectedDefIds[]"]:checked');
    var ids = checkboxes.map(function() {return $(this).val(); });
    var idString = ids.get().join();

    $('input[name="associateDefinitionIds"]').val(idString);
    $('#associateEntryIds').select2('open');
  }

  function moveLexemes(fromId, toId) {
    var from = $(fromId);
    var to = $(toId);
    from.val().forEach(function(lexemeId) {
      to.append(new Option('', lexemeId, true, true));
    });
    from.val(null).trigger('change');
    refreshSelect2(toId, 'ajax/getLexemesById.php');
  }

  function checkEntryWikiPage() {
    var entryId = $('#entryId').val();
    ifWikiPageExists('Intrare:' + entryId, function() {
      $('#wikiLink')
        .attr('title', 'intrarea are o pagină wiki')
        .toggleClass('btn-default btn-warning');
    });
  }

  init();
});
