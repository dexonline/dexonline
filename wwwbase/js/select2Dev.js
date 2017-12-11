/* Custom code built on top of select2.min.js */

$.fn.select2.defaults.set('language', 'ro');

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
 * sel = CSS selector
 * url = Ajax URL used to resolve IDs to objects
 * options = options passed to select2
 *
 * Returns a Deferred object that runs when all objects are initialized.
 **/
function initSelect2(sel, url, options) {
  return resolveSelectDeferred(sel, url)
    .done(function() {
      var s = $(sel);
      s.select2(options);
      s.each(function() {
        makeSortable($(this));
        makeClickable($(this));
      });
    });
}

// Make values sortable. The trick here is to make the <select> options mirror
// the value order.
// Pass a single object or dragging can move values between objects.
function makeSortable(s) {
  s.parent().find('ul.select2-selection__rendered').sortable({
    containment: 'parent',

    start: function(e, ui) {
      // store the starting index so we can track its movement
      $(this).attr('data-old-index', ui.item.index());
    },

    update: function(e, ui) {
      var oldIndex = $(this).attr('data-old-index');
      // sometimes index() returns a value equal to length, not length - 1
      var newIndex = Math.min(ui.item.index(), s.children().length - 1);

      $(this).removeAttr('data-old-index');

      var o = s.children().eq(oldIndex).remove();
      if (newIndex == 0) {
        s.prepend(o);
      } else {
        s.children().eq(newIndex - 1).after(o);
      }
      s.trigger('change'); // make this count as a change in the meaning tree editor
    }
  });
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
function refreshSelect2(sel, url) {
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
function formatLexemWithEditLink(lexem) {
  var elementData = $(lexem.element).data();
  var html;

  if (startsWith(lexem.id, '@')) {
    // don't show an edit link for soon-to-be created lexems
    html = lexem.text;
  } else {
    html = lexem.text +
      ' <a class="glyphicon glyphicon-pencil" href="' + wwwRoot +
      'admin/lexemEdit.php?lexemId=' + lexem.id + '"></a>';
  }

  if ((lexem.consistentAccent == '0') ||
      (lexem.hasParadigm === false) ||
      (elementData.consistentAccent === '0') ||
      (elementData.hasParadigm === false)) {
    return $('<span class="text-danger">' + html + '</span>');
  } else {
    return $('<span>' + html + '</span>');
  }
}

function formatEntryWithEditLink(entry) {
  if (startsWith(entry.id, '@')) {
    // don't show an edit link for soon-to-be created entries
    var link = '';
  } else {
    var link = ' <a class="glyphicon glyphicon-pencil" href="' + wwwRoot +
      'editEntry.php?id=' + entry.id + '"></a>';
  }

  return $('<span>' + entry.text + link + '</span>');
}

function allowNewOptions(data) {
  return {
    id: '@' + data.term,
    text: data.term + ' (cuvânt nou)',
  };
};

$(function() {
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

