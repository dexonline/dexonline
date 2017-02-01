$(function() {
  var CTA_COOKIE = 'hideCallToAction';
  var CTA_COOKIE_DURATION = 8; // in hours

  function init() {
    $('.callToActionHide').click(setCallToActionCookie);
  }

  function setCallToActionCookie() {
    var date = new Date();
    date.setTime(date.getTime() + (CTA_COOKIE_DURATION * 3600 * 1000));
    $.cookie(CTA_COOKIE, 'on', { expires: date, path: '/' });
  }

  init();
});
