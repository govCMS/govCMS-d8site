/**
 * Main menu morph dropdown.
 */
(function ($, Drupal, window, document) {
  'use strict';

  // Example of Drupal behavior loaded.
  Drupal.behaviors.morph_dropdown = {
    attach: function (context, settings) {

      // stripe style menu
      function morphDropdown(element) {
        this.element = element;
        this.mainNavigation = this.element.find('.main-nav');
        this.mainNavigationItems = this.mainNavigation.find('.has-dropdown');
        this.dropdownList = this.element.find('.dropdown-list');
        this.dropdownWrappers = this.dropdownList.find('.dropdown');
        this.dropdownItems = this.dropdownList.find('.dd-content');
        this.dropdownBg = this.dropdownList.find('.bg-layer');
        this.mq = this.checkMq();

        this.bindEvents();
      }

      morphDropdown.prototype.checkMq = function () {
        // check screen size
        var self = this;
        return window.getComputedStyle(self.element.get(0), '::before').getPropertyValue('content').replace(/'/g, '').replace(/"/g, '').split(', ');
      };

      morphDropdown.prototype.bindEvents = function () {
        var self = this;
        // hover over an item in the main navigation
        this.mainNavigationItems.mouseenter(function (event) {
          // hover over one of the nav items -> show dropdown
          self.showDropdown($(this));
        }).mouseleave(function () {
          setTimeout(function () {
            // if not hovering over a nav item or a dropdown -> hide dropdown
            if (self.mainNavigation.find('.has-dropdown:hover').length === 0 && self.element.find('.dropdown-list:hover').length === 0) {
              self.hideDropdown();
            }
          }, 1000);
        });

        // hover over the dropdown
        this.dropdownList.mouseleave(function () {
          setTimeout(function () {
            // if not hovering over a dropdown or a nav item -> hide dropdown
            if (self.mainNavigation.find('.has-dropdown:hover').length === 0 && self.element.find('.dropdown-list:hover').length === 0) {
              self.hideDropdown();
            }
          }, 50);
        });

        // click on an item in the main navigation -> open a dropdown on a touch device
        this.mainNavigationItems.on('touchstart', function (event) {
          var selectedDropdown = self.dropdownList.find('#' + $(this).data('content'));
          if (!self.element.hasClass('is-dropdown-visible') || !selectedDropdown.hasClass('dd-active')) {
            event.preventDefault();
            self.showDropdown($(this));
          }
        });

        // on small screens, open navigation clicking on the menu icon
        this.element.on('click', '.nav-trigger', function (event) {
          event.preventDefault();
          self.element.toggleClass('nav-open');
        });
      };

      morphDropdown.prototype.showDropdown = function (item) {
        this.mq = this.checkMq();
        if (this.mq[0] === 'desktop') {
          var self = this;
          var selectedDropdown = this.dropdownList.find('#' + item.data('content'));
          var selectedDropdownHeight = selectedDropdown.innerHeight();
          var selectedDropdownWidth = selectedDropdown.children('.dd-content').innerWidth();
          var selectedDropdownLeft = item.position().left + (item.width() * 0.5) - (selectedDropdownWidth * 0.5);
          // update dropdown position and size
          this.updateDropdown(selectedDropdown, parseInt(selectedDropdownHeight), selectedDropdownWidth, parseInt(selectedDropdownLeft));
          // add active class to the proper dropdown item
          this.element.find('.dd-active').removeClass('dd-active');
          selectedDropdown.addClass('dd-active').removeClass('move-left move-right').prevAll().addClass('move-left').end().nextAll().addClass('move-right');
          item.addClass('dd-active');
          // show the dropdown wrapper if not visible yet
          if (!this.element.hasClass('is-dropdown-visible')) {
            setTimeout(function () {
              self.element.addClass('is-dropdown-visible');
            }, 10);
          }
        }
      };

      morphDropdown.prototype.updateDropdown = function (dropdownItem, height, width, left) {
        this.dropdownList.css({
          '-moz-transform': 'translateX(' + left + 'px)',
          '-webkit-transform': 'translateX(' + left + 'px)',
          '-ms-transform': 'translateX(' + left + 'px)',
          '-o-transform': 'translateX(' + left + 'px)',
          'transform': 'translateX(' + left + 'px)',
          'width': width + 'px',
          'height': height + 'px'
        });

        this.dropdownBg.css({
          '-moz-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
          '-webkit-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
          '-ms-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
          '-o-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
          'transform': 'scaleX(' + width + ') scaleY(' + height + ')'
        });
      };

      morphDropdown.prototype.hideDropdown = function () {
        this.mq = this.checkMq();
        if (this.mq[0] === 'desktop') {
          this.element.removeClass('is-dropdown-visible').find('.dd-active').removeClass('dd-active').end().find('.move-left').removeClass('move-left').end().find('.move-right').removeClass('move-right');
        }
      };

      morphDropdown.prototype.resetDropdown = function () {
        this.mq = this.checkMq();
        if (this.mq[0] === 'mobile') {
          this.dropdownList.removeAttr('style');
        }
      };

      var resizing = false;
      var morphDropdowns = [];
      if ($('.cd-morph-dropdown', context).length > 0) {
        $('.cd-morph-dropdown', context).each(function () {
          // create a morphDropdown object for each .cd-morph-dropdown
          morphDropdowns.push(new morphDropdown($(this)));
        });

        // on resize, reset dropdown style property
        updateDropdownPosition();
        $(window).on('resize', function () {
          if (!resizing) {
            resizing = true;
            if (!window.requestAnimationFrame) {
              setTimeout(updateDropdownPosition, 300);
            }
            else {
              window.requestAnimationFrame(updateDropdownPosition);
            }
          }
        });
      }

      function updateDropdownPosition() {
        morphDropdowns.forEach(function (element) {
          element.resetDropdown();
        });
        resizing = false;
      }
    }
  };

})(jQuery, Drupal, this, this.document);
