$(function() {

  function init() {

    initSelect2('#lexemIds', 'ajax/getLexemsById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexems.php' },
      minimumInputLength: 1,
      templateSelection: formatLexemWithEditLink,
    });
            
    initSelect2('#structuristId', 'ajax/getUsersById.php', {
      ajax: createUserAjaxStruct(PRIV_STRUCT),
      allowClear: true,
      minimumInputLength: 3,
      placeholder: '(opțional)',
      width: '100%',
    });
    
    initSelect2('#treeIds', 'ajax/getTreesById.php', {
      ajax: { url: wwwRoot + 'ajax/getTrees.php' },
      minimumInputLength: 1,
    });
            
    $('#mergeEntryId').select2({
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      placeholder: 'alegeți o intrare',
      width: '100%',
    });

    $('.toggleRepLink').click(toggleRepClick);
    $('.toggleRepSelect').click(toggleRepChange);
    $('.toggleStructuredLink').click(toggleStructuredClick);
    $('#defFilterSelect').click(defFilterChange);

    $('.dissociateLink').click(confirmDissociateDefinition);

    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestei intrări?');
    });
  }

  /* Definitions can be shown as internal or HTML notation, with abbreviations expanded or collapsed. This gives rise to four combinations, coded on
   * two bits each. Clicking on the "show / hide HTML" and show / hide abbreviations" fiddles some bits and sets the "visible" class on the
   * appropriate div. */
  function toggleRepClick() {
    // Hide the old definition
    var oldActive = $(this).closest('.defDetails').prevAll('[data-active]');
    oldActive.stop().slideToggle().removeAttr('data-active');

    // Recalculate the code and show the new definition
    var code = oldActive.attr('data-code') ^ $(this).attr('data-order');
    var newActive = $(this).closest('.defDetails').prevAll('[data-code=' + code + ']');
    newActive.stop().slideToggle().attr('data-active', '');

    // Toggle the link text and data-value attribute
    var tmp = $(this).text();
    $(this).text($(this).attr('data-other-text'));
    $(this).attr('data-other-text', tmp);
    $(this).attr('data-value', 1 - $(this).attr('data-value'));
    return false;
  }

  /* User has selected a value from the text/html select. Toggle all definitions that aren't in that state already. */
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
      parent.addClass('unstructured');
      var value = 0;
    } else {
      parent.removeClass('unstructured');
      parent.addClass('structured');
      var value = 1;
    }
    $.get(wwwRoot + 'ajax/setDefinitionStructured.php?id=' + id + '&value=' + value);

    $(this).siblings().toggle();
    $(this).toggle();

    return false;
  }

  function defFilterChange() {
    if ($(this).val() == 'structured') {
      $('.defWrapper.unstructured').hide('slow');
    } else {
      $('.defWrapper.unstructured').show('slow');
    }
    if ($(this).val() == 'unstructured') {
      $('.defWrapper.structured').hide('slow');
    } else {
      $('.defWrapper.structured').show('slow');
    }
  }

  function confirmDissociateDefinition() {
    return confirm('Confirmați disocierea definiției?');
  }

  init();
});
