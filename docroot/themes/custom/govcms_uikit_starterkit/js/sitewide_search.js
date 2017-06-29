/**
 * Sitewide search widget.
 */
(function ($, Drupal, window, document) {
  'use strict';

  // Example of Drupal behavior loaded.
  Drupal.behaviors.sitewideSearch = {
    attach: function (context, settings) {
      if (typeof context['location'] !== 'undefined') { // Only fire on document load.
        var $searchBlock = $('#block-exposedformsearchpage-2');

        // Show the keywords input on first click of the search button.
        $searchBlock.find('.form-submit').click(function (e) {
          if (!$searchBlock.hasClass('is-active')) {
            $searchBlock.addClass('is-active');
            e.preventDefault();
          }
        });

        $(document).click(function (e) {
          if (e.target.id !== 'edit-submit-search' && e.target.id !== 'edit-keywords' && $searchBlock.hasClass('is-active')) {
            $searchBlock.removeClass('is-active');
          }
        });
      }
    }
  };

})(jQuery, Drupal, this, this.document);
