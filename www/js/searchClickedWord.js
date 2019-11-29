var patternFragment = RegExp('/^[~-]|[~-]$|\d+/', 'gm');

function searchClickedWord(event) {
  if ($(event.target).is('abbr')) return false;
  var s = window.getSelection();

  if (!s.isCollapsed) return false;
  var begin = /^\s/;
  var end = /\s$/;

  var d = document,
    nA = s.anchorNode,
    oA = s.anchorOffset,
    nF = s.focusNode,
    oF = s.focusOffset,
    range = d.createRange();

  range.setStart(nA,oA);
  range.setEnd(nF,oF);

  // Extend range to the next space or end of node
  while(range.endOffset < range.endContainer.textContent.length &&
        !end.test(range.toString())){
      range.setEnd(range.endContainer, range.endOffset + 1);
        }
  // Extend range to the previous space or start of node
  while(range.startOffset > 0 &&
        !begin.test(range.toString())){
          range.setStart(range.startContainer, range.startOffset - 1);
        }

  // Remove spaces
  if(end.test(range.toString()) && range.endOffset > 0)
    range.setEnd(range.endContainer, range.endOffset - 1);
  if(begin.test(range.toString()))
    range.setStart(range.startContainer, range.startOffset + 1);

  // Assign range to selection
  //s.addRange(range);

  var word = range.toString().replace(/[.,„”\/#!$%\^&\*;:{}=\_`\[\]()]/g,"");

  var errors =
    patternFragment.test(word) === true || word.length === 0 ;

  if (!errors) {
    window.location = wwwRoot + 'definitie' + source + '/'
      + encodeURIComponent(word.romanise().toLowerCase());
  }
}
