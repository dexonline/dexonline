$(function() {
  var menuBar = null;
  var stemLi = null;
  var sel = null; // selected <li>

  function init() {
    $('.expand').click(toggleSubtree);
    $('#tagTree li, #stem').click(tagClick);
    $('#butUp').click(moveTagUp);
    $('#butDown').click(moveTagDown);
    $('#butLeft').click(moveTagLeft);
    $('#butRight').click(moveTagRight);
    $('#butAddSibling').click(addSibling);
    $('#butAddChild').click(addChild);
    $('#butDelete').click(deleteTag);
    $('#butDetails').click(redirectToDetails);
    $('#butSave').click(saveTree);
    menuBar = $('#menuBar').detach();
    stemLi = $('#stem').detach().removeAttr('id');

    // collapse all subtrees
    $('#tagTree ul').hide();
  }

  function toggleSubtree(e) {
    e.stopPropagation();
    var li = $(this).parent();
    if ($(this).hasClass('closed')) {
      openSubtree(li);
    } else if ($(this).hasClass('open')) {
      closeSubtree(li);
    }
  }

  function openSubtree(li, args) {
    li.children('.expand').removeClass('closed glyphicon-plus').addClass('open glyphicon-minus');
    li.children('ul').stop().slideDown(args);
  }

  function closeSubtree(li, args) {
    li.children('.expand').removeClass('open glyphicon-minus').addClass('closed glyphicon-plus');
    li.children('ul').stop().slideUp(args);
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

    $('#butDetails').prop('disabled', !value.text());
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
      var numChildren = parentLi.find('> ul > li').length;
      if (!numChildren) {
        parentLi.find('.expand').removeClass('open glyphicon-minus');
      }
    }
  }

  function moveTagRight() {
    var parentLi = sel.prev();
    if (parentLi.length) {
      var ul = ensureUl(parentLi);
      openSubtree(parentLi, {
        complete: function() {
          sel.appendTo(ul);
        },
      });
    }
  }

  function addSibling() {
    var newNode = stemLi.clone(true);
    newNode.insertAfter(sel).click();
  }

  function addChild() {
    var ul = ensureUl(sel);
    openSubtree(sel);
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
      alert('Nu pot șterge această etichetă, deoarece ea sau unele descendente ale ei sunt folosite.');
    } else {
      var toDelete = sel;
      var parentLi = sel.parent().parent('li');
      var numSiblings = sel.siblings().length;
      endEdit();
      toDelete.remove();

      if (parentLi.length && !numSiblings) {
        parentLi.find('.expand').removeClass('open glyphicon-minus');
      }
    }
  }

  function redirectToDetails() {
    var id = sel.find('> .value').data('id');
    var url = $(this).data('href') + id;
    window.location = url;
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
      var level = $(this).parentsUntil($('#tagTree'), 'li').length;
      results.push({
        id: $(this).data('id'),
        value: $(this).text(),
        level: level,
      });
    });

    $('input[name=jsonTags]').val(JSON.stringify(results));
    return true;
  }

  init();
});
