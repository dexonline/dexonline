$(function() {
  $(document).ajaxSend(function( event, request, settings ) {
    if (settings.isLocal) settings.context.addClass('running');
  });
  $(document).ajaxComplete(function( event, request, settings ) {
    if (settings.isLocal) settings.context.removeClass('running');
  });
});
