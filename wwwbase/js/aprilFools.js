(function(){

  var body = document.body;

  var soundPath = document.getElementById('aprilFools').getAttribute('data-sound');
  var typingSound = new Audio(soundPath);

  var overlay = document.createElement('div');
  overlay.setAttribute('id', 'aprilFoolsOverlay');

  var typeInto = document.createElement('p');
  overlay.appendChild(typeInto);

  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';


  function playSound(chr) {
    typingSound.pause();
    typingSound.currentTime = 0;
    typingSound.play();
  };


  var typewriter = new Typewriter(typeInto);
  typewriter.setCaret("");
  typewriter.setCaretPeriod(0);
  typewriter.setDelay(500, 100);
  typewriter.setCharacterCallback(playSound);
  typewriter.setCompletionCallback(function(){
    document.body.removeChild(overlay);
    document.body.style.overflow = '';
  });
  typewriter.typeText(
   "Momentan toate dactilografele noastre sunt ocupate. Căutarea dumneavoastră este importantă pentru noi."
  )

})();
