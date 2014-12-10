$().ready(function() {
  /* Add our own icon. Kudos <http://stackoverflow.com/questions/16604842/adding-a-custom-context-menu-item-to-elfinder/> */
  elFinder.prototype.i18.en.messages['cmdeditimage'] = 'Msg1';
  elFinder.prototype._options.commands.push('editimage');
  elFinder.prototype.commands.editimage = function() {
    this.exec = function(hashes) {
      alert('alert1');
    }
    this.getstate = function() {
      //return 0 to enable, -1 to disable icon access
      var sel = this.files(sel),
      cnt = sel.length;
      return !this._disabled && cnt ? 0 : -1;
    }
  }

  $('#fileManager').elfinder({
    url: '../elfinder-connector/visual_connector.php',
    lang: 'en',

    uiOptions: {
      toolbar: [
        ["mkdir","upload"],
        ["open","download","getfile"],
        ["info"],
        ["quicklook"],
        ["copy","cut","paste"],
        ["rm"],
        ["duplicate","rename"],
        ["search"],
        ["view","sort"],
        ["help"],
        ['editimage'],],
    },
    // contextmenu: {
    //   // current directory file menu
    //   files: [
    //     'getfile', '|','open', 'quicklook', 'editimage',
    //   ]
    // },
  }).elfinder('instance');
});
