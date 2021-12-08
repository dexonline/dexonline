/* Custom code built on top of select2.min.js */

$.fn.select2.defaults.set('language', 'ro');

$(function() {

  /**
   * Resolves a select element whose <option>s contain only IDs.
   * Fetches the display value and possibly other attributes.
   * obj = jQuery object
   * url = Ajax URL used to resolve IDs to objects
   **/
  function resolveSelect(obj, url) {
    var values = [];
    obj.find('option').each(function() {
      values.push($(this).val());
    });

    return $.ajax({
      url: wwwRoot + url + '?q=' + JSON.stringify(values),
    }).done(function(data) {
      for (var i = 0; i < data.length; i++) {
        var o = obj.find('option').eq(i);
        o.html(data[i].text);
        // Convert any properties besides id and text to HTML5 data attributes
        for (var prop in data[i]) {
          if (prop != 'id' && prop != 'text') {
            o.data(prop, data[i][prop]);
          }
        }
      }
    });
  }

  /**
   * Builds a Deferred around resolveSelect() that runs when all the objects are initialized.
   **/
  function resolveSelectDeferred(sel, url) {
    var deferreds = [];

    $(sel).each(function() {
      var obj = $(this);
      deferreds.push(
        resolveSelect(obj, url)
      );
    });

    return $.when.apply($, deferreds);
  }

  /**
   * Initialize select2 objects whose <option>s contain only IDs.
   * @param string sel CSS selector
   * @param string url Ajax URL used to resolve IDs to objects
   * @param object options Options passed to select2
   * @param bool sharedDrag Whether options can be dragged between objects.
   *
   * Returns a Deferred object that runs when all objects are initialized.
   **/
  window.initSelect2 = function(sel, url, options, sharedDrag = false) {
    return resolveSelectDeferred(sel, url)
      .done(function() {
        var s = $(sel);
        s.select2(options);
        s.each(function() {
          makeClickable($(this));
        });
        makeDraggable(s, sharedDrag);
      });
  }

  /**
   * Make values draggable.
   * @param aray s A set of jQuery objects.
   * @param bool shared Whether options can be dragged between objects.
   */
  function makeDraggable(s, shared = false) {
    var elems = s.parent()
        .find('.select2-selection--multiple .select2-selection__rendered')
        .get();
    if (shared && elems.length) {
      dragula(elems).on('drop', dragulaDrop);
    } else {
      for (var i = 0; i < elems.length; i++) {
        dragula([ elems[i] ]).on('drop', dragulaDrop);
      }
    }
  }

  /**
   * Propagate the new order in target to the underlying select. Likewise for
   * source, if different from target.
   */
  function dragulaDrop(el, target, source, sibling) {
    // make sure the typing area stays last
    if (!sibling) {
      $(el).insertBefore($(el).prev());
    }

    rebuildList(target);
    if (source != target) {
      rebuildList(source);
    }
  }

  /**
   * Rebuilds the corresponding <select> and Select2 after a drop.
   * @param c A Select2 container element.
   */
  function rebuildList(c) {
    var sel = $(c).closest('.select2').prev('select');
    sel.empty();

    var ids = []; // remove duplicate IDs

    // use slice() to exclude the typing area
    $(c).children().slice(0, -1).each(function() {
      var d = $(this).data().data;
      if (!ids.includes(d.id)) {
        sel.append(new Option(d.text, d.id, true, true));
        ids.push(d.id);
      }
    });

    sel.trigger('change');
  }

  // Allow sorting of select2 options by clicking on them and using the arrow keys.
  // Pass a single object or arrow keys will move objects in all boxes simultaneously.
  function makeClickable(s) {
    s.parent().on('click', 'li.select2-selection__choice', function() {
      $(this).siblings().removeClass('select2-highlighted');
      $(this).addClass('select2-highlighted');
    });
    s.parent().on('keyup', '.select2-container', function(e) {
      var o = $(this).find('.select2-highlighted');
      if (o.length) {
        var index = o.index();
        var length = s.children().length;
        var opt = s.children().eq(index); // <select> option

        var step = 0;
        if ((e.keyCode == 37) && (index > 0)) {
          opt.prev().before(opt);
          step = -1;
        } else if ((e.keyCode == 39) && (index < length - 1)) {
          opt.next().after(opt);
          step = 1;
        }

        if (step) {
          s.trigger('change');
          $(this).find('.select2-selection__choice').eq(index + step).addClass('select2-highlighted');
        }
      }
    });
  }

  /**
   * Refresh select2 objects whose <option>s contain only IDs.
   * sel = CSS selector
   * url = Ajax URL used to resolve IDs to objects
   **/
  window.refreshSelect2 = function(sel, url) {
    return resolveSelectDeferred(sel, url)
      .done(function() {
        $(sel).trigger('change');
      });
  }

  function createUserAjaxStruct(priv) {
    if (typeof(priv)==='undefined') priv = 0;
    return {
      data: function(params) {
        return {
          term: params.term,
          priv: priv,
        };
      },
      url: wwwRoot + 'ajax/getUsers.php',
    };
  }

  /**
   * The consistent accent / paradigm info propagates in two ways:
   * - for initial elements, using HTML5 data attributes
   * - for dynamically added elements, using json parameters
   **/
  window.formatLexemeWithEditLink = function(lexeme) {
    var elementData = $(lexeme.element).data();
    var html;

    if (startsWith(lexeme.id, '@')) {
      // don't show an edit link for soon-to-be created lexemes
      html = lexeme.text;
    } else {
      html = lexeme.text +
        ' <a href="' + wwwRoot + 'editare-lexem?lexemeId=' + lexeme.id + '">' +
        '<span class="material-icons">edit</span>'+
        '</a>';
    }

    if ((lexeme.consistentAccent == '0') ||
        (lexeme.hasParadigm === false) ||
        (elementData.consistentAccent === '0') ||
        (elementData.hasParadigm === false)) {
      return $('<span class="text-danger">' + html + '</span>');
    } else {
      return $('<span>' + html + '</span>');
    }
  }

  window.formatEntryWithEditLink = function(entry) {
    if (startsWith(entry.id, '@')) {
      // don't show an edit link for soon-to-be created entries
      var link = '';
    } else {
      var link = ' <a href="' + wwwRoot + 'editare-intrare?id=' + entry.id + '">' +
          '<span class="material-icons">edit</span>'+
          '</a>';
    }

    return $('<span>' + entry.text + link + '</span>');
  }

  window.formatDefinition = function(item) {
    if (item.id && item.source) {
      return $('<span>' + item.html + ' (' + item.source + ') [' + item.id + ']' + '</span>');
    }
    return item.text;
  }

  window.allowNewOptions = function(data) {
    return {
      id: '@' + data.term,
      text: data.term + ' (cuvânt nou)',
    };
  };

  initSelect2('.select2Tags', 'ajax/getTagsById.php', {
    ajax: { url: wwwRoot + 'ajax/getTags.php' },
    minimumInputLength: 1,
    width: '100%',
  });

  initSelect2('.select2Trees', 'ajax/getTreesById.php', {
    ajax: { url: wwwRoot + 'ajax/getTrees.php' },
    minimumInputLength: 1,
    width: '100%',
  });

  initSelect2('.select2Users', 'ajax/getUsersById.php', {
    ajax: createUserAjaxStruct(),
    minimumInputLength: 3,
    placeholder: '',
  });

});
