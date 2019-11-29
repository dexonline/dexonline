var Romanian = {};
Romanian.rom_map = {
  'á' : 'a',
  'Á' : 'a',
  'ắ' : 'ă',
  'Ắ' : 'ă',
  'ấ' : 'â',
  'Ấ' : 'â',
  'é' : 'e',
  'É' : 'e',
  'í' : 'i',
  'Í' : 'i',
  'î́' : 'î',
  'Î́' : 'î',
  'ó' : 'o',
  'Ó' : 'o',
  'ú' : 'u',
  'Ú' : 'u',
  'ý' : 'y',
  'Ý' : 'y',
};

String.prototype.romanianise = function() {
  return this.replace(/[^A-Za-z0-9\[\] ]/g, function(x) { return Romanian.rom_map[x] || x; });
};

String.prototype.isRomanian = function() {
  return this == this.romanianise();
};
