$(function() {
  var stem; /* a stem frequent object to be cloned for each addition */
  var trigger; /* .frequentObjects div containing the most recently used '+' button */

  const COOKIE_NAME = 'frequentObjects';
  const DATA_SOURCES = {
    sources: 'ajax/getSources.php',
    tags: 'ajax/getTags.php',
  }

  function init() {
    stem = $('#frequentObjectStem').detach().removeAttr('id');

    $('.frequentObjects').each(loadFromCookie);

    /* use on() so that cloned copies of stem also respond */
    $('.frequentObjects').on('click', '.frequentObject', frequentObjectClick);
    $('.frequentObjects').on('click', '.frequentObjectDelete', frequentObjectDelete);

    $('#frequentObjectAdd').click(frequentObjectAddClick);

    $('#frequentObjectModal').on('shown.bs.modal', modalOpen);
    $('#frequentObjectModal').on('hidden.bs.modal', modalClose);
  }

  function modalOpen(e) {
    trigger = $(e.relatedTarget).closest('.frequentObjects');
    var type = trigger.data('type');

    $('#addObjectId').select2({
      ajax: {
        url: wwwRoot + DATA_SOURCES[type],
      },
      minimumInputLength: 1,
      placeholder: 'alegeți un obiect',
      width: '100%',
    });

    $('#addObjectId').select2('open');
  }

  function modalClose(e) {
    $('#addObjectId').val('');
    $('#addObjectId').select2('destroy');
  }

  function loadFromCookie() {
    var cookieName = COOKIE_NAME + '-' + $(this).data('name');

    var cookieValue = $.cookie(cookieName);
    if (cookieValue) {
      var dict = JSON.parse(cookieValue);
      for (var i in dict) {
        frequentObjectAdd(dict[i].id, dict[i].text, $(this));
      }
    }
  }

  // div: a .frequentObjects div
  function saveToCookie(div) {
    var cookieName = COOKIE_NAME + '-' + div.data('name');

    var dict = [];
    div.find('.frequentObject').each(function() {
      dict.push({
        id: $(this).data('id'),
        text: $(this).data('text'),
      });
    });

    $.cookie(cookieName, JSON.stringify(dict), { expires: 3650, path: '/' });
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
  }

  function frequentObjectAddClick() {
    var id = $('#addObjectId').val();
    var text = $("#addObjectId option:selected").text();
    $('#frequentObjectModal').modal('hide');

    if (id) {
      // add the selected object unless it is already in the list
      var exists = trigger.find('button[data-id="' + id + '"]').length;
      if (!exists) {
        frequentObjectAdd(id, text, trigger);
        saveToCookie(trigger);
      }
    }
  }

  function frequentObjectAdd(id, text, target) {
    var div = stem.clone(true);

    div.find('button').first()
      .attr('data-id', id)
      .attr('data-text', text)
      .text(text);

    div.insertBefore(target.find('.frequentObjectAddDiv'));
  }

  function frequentObjectDelete() {
    var target = $(this).closest('.frequentObjects');
    $(this).closest('.btn-group').remove();
    saveToCookie(target);
  }

  init();

});
