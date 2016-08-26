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
      $(sel).select2(options);
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

function createUserAjaxStruct(priv = 0) {
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
    return $('<span class="select2LexemWarnings">' + html + '</span>');
  } else {
    return $('<span>' + html + '</span>');
  }
}

function formatEntryWithEditLink(lexem) {
  return $('<span>' + lexem.text +
           ' <a class="glyphicon glyphicon-pencil" href="' + wwwRoot +
           'editEntry.php?id=' + lexem.id + '"></a></span>');
}

function allowNewOptions(data) {
  return {
    id: '@' + data.term,
    text: data.term + ' (cuvânt nou)',
  };
};

function adminIndexInit() {
  $('#lexemId').select2({
    ajax: {
      url: wwwRoot + 'ajax/getLexems.php',
    },
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
    width: '300px',
  }).on('change', function(e) {
    $(this).parents('form').submit();
  });

  $('#definitionId').select2({
    ajax: { url: wwwRoot + 'ajax/wotdGetDefinitions.php', },
    templateResult: function(item) {
      return item.text + ' (' + item.source + ') [' + item.id + ']';
    },
    minimumInputLength: 1,
    placeholder: 'caută o definiție',
    width: '300px',
  }).on('change', function(e) {
    $(this).parents('form').submit();
  });

  $('#structuristId').select2({
    ajax: createUserAjaxStruct(PRIV_STRUCT),
    allowClear: true,
    minimumInputLength: 1,
    placeholder: '(opțional)',
    width: '100%',
  });
}
