$(function() {

  const CONTAINER_CLASS = 'sortable-container';

  $.fn.extend({
    sortable: function() {
      this.each(init);
      return this;
    }
  });

  function init() {
    var sel = $(this).data('handle') || '> *';
    $(this)
      .addClass(CONTAINER_CLASS)
      .on('mousedown', sel, mouseDown)
      .on('mouseup', sel, finish)
      .on('dragend', '> *', finish)
      .on('dragover', '> *', dragOver)
      .on('dragstart', '> *', dragStart);
  }

  function mouseDown(e) {
    var child = $(this).closest('.' + CONTAINER_CLASS + ' > *');
    child.attr('draggable', 'true').css('opacity', '0.5');
    $(this).closest('.' + CONTAINER_CLASS).data('dragging', child);
  }

  /**
   * Same code (but different targets) for mouseup and dragend. Note that
   * mouseup does not fire when there is a drag.
   */
  function finish() {
    var child = $(this).closest('.' + CONTAINER_CLASS + ' > *');
    child.removeAttr('draggable').css('opacity', '');
    $(this).closest('.' + CONTAINER_CLASS).removeData('dragging');
  }

  function dragStart(e) {
    e.originalEvent.dataTransfer.effectAllowed = 'move';
  }

  function dragOver(e) {
    var t = $(e.currentTarget);
    var dragging = t.closest('.' + CONTAINER_CLASS).data('dragging');
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
