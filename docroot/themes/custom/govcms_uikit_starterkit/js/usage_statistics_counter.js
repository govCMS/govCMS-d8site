/**
 * Usage statistics counter.
 */
(function ($, Drupal, window, document) {
  'use strict';

  var $window = null;

  function isElementVisible($elem) {
    var TopView = $window.scrollTop();
    var BotView = TopView + $window.height();
    var TopElement = $elem.offset().top;
    var BotElement = TopElement + $elem.height();
    return (((BotElement <= BotView) && (TopElement >= TopView)) || (!(BotElement <= TopView) && !(TopElement >= BotView)));
  }

  function startCounter($obj) {
    $obj.prop('Counter', 0).animate({
      Counter: $obj.text()
    }, {
      duration: 1300,
      easing: 'swing',
      step: function (now) {
        $obj.text(Math.ceil(now));
      },
      complete: function () {
        $obj.addClass('counted');
        $obj.removeClass('counting');
      }
    });
  }

  function init_counter($fancy_counters) {
    $fancy_counters.each(function () {
      var $this = $(this);
      var isInView = isElementVisible($this);

      // Start counter when in view.
      if (isInView && !$this.hasClass('counted') && !$this.hasClass('counting')) {
        $this.addClass('counting');
        startCounter($this);
      }

      // Add faded classes when in view.
      if (isInView && !$this.hasClass('faded')) {
        $this.addClass('faded');
      }
    });
  }

  // Example of Drupal behavior loaded.
  Drupal.behaviors.usage_statistics_counter = {
    attach: function (context, settings) {
      $window = $(window);

      if ($('.fancyCounter', context).length > 0) {
        var $fancy_counters = $('.fancyCounter', context);
        $fancy_counters.addClass('do-fade');

        // Start counter for any immediately visible elements.
        init_counter($fancy_counters);

        // Listen on scroll if elements appear.
        $window.scroll(function () {
          init_counter($fancy_counters);
        });
      }

    }
  };

})(jQuery, Drupal, this, this.document);
