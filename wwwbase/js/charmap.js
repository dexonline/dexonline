(function(){

  var shiftDownEvt = new Event('ShiftDown');
  var shiftUpEvt = new Event('ShiftUp');

  // taken from https://stackoverflow.com/a/11101662
  var shiftDown = false;
  var setShiftDown = function(event){
    if(event.keyCode === 16 || event.charCode === 16){
      shiftDown = true;
      window.dispatchEvent(shiftDownEvt);
    }
  };

  var setShiftUp = function(event){
    if(event.keyCode === 16 || event.charCode === 16){
      shiftDown = false;
      window.dispatchEvent(shiftUpEvt);
    }
  };

  window.addEventListener? document.addEventListener('keydown', setShiftDown) : document.attachEvent('keydown', setShiftDown);
  window.addEventListener? document.addEventListener('keyup', setShiftUp) : document.attachEvent('keyup', setShiftUp);


	// adapted from https://stackoverflow.com/questions/11076975/insert-text-into-textarea-at-cursor-position-javascript/41426040#41426040
	function insertAtCursor(myField, myValue) {

		function _do_insert() {
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;

			myField.value = myField.value.substring(0, startPos)
				+ myValue
				+ myField.value.substring(endPos, myField.value.length);

			var pos = startPos + myValue.length;

			myField.focus();
			myField.setSelectionRange(pos, pos);
		}

		//IE support
		if (document.selection) {
			myField.focus();
			sel = document.selection.createRange();
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
		}
	}

	function insertAtTinyMCECursor(editor, chr) {
		editor.insertContent(chr);
	}

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

  var DEFAULT = [].concat(CYRILLIC, GREEK);

	var BUTTON = '<button class="btn btn-default btn-charmap" data-dismiss="modal">';

  function getSection(txt) {
    return '<h3>' + txt.split(';')[1] + '</h3>';
  }

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

	// charmap buttons
	var CharmapButtons = function(target) {
		var is_tinymce = target.hasClass('mce-content-body');
		this.inserter = (
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
	};

	CharmapButtons.prototype.buttons = function(chars) {
		return chars.map(this.button.bind(this));
	};

	CharmapButtons.prototype.button = function(chr) {
    var split = chr.split(';');
    var lower = split[0];
    var upper = split[1];
    var title = split[2];

		var button = $(BUTTON);
		button.text(lower);
		button.attr('title', title);

		var inserter = this.inserter;
		button.on('click', function() {
		  var insert_chr = shiftDown ? upper : lower;
		  inserter(insert_chr);
		});

    window.addEventListener('ShiftDown', function() {
      button.text() === upper ? function(){}() : button.text(upper); });

    window.addEventListener('ShiftUp', function(){
      button.text() === lower ? function(){}() : button.text(lower); });

		return button;
	};

	function isSection(txt) {
	  return txt.indexOf('---') === 0;
  }

	// modal display and logic
	var CharmapModal = function(target, charmap, buttons) {
		this.target = $(target);
		this.charmap = charmap;
		this.buttons = buttons;

		this.editArea = $('[data-role=edit]', this.target);

		this.editBox = $('#editBox', this.target);
		this.editButton = $('#editButton', this.target).on('click', this.edit.bind(this));
		this.resetButton = $('#resetButton', this.target).on('click', this.reset.bind(this));
		this.saveButton = $('#saveButton', this.target).on('click', this.save.bind(this));

		this.update();
	};

	CharmapModal.prototype.update = function() {
	  var content = this.charmap.read().map(
	    function(entry) {
	      return isSection(entry)
          ? getSection(entry)
          : this.buttons.button(entry);
	    }.bind(this));
		$('[data-role=buttons]', this.target)
			.html(content);
	};

	CharmapModal.prototype.show = function() {
		// ensure initial state
		this.editArea.hide();
		this.editButton.show();

		this.target.modal();
	};

	CharmapModal.prototype.edit = function() {
		this.editButton.hide();
		this.editBox.val(this.charmap.read().join('\n'));
		this.editArea.show();
	};

	CharmapModal.prototype.reset = function() {
    if (confirm('Confirmați resetarea glifelor la valorile inițiale?')) {
		  $.removeCookie(COOKIE, { path: '/' });
      this.update();
      this.edit();
    }
	};

	CharmapModal.prototype.save = function() {
		var value = this.editBox.val();
		var to_save = value.split(/\r\n|\r|\n/g).filter(function(val) {
			return val.trim() !== "";
		});
		this.charmap.edit(to_save);
		this.update();
		this.editArea.hide();
		this.editButton.show();
	};

	var CHARMAP = new Charmap();
	var show = function(sel_modal, insert_target) {
		var buttons = new CharmapButtons($(insert_target));
		var modal = new CharmapModal(sel_modal, CHARMAP, buttons);
		modal.show();
	};

	window.Charmap = {
		show: show
	};

})();
