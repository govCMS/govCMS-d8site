(function (Drupal, $) {
  'use strict';

  Drupal.behaviors.govcms_dca = {
    attach: function (context, settings) {
      var clipboard = new Clipboard('.btn-clipboard');
      $('.btn-clipboard').once().on('click', function (event) {
        alert('A tokenised link to this page has been copied to your clipboard!');
        event.preventDefault();
      });
    }

  };
})(Drupal, jQuery);
