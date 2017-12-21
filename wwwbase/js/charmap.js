(function(){

	var COOKIE = 'charmap';

	var DEFAULT = ['á', 'à', 'ä'];
	var BUTTON = '<button class="btn btn-default" data-dismiss="modal">';


	// character read/edit logic
	var Charmap = function() {
		this._cookie_json = $.cookie.json;
	}

	Charmap.prototype.read = function() {
		$.cookie.json = true;
		var value = $.cookie(COOKIE) || [];
		$.cookie.json = this._cookie_json;
		return value;
	}

	Charmap.prototype.edit = function(value) {
		$.cookie.json = true;
		$.cookie(COOKIE, value, { expires: 36500 });
		$.cookie.json = this._cookie_json;
	}

	Charmap.prototype.all = function() {
		return DEFAULT.concat(this.read());
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
			target.val(target.val() + chr);
		});

		return button;
	}


	// modal display and logic
	var CharmapModal = function(target, charmap, buttons) {
		this.target = $(target);
		this.charmap = charmap;
		this.buttons = buttons;

		this.editArea = $('[data-role=edit]', this.target);
		this.editArea.hide();

		this.editBox = $('#editBox', this.target);
		this.editButton = $('#editButton', this.target).on('click', this.edit.bind(this));
		this.saveButton = $('#saveButton', this.target).on('click', this.save.bind(this));

		this.update();
	}

	CharmapModal.prototype.update = function() {
		$('[data-role=buttons]', this.target)
			.html(this.buttons.buttons(this.charmap.all()));
	}

	CharmapModal.prototype.show = function() {
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
