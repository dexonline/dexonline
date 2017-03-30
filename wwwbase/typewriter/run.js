(function(){

  var App = window.typewriter = {};

  function keySounds(soundPath) {
    var appendPath = function(fileName) { return soundPath + fileName; }
    var fileNameFromIdx = function(idx) { return 'key_' + idx + '.mp3'; }
    return [0, 1, 2, 3, 4].map(fileNameFromIdx).map(appendPath);
  };

  var COOKIE_DEFINITIONS = "typewriterRanDefinitions";
  var COOKIE_OVERLAY = "typewriterRanOverlay";

  var ELEM_OVERLAY_ID = "aprilFoolsOverlay";
  var ELEM_OVERLAY_TARGET_ID = "aprilFoolsOverlayTarget";

  var CSS_TYPETARGET = "typewriterTarget";
  var SOUND_BASEPATH = document.getElementById('aprilFools').getAttribute('data-sound');
  var KEY_SOUNDS = keySounds(SOUND_BASEPATH);
  var SOUND_CR = new Audio(SOUND_BASEPATH + 'cr.mp3');

  var TEXT_OVERLAY = [
    "Momentan toate dactilografele noastre sunt ocupate.",
    "Vă rugăm așteptați.",
    "Căutarea dumneavoastră este importantă pentru noi.",
  ];

  function setupOverlay() {
    var overlay = document.createElement('div');
    overlay.setAttribute('id', ELEM_OVERLAY_ID);
    overlay.classList.add(CSS_TYPETARGET);

    var typeInto = document.createElement('p');
    typeInto.setAttribute('id', ELEM_OVERLAY_TARGET_ID);
    overlay.appendChild(typeInto);

    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    return overlay;
  }

  function teardownOverlay(overlay) {
    document.body.removeChild(overlay);
    document.body.style.overflow = '';
  }

  function playSound(sound) {
    sound.pause();
    sound.currentTime = 0;
    sound.play();
  }

  function playRandomKeySound(chr) {
    var sndIdx = Math.floor(Math.random() * keySounds.length);
    playSound(new Audio(KEY_SOUNDS[sndIdx]));
  };

  function setCookie(name) {
    document.cookie = name + "=true; expires=" + getExpirationDate() + "; path=/";
  };

  function walkerFactory(rootElement) {
    return document.createTreeWalker(
      rootElement,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode: function(node){
          var content = node.nodeValue.trim().length;
          return content > 0 ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
        }
      }
    )
  };

  function nodesFromWalker(walker) {
    var node, nodes = [];
    while(node = walker.nextNode()) { nodes.push(node); }
    return nodes;
  };

  function typewriterFactory(node) {
    var text = node.nodeValue;
    node.nodeValue = "";
    var typewriter = new Typewriter(node);
    typewriter.setDelay(150, 50);
    return function(next_idx, all) {
      typewriter.setCompletionCallback(function(){
        var next_next = next_idx + 1;
        if (next_next <= all.length) {
          all[next_idx](next_next, all);
        }
      });
      typewriter.typeText(text);
    }
  }

  function getExpirationDate() {
    var now = new Date();
    now.setHours(now.getHours() + 4);
    return now.toUTCString();
  };

  function shouldRun(cookieName){
    var noCookie = document.cookie.indexOf(cookieName + '=true') === -1;
    return noCookie;
  }

  function runGuard(cookieName, func, funcCallback) {
    var runner;
    if (shouldRun(cookieName)) {
      runner = function() { return func(funcCallback); };
    }
    else {
      runner = function(){ return funcCallback ? funcCallback() : undefined; };
    }
    return runner;
  }

  function runDefinitions() {
    var rootElements = [].slice.call(document.querySelectorAll('.defWrapper p.def'));

    // Create a new element so we can output typing sounds.
    // Doesn't have to be added to the DOM.
    var fooElem = document.createElement('pre');
    soundTyper = new Typewriter(fooElem);
    soundTyper.setDelay(150, 50);
    soundTyper.setCharacterCallback(playRandomKeySound);
    soundTyper.typeText(rootElements[0].innerText);

    // Create typewriters for each element's text nodes.
    rootElements.forEach(function(rootElement) {
      rootElement.classList.add(CSS_TYPETARGET);
      var walker = walkerFactory(rootElement);
      var textNodes = nodesFromWalker(walker);
      var typeWriters = textNodes.map(typewriterFactory);
      typeWriters[0](1, typeWriters);
    });

    if (rootElements.length > 0) { setCookie(COOKIE_DEFINITIONS); };
  };

  function runOverlay(doneCallback) {

    var overlay = setupOverlay();
    var typeInto = document.getElementById('aprilFoolsOverlayTarget');

    function newTypewriter(elem) {
      var typewriter = new Typewriter(elem);
      typewriter.setCaret("");
      typewriter.setCaretPeriod(0);
      typewriter.setDelay(150, 50);
      typewriter.setCharacterCallback(playRandomKeySound);
      return typewriter;
    }

    function typeLineFactory(text) {
      var target = document.createElement('p');
      var typewriter = newTypewriter(target);
      typeInto.appendChild(target);
      return function(next, all) {
        typewriter.setCompletionCallback(function(){
          playSound(SOUND_CR);
          function runNext(){
            var next_next = next + 1;
            if (next_next <= all.length) {
              all[next](next_next, all);
            }
            else {
              teardownOverlay(overlay);
              doneCallback ? doneCallback() : undefined;
            }
          }
          window.setTimeout(runNext, 3000);
        });
        typewriter.typeText(text);
      }
    }

    var typers = TEXT_OVERLAY.map(typeLineFactory);
    typers[0](1, typers);

    setCookie(COOKIE_OVERLAY);
  }

  App.runOverlay = runOverlay;
  App.runDefinitions = runDefinitions;

  var guardedRunOverlay = runGuard(COOKIE_OVERLAY, runOverlay);
  var guardedRunDefinitions = runGuard(COOKIE_DEFINITIONS, runDefinitions);

  App.guardedRunDefinitions = guardedRunDefinitions;
  App.guardedRunOverlay = guardedRunOverlay;

  App.guardedRunAll = function() {
    runGuard(COOKIE_OVERLAY, runOverlay, function(){
      runGuard(COOKIE_DEFINITIONS, runDefinitions)();
    })();
  };

})();
