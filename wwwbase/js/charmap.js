(function(){

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
		else if (myField.selectionStart || myField.selectionStart == '0') {
			_do_insert();
		}

		else {
			myField.value += myValue;
		}
	}

	var COOKIE = 'charmap';

	var DEFAULT = ['á', 'à', 'ä'];
	var BUTTON = '<button class="btn btn-default" data-dismiss="modal">';


	// character read/edit logic
	var Charmap = function() {
		this._cookie_json = $.cookie.json;
	}

	Charmap.prototype.read = function() {
		$.cookie.json = true;
		var cookie_value = $.cookie(COOKIE);
		var value = (cookie_value && cookie_value.length > 0) ? cookie_value : DEFAULT;
		$.cookie.json = this._cookie_json;
		return value;
	}

	Charmap.prototype.edit = function(value) {
		$.cookie.json = true;
		$.cookie(COOKIE, value, { expires: 36500, path: '/' });
		$.cookie.json = this._cookie_json;
	}

	// charmap buttons
	var CharmapButtons = function(target) {
		this.target = target;
	}

	CharmapButtons.prototype.buttons = function(chars) {
		return chars.map(this.button.bind(this));
	}

	CharmapButtons.prototype.button = function(chr) {
		var button = $(BUTTON);
		button.text(chr);

		var target = this.target;
		button.on('click', function() {
			// target is a jQuery element,
			// insertAtCursor requires a DOM element
			// so we use .get(0).
			insertAtCursor(target.get(0), chr);
		});

		return button;
	}


	// modal display and logic
	var CharmapModal = function(target, charmap, buttons) {
		this.target = $(target);
		this.charmap = charmap;
		this.buttons = buttons;

		this.editArea = $('[data-role=edit]', this.target);

		this.editBox = $('#editBox', this.target);
		this.editButton = $('#editButton', this.target).on('click', this.edit.bind(this));
		this.saveButton = $('#saveButton', this.target).on('click', this.save.bind(this));

		this.update();
	}

	CharmapModal.prototype.update = function() {
		$('[data-role=buttons]', this.target)
			.html(this.buttons.buttons(this.charmap.read()));
	}

	CharmapModal.prototype.show = function() {
		// ensure initial state
		this.editArea.hide();
		this.editButton.show();

		this.target.modal();
	}

	CharmapModal.prototype.edit = function() {
		this.editButton.hide();
		this.editBox.val(this.charmap.read().join('\n'));
		this.editArea.show();
	}

	CharmapModal.prototype.save = function() {
		var value = this.editBox.val();
		var to_save = value.split(/\r\n|\r|\n/g).filter(function(val) {
			return val.trim() !== "";
		});
		this.charmap.edit(to_save);
		this.update();
		this.editArea.hide();
		this.editButton.show();
	}

	var CHARMAP = new Charmap();
	var show = function(sel_modal, insert_target) {
		var buttons = new CharmapButtons($(insert_target));
		var modal = new CharmapModal(sel_modal, CHARMAP, buttons);
		modal.show();
	}

	window.Charmap = {
		show: show,
	};

})();