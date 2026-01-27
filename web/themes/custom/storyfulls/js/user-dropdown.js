(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.userDropdown = {
    attach: function (context, settings) {
      $('.user-menu-toggle', context).off('click.userdropdown').on('click.userdropdown', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $menuItem = $(this).closest('.user-menu-item');
        
        // Toggle dropdown
        $menuItem.toggleClass('open');
        
        // Close dropdown when clicking outside
        if ($menuItem.hasClass('open')) {
          $(document).on('click.user-dropdown-outside', function(event) {
            if (!$(event.target).closest('.user-menu-item').length) {
              $menuItem.removeClass('open');
              $(document).off('click.user-dropdown-outside');
            }
          });
        } else {
          $(document).off('click.user-dropdown-outside');
        }
      });
      
      // Close dropdown when pressing Escape
      $(document).off('keydown.user-dropdown-escape').on('keydown.user-dropdown-escape', function(e) {
        if (e.key === 'Escape') {
          $('.user-menu-item').removeClass('open');
          $(document).off('click.user-dropdown-outside');
        }
      });
    }
  };

})(jQuery, Drupal);
