(function(){

  // cookie definition
  var COOKIE = 'charmap';

  var CYRILLIC = [
    '---;Caractere CHIRILICE',
    'а;А', 'б;Б', 'в;В', 'г;Г', 'д;Д', 'е;Е', 'ё;Ё', 'ж;Ж', 'з;З',
    'и;И', 'й;Й', 'к;К', 'л;Л', 'м;М', 'н;Н', 'о;О', 'п;П', 'р;Р',
    'с;С', 'т;Т', 'у;У', 'ф;Ф', 'х;Х', 'ц;Ц', 'ч;Ч', 'ш;Ш', 'щ;Щ',
    'ъ;Ъ', 'ы;Ы', 'ь;Ь', 'э;Э', 'ю;Ю', 'я;Я'
  ];

  var GREEK = [
    '---;Caractere GRECEȘTI',
    'α;Α;Alfa', 'β;Β;Beta', 'γ;Γ;Gamma', 'δ;Δ;Delta', 'ε;Ε;Epsilon', 'ζ;Ζ;Zeta', 'η;Η;Eta', 'θ;Θ;Teta', 'ι;Ι;Iota',
    'κ;Κ;Kappa', 'λ;Λ;Lambda', 'μ;Μ;Miu', 'ν;Ν;Niu', 'ξ;Ξ;Csi', 'ο;Ο;Omicron', 'π;Π;Pi', 'ρ;Ρ;Ro', 'σ;Σ;Sigma',
    'τ;Τ;Tau', 'υ;Υ;Ipsilon', 'φ;Φ;Fi', 'χ;Χ;Hi', 'ψ;Ψ;Psi', 'ω;Ω;Omega'
  ];

  // default cookie value
  var DEFAULT = [].concat(CYRILLIC, GREEK);


  // character read/edit logic
  var Charmap = function() {
    this._cookie_json = $.cookie.json;
  };

  Charmap.prototype.read = function() {
    $.cookie.json = true;
    var cookie_value = $.cookie(COOKIE);
    var value = (cookie_value && cookie_value.length > 0) ? cookie_value : DEFAULT;
    $.cookie.json = this._cookie_json;
    return value;
  };

  Charmap.prototype.edit = function(value) {
    $.cookie.json = true;
    $.cookie(COOKIE, value, { expires: 36500, path: '/' });
    $.cookie.json = this._cookie_json;
  };


  // Shift key logic
  // (adapted from https://stackoverflow.com/a/11101662)
  function isShiftKey(evt) {
    return evt.keyCode === 16 || evt.charCode === 16;
  }

  function changeButtonsCase(modal, shiftDown) {
    [].slice.call(modal.querySelectorAll('.btn-charmap'))
      .forEach(function(button) {
        var new_text = shiftDown ? button.getAttribute('data-upper') : button.getAttribute('data-lower');
        button.innerText === new_text ? function(){}() : button.innerText = new_text;
        button.setAttribute('value', new_text);
      })
  }

  function listenForShiftChanged(modal) {
    document.addEventListener('keydown', function(evt) {
      isShiftKey(evt) && changeButtonsCase(modal, true);
    });
    document.addEventListener('keyup',  function(evt) {
      isShiftKey(evt) && changeButtonsCase(modal, false);
    });
  }

  // Insert character logic
	// (adapted from https://stackoverflow.com/questions/11076975/insert-text-into-textarea-at-cursor-position-javascript/41426040#41426040)
	function insertAtCursor(myField, myValue) {
    // simple input/textarea field inserter
		function _do_insert() {
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;

			myField.value = myField.value.substring(0, startPos)
				+ myValue
				+ myField.value.substring(endPos, myField.value.length);

			var pos = startPos + myValue.length;

			myField.setSelectionRange(pos, pos);
			myField.focus();
		}

		//IE support
		if (document.selection) {
			myField.focus();
			var sel = document.selection.createRange();
			sel.text = myValue;
		}

		// Microsoft Edge
		else if(window.navigator.userAgent.indexOf("Edge") > -1) {
			_do_insert();
		}

		//MOZILLA and others
		else if (myField.selectionStart || myField.selectionStart === '0') {
			_do_insert();
		}

		else {
			myField.value += myValue;
			myField.focus();
		}
	}

	function insertAtTinyMCECursor(editor, chr) {
    // tinymce inserter
		editor.insertContent(chr);
	}

  function getInserter(target) {
    var is_tinymce = target.hasClass('mce-content-body');
    return (
      is_tinymce
        ? function(chr) {
          insertAtTinyMCECursor(tinymce.activeEditor, chr);
        }
        : function(chr) {
          // target is a jQuery element,
          // insertAtCursor requires a DOM element
          // so we use .get(0).
          insertAtCursor(target.get(0), chr);
        }
    );
  }


  // dynamically built content elements
  function isSection(txt) {
    return txt.indexOf('---') === 0;
  }

  function getSection(txt) {
    return '<h3>' + txt.split(';')[1] + '</h3>';
  }

	function getButton(inserter, chr) {
    var props = chr.split(';');
    var lower = props[0];
    var upper = props[1];
    var title = props[2];

    // Default properties
    var button = document.createElement('button');
	  button.className = 'btn btn-default btn-charmap';
	  button.setAttribute('data-dismiss', 'modal');

	  button.innerText = lower;

    button.setAttribute('title', title);
	  button.setAttribute('data-lower', lower);
	  button.setAttribute('data-upper', upper || lower);
	  button.setAttribute('value', lower);

	  button.addEventListener('click', function(){
      inserter(button.getAttribute('value'));
    });

	  return button;
  }

  function getButtonContent(inserter, config){
    return config.map(
      function(entry) {
        return isSection(entry) ? getSection(entry) : getButton(inserter, entry);
      });
  }


	var MODAL; // Placeholder, set once by init
	var CHARMAP = new Charmap();

	var inserter; // Updated on each Charmap.show call.

  // called once at page load.
  function init(modal_selector) {
    var modal = $(modal_selector);

    listenForShiftChanged(modal.get(0));

    var buttons_container = $('[data-role=buttons]', modal);

    function update() {
      buttons_container.html(getButtonContent(inserter, CHARMAP.read()));
    }

    var modalControls = {
      editArea: $('[data-role=edit]', modal),
      editBox: $('#editBox', modal),
      editButton: $('#editButton', modal),
      saveButton: $('#saveButton', modal),
      resetButton: $('#resetButton', modal)
    };

    modal.on('show.bs.modal', function() {
      update();
      modalControls.editArea.hide();
      modalControls.editButton.show();
    });

    modalControls.editButton.on('click', function() {
      modalControls.editButton.hide();
      modalControls.editBox.val(CHARMAP.read().join('\n'));
      modalControls.editArea.show();
    });

    modalControls.saveButton.on('click', function() {
      var value = modalControls.editBox.val();
      var to_save = value.split(/\r\n|\r|\n/g).filter(function(val) {
        return val.trim() !== "";
      });
      CHARMAP.edit(to_save);
      update();
      modalControls.editArea.hide();
      modalControls.editButton.show();
    });

    modalControls.resetButton.on('click', function() {
      if (confirm('Confirmați resetarea glifelor la valorile inițiale?')) {
        $.removeCookie(COOKIE, {path: '/'});
        update();
        modalControls.editBox.val(CHARMAP.read().join('\n'));
      }
    });

    MODAL = modal;
  }

	function show(insert_target) {
    if (MODAL) {
      // update inserter global.
      inserter = getInserter($(insert_target));
      MODAL.modal();
    }
	}

	window.Charmap = {
		show: show,
    init: init
	};

})();
