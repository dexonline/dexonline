$(function() {

  const CONTAINER_CLASS = 'sortable-container';
  const GROUP_KEY = 'sortable-group';

  /**
   * Info about the element being dragged:
   *   - the element itself;
   *   - its source container;
   *   - its index within the source container.
   *
   * Note: Only one object can be dragged at a time, regardless of the number
   * of instances. Hence we can store this information in a module variable.
   */
  var moving = {};

  /**
   * Keep track of mouse coords during drag. Do this at body level. Firefox
   * won't provide the coords at dragend time:
   * https://stackoverflow.com/q/11656061/6022817
   **/
  var coords = {};
  $(document).on('dragover', function(e) {
    coords = { x: e.clientX, y: e.clientY };
  });

  $.fn.extend({
    insertAt: function($parent, index) {
      return this.each(function() {
        if (index === 0) {
          $parent.prepend(this);
        } else {
          $parent.children().eq(index - 1).after(this);
        }
      });
    },
    sortable: function(opts = {}) {
      this.each(function() {
        init($(this), opts);
      });
      return this;
    }
  });

  function init(obj, opts = {}) {
    // items can be dragged between containers in the same group
    var group = opts.group || obj.data('group') || null;

    // if a handle is given, items can only be dragged when grabbed by the handle
    var sel = opts.handle || obj.data('handle') || '> *';

    obj
      .addClass(CONTAINER_CLASS)
      .data(GROUP_KEY, group)
      .on('mousedown', sel, mouseDown)
      .on('mouseup', sel, mouseUp)
      .on('dragend', '> *', dragEnd)
      .on('dragover', '> *', dragOver)
      .on('dragstart', '> *', dragStart);
  }

  function mouseDown(e) {
    var child = $(this)
        .closest('.' + CONTAINER_CLASS + ' > *')
        .attr('draggable', 'true')
        .css('opacity', '0.5');

    moving = {
      item: child,
      index: child.index(),
      src: $(this).closest('.' + CONTAINER_CLASS),
    };
  }

  function dragStart(e) {
    e.originalEvent.dataTransfer.effectAllowed = 'move';
  }

  function dragOver(e) {
    if (!isValidContainer(e.currentTarget)) {
      return false;
    }

    var t = $(e.currentTarget);

    if (!t.is(moving.item)) { // not the same object
      if (isBefore(moving.item, t)) {
        moving.item.insertAfter(t);
      } else {
        moving.item.insertBefore(t);
      }
    }
  }

  /**
   * Returns true iff el is an element inside an appropriate container for the
   * current drag.
   */
  function isValidContainer(el) {
    var container = $(el).closest('.' + CONTAINER_CLASS);
    var group = container.data(GROUP_KEY);

    if (!container.length) {
      return false; // not a container at all
    }

    if (container.is(moving.src)) {
      return true; // we're in the original container
    }

    if (!group) {
      return false; // current container isn't part of any group
    }

    if (group == moving.src.data(GROUP_KEY)) {
      return true; // current container is in the correct group
    }

    return false;
  }

  function isBefore(a, b) {
    while (a.length && !a.is(b)) {
      a = a.next();
    }
    return a.length;
  }

  function cleanup(el) {
    var child = $(el).closest('.' + CONTAINER_CLASS + ' > *');
    child.removeAttr('draggable').css('opacity', '');
    moving = {};
  }

  function cancel() {
    moving.item.detach().insertAt(moving.src, moving.index);
  }

  function mouseUp(e) {
    cleanup(this);
  }

  function dragEnd(e) {
    var over = document.elementFromPoint(coords.x, coords.y);
    if (!isValidContainer(over)) {
      cancel();
    }
    cleanup(this);
  }

});
