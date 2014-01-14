/*jquery.WM.js - Window Manager for jQuery.
 * Copyright (c) 2009 Philip Collins
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * modified by cata -- do not replace without diffing
 */
(function($){
  var $win,lastMouseX,lastMouseY,zIndex=10,minH,minW=160,newWinOffset=50;

  var isIE = navigator.userAgent.match(/MSIE/);

  var template = '<div class=window><div class=windowtitlebar><img src=/favicon.ico width=16 height=16 class=titlebaricon><div class=titlebartext></div><div class=horizbuts><div class=minimizebut title=minimize></div><div class=restorebut title=restore></div><div class=maximizebut title=maximize></div><div class=closebut title=close></div></div></div><div class=windowcontent></div><div class=resizer-tl></div><div class=resizer-t></div><div class=resizer-tr></div><div class=resizer-r></div><div class=resizer-br></div><div class=resizer-b></div><div class=resizer-bl></div><div class=resizer-l></div></div>';

  var resizerP = $('<div id=resizerproxy>').mousemove(function(e){
    if (resizeMask & 1) {
      resizerPos.top += (lastMouseY-e.pageY) * -1;
      var a=resizerPos.parentH-resizerPos.top-resizerPos.bottom-minH;
      if (a<0) resizerPos.top+=a;
    }
    else if (resizeMask & 4) {
      resizerPos.bottom += (lastMouseY-e.pageY);
      var a=resizerPos.parentH-resizerPos.top-resizerPos.bottom-minH;
      if (a<0) resizerPos.bottom+=a;
    }
    if (resizeMask & 8) {
      resizerPos.left += (lastMouseX-e.pageX) * -1;
      var a=resizerPos.parentW-resizerPos.left-resizerPos.right-minW;
      if (a<0) resizerPos.left+=a;
    }
    else if (resizeMask & 2) {
      resizerPos.right += (lastMouseX-e.pageX);
      var a=resizerPos.parentW-resizerPos.left-resizerPos.right-minW;
      if (a<0) resizerPos.left+=a;
    }
    lastMouseX = e.pageX;
    lastMouseY = e.pageY;
    resizerC.css(resizerPos);
    return false;
  })
  .mouseup(function(){
    resizerP.hide();
    $win.css(resizerPos).WM('ensure_viewable');
    $win = undefined;
    return false;
  });

  var resizerC=$('<div>').appendTo(resizerP);

  var resizerPos,resizeMask; // top=1,right=2,bottom=4,left=8

  var onStartResize = function(e){
    var rv = this.className.match(/resizer\-(\w+)/);
    if (rv.length != 2) return true;
    var type = rv[1];
    resizeMask = 0;
    if (type[0]=='t') resizeMask |= 1;
    else if (type[0]=='b') resizeMask |= 4;
    if (type.match(/l/)) resizeMask |= 8;
    else if (type.match(/r/)) resizeMask |= 2;
    $win = $(this).closest('.window')
      .removeClass('minimized').removeClass('maximized').removeData('oldPos');
    minH = $win.find('> .windowtitlebar').height() + 5;
    resizerPos = getPos($win);
    resizerPos.right = resizerPos.parentW-$win.outerWidth()-resizerPos.left;
    resizerPos.bottom = resizerPos.parentH-$win.outerHeight()-resizerPos.top;
    resizerPos.width = resizerPos.height = 'auto';
    lastMouseX = e.pageX;
    lastMouseY = e.pageY;
    resizerC.css(resizerPos);
    resizerP.show();
    return false;
  };

  var moverP = $('<div id=moverproxy>').mousemove(function(e){
    moverPos.top += (lastMouseY-e.pageY) * -1;
    moverPos.left += (lastMouseX-e.pageX) * -1;
    lastMouseX = e.pageX;
    lastMouseY = e.pageY;
    moverC.css(moverPos);
    return false;
  })
  .mouseup(function(){
    moverP.hide();
    $win.css(moverPos).WM('ensure_viewable').removeClass('moving');
    $win = undefined;
    return false;
  });

  var moverPos={left:0,top:0,right:'auto',bottom:'auto',width:0,height:0};
  var moverC=$('<div>').appendTo(moverP);
  var lastClick=0;
  var onStartMove = function(e){
    if (e.button && e.button==2) return true;
    $win = $(this).closest('.window');
    // if dbdclick title bar, then maximize
    if (!isIE)
    var t1 = new Date().getTime();
    if (t1 - lastClick <= 250) {
      lastClick = 0;
      $win.triggerHandler('dblclick');
      return false;
    }

    lastClick = t1;
    $win.WM('raise');
    moverPos = getPos($win);
    moverPos.width = $win.width();
    moverPos.height = $win.height();
    moverPos.bottom = moverPos.right = 'auto';
    moverC.css(moverPos);
    moverP.show();
    lastMouseX = e.pageX;
    lastMouseY = e.pageY;
    return false;
  };

  // take fixed pos into account
  var getPos=function(w){
    var p = w.offset();  
    p.top -= $(document).scrollTop();
    p.left -= $(document).scrollLeft();
    p.parentW = $(window).width();
    p.parentH = $(window).height();
    return p;
  };

  // save win current position (called before max or mining a win) 
  var savePos=function(w){
    if (! w.data('oldPos')) {
      var p = getPos(w);
      w.data('oldPos',{top:p.top,left:p.left,right:'auto',
        bottom:'auto',width:w.width(),height:w.height()});
    }
  };

  var cancelBubble = function(){ return false; };
  var onClickWindow = function(){$(this).closest('.window').WM('raise');return true;};
  var onClickMinimizeBut = function(){$(this).closest('.window').WM('minimize');return false;};
  var onClickRestoreBut = function(){$(this).closest('.window').WM('restore');return false;};
  var onClickMaximizeBut = function(){$(this).closest('.window').WM('maximize');return false;};
  var onClickCloseBut = function(){$(this).closest('.window').WM('close');return false;};
  var onDblClickTitlebar=function(){
    var w = $(this).closest('.window');
    if (w.hasClass('maximized')) w.WM('restore');
    else w.WM('maximize');
    return false;
  };
  moverP[0].onselectstart=resizerP[0].onselectstart=cancelBubble;

    
  var methods = {};
  methods.ensure_viewable=function(){
    this.filter('.window').each(function(){
      var w = $(this);
      var p = getPos(w);
      if (p.top < 0)
        w.css('top',0);
      else if (p.parentH - p.top < 20)
        w.css('top',p.parentH - 20);
      if (p.left + w.width() < 80)
        w.css({left:w.width()*-1+80,width:w.width(),right:'auto'});
      else if (p.parentW - p.left < 30)
        w.css('left',p.parentW - 30);
    });
    return this;
  };

  methods.minimize = function() {
    this.each(function(){
      var w = $(this);
      if (! w.is('.window')) return true;
      w.removeClass('maximized');
      savePos(w);
      minH = w.find('>.windowtitlebar').height()-5;
      w.css({left:'auto',top:'auto',right:0,width:300,height:minH,zIndex:100000})
       .removeClass('focused')
       .addClass('minimized'); 
    });
    $().WM('retileMin');
    return this;
  };

  methods.retileMin=function(){
    var r = 0;
    var b = 0;
    $('.window.minimized').each(function(i) {
      $(this).css({ 'bottom': b, 'right': r });
      r += $(this).width();
      var spaceLeft = $(this).offset().left;
      if (spaceLeft < $(this).width()) {
        b += $(this).find('>.windowtitlebar').height();
        r = 0;
      }
    });
    return this;
  };

  methods.maximize = function() {
    var retile;
    this.each(function(){
      var w = $(this);
      if (! w.is('.window')) return true;
      if (w.hasClass('minimized')) {
        w.removeClass('minimized');
        retile=1;
      }
      savePos(w);
      w.css({left:0,top:0,bottom:0,right:0,width:'auto',height:'auto'})
       .WM('raise').addClass('maximized'); 
    });
    if (retile) $().WM('retileMin');
    return this;
  };

  methods.restore = function() {
    var retile;
    this.each(function(){
      var w = $(this);
      if (! w.is('.window')) return true;
      if (w.hasClass('minimized')) {
        w.removeClass('minimized');
        retile=1;
      }
      else {
        w.removeClass('maximized');
      }
      w.css(w.data('oldPos'))
       .removeData('oldPos')
       .WM('ensure_viewable');
    });
    if (retile) $().WM('retileMin');
    this.WM('raise');
    return this;
  };

  methods.raise = function() {
    var w = this.filter('.window:first');
    if (w.length==0||w.hasClass('focused')) return this;
    $(".window.focused").removeClass('focused');
    w.addClass('focused').css('zIndex',++zIndex);
    return this;
  };

  methods.close = function() {
    return this.filter('.window').remove();
  };

  methods.open = function(lnk,target,opts) {
    if (! opts) opts = {};
    var w = $(template);

    var nam = opts.name;
    if (! nam && lnk) { 
      var rv = lnk.match(/([^\/]+)$/);
      if (rv) nam = rv[1].replace(/\.[^\.]+$/,'');
    }

    if (! nam) nam = opts.title || target;
    if (nam) nam = nam.replace(/[^A-Za-z0-9]/g,'');
    if (! nam) nam = 'default';
    w.addClass('windowname_' + nam);

    // smart window placement
    newWinOffset+=10;
    if (newWinOffset > 400) newWinOffset = 50;
    w.css('top',newWinOffset).css('left',newWinOffset);

    // hook in resizer handles
    $('.resizer-tl,.resizer-t,.resizer-tr,.resizer-r,.resizer-br,.resizer-b,'+
      '.resizer-bl,.resizer-l',w).mousedown(onStartResize);

    // raise window if clicked
    w.click(onClickWindow);

    // hook in titlebar actions
    var tb=$('.windowtitlebar',w);
    tb.mousedown(onStartMove);
    tb.dblclick(onDblClickTitlebar);
    var buts = tb.find('> .horizbuts').mousedown(cancelBubble).children();
    buts.eq(0).click(onClickMinimizeBut);
    buts.eq(1).click(onClickRestoreBut);
    buts.eq(2).click(onClickMaximizeBut);
    buts.eq(3).click(onClickCloseBut);

    // set default window title
    var tbt = tb.children('.titlebartext').text(opts.title||lnk||'');
    tbt[0].onselectstart = cancelBubble;
    tbt[0].unselectable = "on";

    // place window
    $(window.top.document.body).append(w);

    // open iframe if external link
    if (! lnk) {} // do nothing

    // if content is url, load in iframe
    else if (typeof lnk == 'string') {

      // if external link, add external favicon
      var m = lnk.match(/^(https?\:\/\/)([^\/]+)/);
      if (m && m[2] != document.location.hostname) 
        w.find('.titlebaricon').attr('src', m[1]+m[2]+"/favicon.ico");
      $('<iframe src="'+lnk+'" target="'+target+'"></iframe>'+
        '<div class=iframecover></div>')
        .appendTo(w.find('.windowcontent')); 
    }

    // else let jquery append it
    else w.find('.windowcontent').append(lnk);

    w.WM('raise');
    return w;
  };

  $.fn.WM = function(method) {
    if (method in methods) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || ! method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error('Method ' +  method + ' does not exist on jQuery.colorramp');
    }
  };

  // if anchor clicked having target=_child open link as child window
  $(document).delegate('a.jquery-wm','click',function(e){
    if (e.button > 0) return true;
    var t = $(this);
    window.top.jQuery().WM('open',this.href,this.target,
      {title:t.attr('title')||t.text().substr(0,100)});
    return false;
  });

  // make sure all child windows are on screen when window resizes
  $(window).resize(function(){$('.window').WM('ensure_viewable');return true;});

  $(function(){
    if (isIE) $(document.body).addClass('IE');
    $(document.body).append(moverP).append(resizerP);
  });
})(jQuery);
