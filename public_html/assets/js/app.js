(function($){

    /**
     * Copyright 2012, Digital Fusion
     * Licensed under the MIT license.
     * http://teamdf.com/jquery-plugins/license/
     *
     * @author Sam Sehnert
     * @desc A small plugin that checks whether elements are within
     *       the user visible viewport of a web browser.
     *       only accounts for vertical position, not horizontal.
     */
    $.fn.visible = function(partial,hidden,direction,container){

        if (this.length < 1)
            return;

        var $t          = this.length > 1 ? this.eq(0) : this,
						isContained = typeof container !== 'undefined' && container !== null,
						$w				  = isContained ? $(container) : $(window),
						wPosition        = isContained ? $w.position() : 0,
            t           = $t.get(0),
            vpWidth     = $w.outerWidth(),
            vpHeight    = $w.outerHeight(),
            direction   = (direction) ? direction : 'both',
            clientSize  = hidden === true ? t.offsetWidth * t.offsetHeight : true;

        if (typeof t.getBoundingClientRect === 'function'){

            // Use this native browser method, if available.
            var rec = t.getBoundingClientRect(),
                tViz = isContained ?
												rec.top - wPosition.top >= 0 && rec.top < vpHeight + wPosition.top :
												rec.top >= 0 && rec.top < vpHeight,
                bViz = isContained ?
												rec.bottom - wPosition.top > 0 && rec.bottom <= vpHeight + wPosition.top :
												rec.bottom > 0 && rec.bottom <= vpHeight,
                lViz = isContained ?
												rec.left - wPosition.left >= 0 && rec.left < vpWidth + wPosition.left :
												rec.left >= 0 && rec.left <  vpWidth,
                rViz = isContained ?
												rec.right - wPosition.left > 0  && rec.right < vpWidth + wPosition.left  :
												rec.right > 0 && rec.right <= vpWidth,
                vVisible   = partial ? tViz || bViz : tViz && bViz,
                hVisible   = partial ? lViz || rViz : lViz && rViz;

            if(direction === 'both')
                return clientSize && vVisible && hVisible;
            else if(direction === 'vertical')
                return clientSize && vVisible;
            else if(direction === 'horizontal')
                return clientSize && hVisible;
        } else {

            var viewTop 				= isContained ? 0 : wPosition,
                viewBottom      = viewTop + vpHeight,
                viewLeft        = $w.scrollLeft(),
                viewRight       = viewLeft + vpWidth,
                position          = $t.position(),
                _top            = position.top,
                _bottom         = _top + $t.height(),
                _left           = position.left,
                _right          = _left + $t.width(),
                compareTop      = partial === true ? _bottom : _top,
                compareBottom   = partial === true ? _top : _bottom,
                compareLeft     = partial === true ? _right : _left,
                compareRight    = partial === true ? _left : _right;

            if(direction === 'both')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop)) && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
            else if(direction === 'vertical')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
            else if(direction === 'horizontal')
                return !!clientSize && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
        }
    };

})(jQuery);


$(function() {

  $('a[href="#"]').on('click', function(e) {e.preventDefault();});
  
  var scrollTo = function(selector, then) {
    if(!$(selector).length) return;
  
    $('html:not(:animated),body:not(:animated)').stop().animate({
      scrollTop: $(selector).offset().top
    }, 500, function(){
      if(then) then();
    });
  };
  
  $('[data-to-top]').on('click touchend',function(e) {
    scrollTo('body');
  });
  
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
  
    if(scroll > $(window).height() / 3) {
      $('[data-to-top]').addClass('active');
    } else {
      $('[data-to-top]').removeClass('active');
    }
  });
  
  if(location.hash) {
    $('a[href="' + location.hash.replace(/^#_/, '#') + '"]').tab('show');
  }
  
  $(document.body).on('click', 'a[data-toggle]', function(e) {
    e.preventDefault();
    location.hash = this.getAttribute('href').replace(/^#/, '#_');
  });
  
  $(window).on('popstate', function() {
    var anchor = location.hash.replace(/^#_/, '#') || $('a[data-toggle="tab"]').first().attr('href');
    $('a[href="' + anchor + '"]').tab('show');
  });
  
  $('[data-count-up]').each(function() {
    var $this = $(this);
    var countTo = $this.data('count-up');
  
    if(!countTo.length) countTo = parseFloat($this.html());
  
    var reset = function() {
      $this.data('visible', 'no');
  
      $this.html(0);
    };
  
    var render = function() {
  
      $({ countNum: 0})
        .animate({
            countNum: countTo
          },
          {
            duration: 500,
            easing:'linear',
            step: function() {
              $this.text(Math.floor(this.countNum));
            },
            complete: function() {
              $this.text(this.countNum);
            }
          });
    };
  
    reset();
  
    var updateVisbility = function() {
  
      if($this.visible(true) && $this.is(':visible')) {
        if($this.data('visible') === 'no') {
          render();
        }
        $this.data('visible', 'yes');
      }
    };
  
    setTimeout(updateVisbility, 500);
  
    $(window).on('scroll resize', updateVisbility);
  
    $(document).on('shown.bs.tab', function() {
      reset();
      updateVisbility();
    });
  });
  
  
  Chart.defaults.global.animation.easing = 'easeOutBack';
  
  $('[data-chart]').each(function() {
    var $this = $(this);
    var values = $this.data('values').split(',');
    var labels = $this.data('labels').split(',');
    var colors = $this.data('colors').split(',');
    var chart = null;
  
    var renderChart = function() {
      chart = new Chart($this, {
        type: 'doughnut',
        options: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 20
            }
          }
        },
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: colors
          }]
        }
      });
    };
  
    var reset = function() {
      if(chart) {
        chart.reset();
        chart.render();
      }
    };
  
    var updateVisbility = function() {
  
      if($this.visible(true) && $this.is(':visible')) {
        if(!chart) renderChart();
        chart.update(500);
      }
    };
  
    setTimeout(updateVisbility, 500);
  
    $(window).on('scroll resize', updateVisbility);
  
    $(document).on('shown.bs.tab', function() {
      reset();
      updateVisbility();
    });
  });

});