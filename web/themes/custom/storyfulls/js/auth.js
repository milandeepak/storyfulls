(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.storyFullsAuth = {
    attach: function (context, settings) {
      
      // === Registration Form Handling ===
      var $registerForm = $('.user-register-form', context);
      
      if ($registerForm.length) {

        // Managed by Parents / Show profile to public – sync checked state for tick visibility
        function syncToggleSwitchChecked() {
          $('.toggle-switch-wrapper', context).each(function() {
            var $wrapper = $(this);
            var $input = $wrapper.find('input[type="checkbox"]');
            if ($input.length && $input.is(':checked')) {
              $wrapper.addClass('is-checked');
            } else {
              $wrapper.removeClass('is-checked');
            }
          });
        }
        syncToggleSwitchChecked();
        $(context).on('change', '.toggle-switch-wrapper input[type="checkbox"]', syncToggleSwitchChecked);
        
        // Tab Switching Logic
        $('.register-tab', context).off('click.registertab').on('click.registertab', function(e) {
          e.preventDefault();
          var targetId = $(this).data('target');
          
          // Update Tabs
          $('.register-tab').removeClass('active');
          $(this).addClass('active');
          
          // Show/Hide Steps
          $('.register-step').hide();
          $('#' + targetId).show();
        });
        
        // Next Button Handler
        $(context).on('click', '.btn-next-step', function(e) {
          e.preventDefault();
          $('.register-tab[data-target="step-2"]').trigger('click');
          $('html, body').animate({ scrollTop: 0 }, 300);
        });
        
        // Back Button Handler
        $(context).on('click', '.btn-prev-step', function(e) {
          e.preventDefault();
          $('.register-tab[data-target="step-1"]').trigger('click');
          $('html, body').animate({ scrollTop: 0 }, 300);
        });
        
        // === AUTHORS SECTION - Simple Add/Remove ===
        var authorFieldCount = 1; // Start with 1 (field 0 already exists)
        
        // Add Author Button
        $(context).find('#addAuthorBtn').off('click').on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if (authorFieldCount >= 5) {
            alert('You can only add up to 5 favourite authors.');
            return;
          }
          
          // Get autocomplete URL from the first field to ensure consistency
          var $firstField = $('input[name="favorite_author_0"]');
          var autocompletePath = $firstField.attr('data-autocomplete-path') || '/storyfulls/autocomplete/authors';
          
          // Create new field
          var $newField = $('<div class="reg-favorite-field" data-field-index="' + authorFieldCount + '">' +
            '<input type="text" name="favorite_author_' + authorFieldCount + '" ' +
            'class="reg-favorite-input author-autocomplete-field form-text form-autocomplete" ' +
            'placeholder="Type author name..." data-field-index="' + authorFieldCount + '" ' +
            'data-autocomplete-path="' + autocompletePath + '">' +
            '<button type="button" class="reg-remove-btn" title="Remove"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>' +
            '</div>');
          
          $('#authorsFieldsContainer').append($newField);
          
          // Initialize Drupal behaviors for the new field (enables autocomplete)
          Drupal.attachBehaviors($newField[0], settings);
          
          authorFieldCount++;
          
          // Show remove buttons if more than 1 field
          if (authorFieldCount > 1) {
            $('.reg-favorite-field .reg-remove-btn').show();
          }
        });
        
        // Remove Author Field
        $(context).on('click', '.reg-favorite-field .reg-remove-btn', function(e) {
          e.preventDefault();
          var $field = $(this).closest('.reg-favorite-field');
          
          // Only allow removal if more than 1 field exists
          if ($('#authorsFieldsContainer .reg-favorite-field').length <= 1) {
            alert('You must have at least one author field.');
            return;
          }
          
          $field.fadeOut(300, function() {
            $(this).remove();
            authorFieldCount--;
            
            // Hide remove buttons if only 1 field left
            if ($('#authorsFieldsContainer .reg-favorite-field').length <= 1) {
              $('.reg-favorite-field .reg-remove-btn').hide();
            }
          });
        });
        
        // === BOOKS SECTION - Simple Add/Remove ===
        var bookFieldCount = 1; // Start with 1 (field 0 already exists)
        
        // Add Book Button
        $(context).find('#addBookBtn').off('click').on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if (bookFieldCount >= 5) {
            alert('You can only add up to 5 favourite books.');
            return;
          }
          
          // Get autocomplete URL from the first field to ensure consistency
          var $firstBookField = $('input[name="favorite_book_0"]');
          var bookAutocompletePath = $firstBookField.attr('data-autocomplete-path') || '/storyfulls/autocomplete/books';
          
          // Create new field
          var $newField = $('<div class="reg-favorite-field" data-field-index="' + bookFieldCount + '">' +
            '<input type="text" name="favorite_book_' + bookFieldCount + '" ' +
            'class="reg-favorite-input book-autocomplete-field form-text form-autocomplete" ' +
            'placeholder="Type book title..." data-field-index="' + bookFieldCount + '" ' +
            'data-autocomplete-path="' + bookAutocompletePath + '">' +
            '<button type="button" class="reg-remove-btn" title="Remove"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>' +
            '</div>');
          
          $('#booksFieldsContainer').append($newField);
          
          // Initialize Drupal behaviors for the new field (enables autocomplete)
          Drupal.attachBehaviors($newField[0], settings);
          
          bookFieldCount++;
          
          // Show remove buttons if more than 1 field
          if (bookFieldCount > 1) {
            $('#booksFieldsContainer .reg-remove-btn').show();
          }
        });
        
        // Remove Book Field
        $(context).on('click', '#booksFieldsContainer .reg-remove-btn', function(e) {
          e.preventDefault();
          var $field = $(this).closest('.reg-favorite-field');
          
          // Only allow removal if more than 1 field exists
          if ($('#booksFieldsContainer .reg-favorite-field').length <= 1) {
            alert('You must have at least one book field.');
            return;
          }
          
          $field.fadeOut(300, function() {
            $(this).remove();
            bookFieldCount--;
            
            // Hide remove buttons if only 1 field left
            if ($('#booksFieldsContainer .reg-favorite-field').length <= 1) {
              $('#booksFieldsContainer .reg-remove-btn').hide();
            }
          });
        });
      }

      // Password strength meter (Keep existing logic)
      $('input[name="pass[pass1]"], input[data-drupal-selector="edit-pass-pass1"]', context).off('keyup.passstrength').on('keyup.passstrength', function() {
        var password = $(this).val();
        var strength = calculatePasswordStrength(password);
        
        var $indicator = $(this).closest('.form-item').find('.password-strength');
        if (!$indicator.length) {
          $indicator = $('<div class="password-strength"></div>').insertAfter($(this));
        }
        
        $indicator.removeClass('weak medium strong').hide();
        
        if (password.length > 0) {
          $indicator.show();
          if (strength < 30) {
            $indicator.addClass('weak').text('Weak');
          } else if (strength < 60) {
            $indicator.addClass('medium').text('Medium');
          } else {
            $indicator.addClass('strong').text('Strong');
          }
        }
      });

      function calculatePasswordStrength(password) {
        var strength = 0;
        if (password.length >= 8) strength += 20;
        if (password.length >= 12) strength += 10;
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        return Math.min(strength, 100);
      }
    }
  };

})(jQuery, Drupal, once);
