$(function() {

  const CONTAINER_CLASS = 'sortable-container';
  const GROUP_KEY = 'sortable-group';

  // Info about the object being dragged. There can be only one regardless of
  // the number of instances.
  var dragging;
  var sourceContainer;

  $.fn.extend({
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
      .on('mouseup', sel, finish)
      .on('dragend', '> *', finish)
      .on('dragover', '> *', dragOver)
      .on('dragstart', '> *', dragStart);
  }

  function mouseDown(e) {
    dragging = $(this).closest('.' + CONTAINER_CLASS + ' > *');
    dragging.attr('draggable', 'true').css('opacity', '0.5');
    sourceContainer = $(this).closest('.' + CONTAINER_CLASS);
  }

  /**
   * Same code (but different targets) for mouseup and dragend. Note that
   * mouseup does not fire when there is a drag.
   */
  function finish() {
    var child = $(this).closest('.' + CONTAINER_CLASS + ' > *');
    child.removeAttr('draggable').css('opacity', '');
    dragging = sourceContainer = null;
  }

  function dragStart(e) {
    e.originalEvent.dataTransfer.effectAllowed = 'move';
  }

  function dragOver(e) {
    var t = $(e.currentTarget);
    var container = t.closest('.' + CONTAINER_CLASS);
    var cgroup = container.data(GROUP_KEY);

    // Since the event was called, we know this is a sortable container.
    // Check if the container or group is acceptable.
    if (!container.is(sourceContainer) &&
        (!cgroup || (cgroup != sourceContainer.data(GROUP_KEY)))) {
      return;
    }

    if (!t.is(dragging)) { // not the same object
      if (isBefore(dragging, t)) {
        dragging.insertAfter(t);
      } else {
        dragging.insertBefore(t);
      }
    }
  }

  function isBefore(a, b) {
    while (a.length && !a.is(b)) {
      a = a.next();
    }
    return a.length;
  }

});
