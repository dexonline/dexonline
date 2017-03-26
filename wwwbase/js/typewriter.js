(function(){
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
    return function(next_idx, all) {
      typewriter.setCompletionCallback(function(){
        var next_next = next_idx + 1;
        if (next_next <= all.length) {
          all[next_idx](next_idx+1, all);
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

  function shouldRun(){
    var noCookie = document.cookie.indexOf('typewriterRan=true') === -1;
    var now = new Date();
    var goodDate = now.getDate() === 1 && now.getMonth() === 3; // months start at 0
    return noCookie && goodDate;
  }

  if (true || shouldRun()) {
    var rootElements = [].slice.call(document.querySelectorAll('p.def'));
    rootElements.forEach(function(rootElement) {
      var walker = walkerFactory(rootElement);
      var textNodes = nodesFromWalker(walker);
      var typeWriters = textNodes.map(typewriterFactory);
      typeWriters[0](1, typeWriters);
    });

    document.cookie = "typewriterRan=true; expires=" + getExpirationDate() + "; path=/";
  }

})();
