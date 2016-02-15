$(function() {
  var menuBar = null;
  var stemLi = null;
  var sel = null; // selected <li>

  function init() {
    $('#mtTree li, #stem').click(tagClick);
    $('#butUp').click(moveTagUp);
    $('#butDown').click(moveTagDown);
    $('#butLeft').click(moveTagLeft);
    $('#butRight').click(moveTagRight);
    $('#butAddSibling').click(addSibling);
    $('#butAddChild').click(addChild);
    $('#butDelete').click(deleteTag);
    $('#butSave').click(saveTree);
    menuBar = $('#menuBar').detach();
    stemLi = $('#stem').detach().removeAttr('id');
  }

  function tagClick(e) {
    if ($(e.target).is('button, input')) { // ignore button and input clicks
      return false;
    }
    e.stopPropagation();
    endEdit();
    sel = $(this);
    sel.addClass('selected');
    var value = sel.find('> .value').hide();
    menuBar.insertAfter(value).show();
    menuBar.find('#valueBox').val(value.text()).focus();
  }

  /* End the previous edit, if any */
  function endEdit() {
    if (sel) {
      var value = menuBar.prev();
      value.text($('#valueBox').val()).show();
      menuBar.detach();
      sel.removeClass('selected');
      sel = null;
    }
  }

  function moveTagUp() {
    sel.insertBefore(sel.prev());
  }

  function moveTagDown() {
    sel.insertAfter(sel.next());
  }

  function moveTagLeft() {
    var parentLi = sel.parent().parent('li');
    if (parentLi.length) {
      sel.insertAfter(parentLi);
    }
  }

  function moveTagRight() {
    if (sel.prev().length) {
      var ul = ensureUl(sel.prev());
      sel.appendTo(ul);
    }
  }

  function addSibling() {
    var newNode = stemLi.clone(true);
    newNode.insertAfter(sel).click();
  }

  function addChild() {
    var ul = ensureUl(sel);
    var newNode = stemLi.clone(true);
    newNode.appendTo(ul).click();
  }

  // Ensures the node has a <ul> child, creates it if it doesn't, and returns the <ul> child.
  function ensureUl(node) {
    if (!node.children('ul').length) {
      node.append('<ul></ul>');
    }
    return node.children('ul');
  }

  function deleteTag() {
    var blockers = sel.find('[data-can-delete=0]');
    if (blockers.length) {
      alert('Nu pot șterge această etichetă, deoarece ea sau unele descendente ale ei sunt aplicate pe sensuri.');
    } else {
      var toDelete = sel;
      endEdit();
      toDelete.remove();
    }
  }

  function validate() {
    var empty = $('.value').filter(function() {
      return $(this).text() == '';
    });
    if (empty.length) {
      alert('Textul etichetelor nu poate fi vid.');
      return false;
    }
    return true;
  }

  function saveTree() {
    var results = [];
    endEdit();

    if (!validate()) {
      return false;
    }

    $('.value').each(function(i) {
      var parent = $(this).parent().parent().prev();
      var parentId = parent.is('.value') ? parent.data('id') : 0;
      results.push({
        id: $(this).data('id'),
        value: $(this).text(),
        parentId: parentId,
      });
    });
    $('input[name=jsonTags]').val(JSON.stringify(results));
    return true;
  }

  init();
});
