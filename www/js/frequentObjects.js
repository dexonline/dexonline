$(function() {
  var stem; /* a stem frequent object to be cloned for each addition */
  var trigger; /* .frequentObjects div containing the most recently used '+' button */

  // We used to store frequent objects in cookies, but cookies can become
  // long. Besides, the server doesn't need them. So we check for cookies and,
  // if found, move them to local storage.
  const STORAGE_NAME = 'frequentObjects';
  const DATA_SOURCES = {
    sources: 'ajax/getSources.php',
    tags: 'ajax/getTags.php',
  }

  function init() {
    stem = $('#frequentObjectStem').detach().removeAttr('id');

    $('.frequentObjects').each(loadFromStorage);

    /* use on() so that cloned copies of stem also respond */
    $('.frequentObjects').on('click', '.frequentObject', frequentObjectClick);

    $('#frequentObjectAdd').click(frequentObjectAddClick);

    $('#frequentObjectModal').on('shown.bs.modal', modalOpen);
    $('#frequentObjectModal').on('hidden.bs.modal', modalClose);

    $('.frequentObjects')
      .sortable({
        trash: '#frequentObjectsTrash',
        handle: '.frequentObject',
      })
      .on('dragstart', 'button', dragStart)
      .on('dragend', 'button', dragEnd);

    $('#frequentObjectsTrash').droppable({
      classes: {
        'ui-droppable-hover': 'frequentObjectsTrashActive',
      },
      drop: frequentObjectDelete,
    });
  }

  function dragStart() {
    $('#frequentObjectsTrash').stop().fadeIn();
  }

  function dragEnd(e) {
    // make sure the plus button stays last
    var btn = $(e.target);
    if (btn.is(':last-child')) {
      btn.insertBefore(btn.prev());
    }

    $('#frequentObjectsTrash').stop().fadeOut();
    saveToStorage($(this).closest('.frequentObjects'));
  }

  function modalOpen(e) {
    trigger = $(e.relatedTarget).closest('.frequentObjects');
    var type = trigger.data('type');

    $('#addObjectId').select2({
      ajax: {
        url: wwwRoot + DATA_SOURCES[type],
      },
      dropdownParent: $('#frequentObjectModal'),
      minimumInputLength: 1,
      placeholder: 'alegeți o valoare',
      width: '100%',
    });

    $('#addObjectId').select2('open');
  }

  function modalClose(e) {
    $('#addObjectId').val('');
    $('#addObjectId').select2('destroy');
  }

  /**
   * Loads frequent objects from local storage. If not found, tries to load
   * them from cookies and migrate them to local storage.
   * @param $(this) A frequent objects container.
   */
  function loadFromStorage() {
    var key = STORAGE_NAME + '-' + $(this).data('name');
    var value = localStorage.getItem(key);

    if (!value) {
      // fallback to cookies; if found, migrate them to local storage
      value = $.cookie(key);
      if (value) {
        localStorage.setItem(key, value);
        $.removeCookie(key, {path: '/'});
      }
    }

    if (value) {
      var dict = JSON.parse(value);
      for (var i in dict) {
        frequentObjectAdd(dict[i].id, dict[i].text, $(this));
      }
    }
  }

  // div: a .frequentObjects div
  function saveToStorage(div) {
    var key = STORAGE_NAME + '-' + div.data('name');

    var dict = [];
    div.find('.frequentObject').each(function() {
      dict.push({
        id: $(this).data('id'),
        text: $(this).data('text'),
      });
    });

    if (dict.length) {
      localStorage.setItem(key, JSON.stringify(dict));
    } else {
      localStorage.removeItem(key);
    }
  }

  function frequentObjectClick() {
    // get the id and text from the clicked button
    var id = $(this).data('id');
    var text = $(this).data('text');

    // get the target select2 from the wrapping .frequentObjects
    var targetId = $(this).closest('.frequentObjects').data('target');
    var target = $(targetId);

    // add the option
    var opt = target.children('option[value="' + id + '"]');
    if (opt.length) {
      // option already exists, just make it selected
      opt.prop('selected', true);
    } else {
      target.append(new Option(text, id, true, true))
    }
    target.trigger('change');

    // focus the focusTarget element
    var focusId = $(this).closest('.frequentObjects').data('focusTarget');
    var focusElem = $(focusId);
    if (focusElem.data('select2')) {
      focusElem.select2('focus');
    } else {
      focusElem.focus();
    }
  }

  function frequentObjectAddClick() {
    var id = $('#addObjectId').val();
    var text = $('#addObjectId option:selected').text();
    $('#frequentObjectModal').modal('hide');

    if (id) {
      // add the selected object unless it is already in the list
      var exists = trigger.find('button[data-id="' + id + '"]').length;
      if (!exists) {
        frequentObjectAdd(id, text, trigger);
        saveToStorage(trigger);
      }
    }
  }

  function frequentObjectAdd(id, text, target) {
    var div = stem.clone(true);

    div
      .attr('data-id', id)
      .attr('data-text', text)
      .text(text);

    div.insertBefore(target.find('.frequentObjectInsertTarget'));
  }

  function frequentObjectDelete(event, ui) {
    var target = ui.draggable.closest('.frequentObjects');
    ui.draggable.fadeOut(function(){
      $(this).remove();
      saveToStorage(target);
    });
  }

  init();

});
