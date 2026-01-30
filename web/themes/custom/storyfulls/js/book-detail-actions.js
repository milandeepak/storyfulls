(function ($, Drupal, once) {
  'use strict';

  console.log('book-detail-actions.js loaded!');

  Drupal.behaviors.bookDetailActions = {
    attach: function (context, settings) {
      console.log('Book detail actions behavior attached');
      console.log('Context:', context);
      console.log('Looking for .read-later-btn');
      
      const buttons = once('read-later-click', '.read-later-btn', context);
      console.log('Found buttons:', buttons.length);
      
      // Handle "Write a Review" button when user already reviewed
      const reviewButtons = once('already-reviewed-click', '.already-reviewed-btn', context);
      console.log('Found already-reviewed buttons:', reviewButtons.length);
      
      $(reviewButtons).on('click', function(e) {
        e.preventDefault();
        console.log('Already reviewed button clicked!');
        showToast('✓ You\'ve already reviewed this book. Thank you for your feedback!', 'info');
      });
      
      // Read Later button
      $(buttons).on('click', function(e) {
        e.preventDefault();
        console.log('Read Later button clicked!');
        
        const $btn = $(this);
        const bookId = $btn.data('book-id');
        
        console.log('Book ID:', bookId);
        
        if (!bookId) {
          console.error('Book ID not found');
          showToast('Error: Book ID not found', 'error');
          return;
        }
        
        // Disable button while processing
        $btn.prop('disabled', true);
        const originalText = $btn.find('.btn-text').text();
        $btn.find('.btn-text').text('Adding...');
        
        console.log('Sending AJAX request to /storyfulls/wishlist/add');
        
        $.ajax({
          url: '/storyfulls/wishlist/add',
          method: 'POST',
          data: {
            book_id: bookId
          },
          success: function(response) {
            console.log('AJAX Success:', response);
            if (response.success) {
              showToast(response.message, 'success');
              $btn.find('.btn-text').text('Added!');
              setTimeout(function() {
                $btn.find('.btn-text').text(originalText);
                $btn.prop('disabled', false);
              }, 2000);
            } else {
              if (response.already_added) {
                showToast(response.message, 'info');
              } else {
                showToast(response.message, 'error');
              }
              $btn.find('.btn-text').text(originalText);
              $btn.prop('disabled', false);
            }
          },
          error: function(xhr) {
            console.error('AJAX Error:', xhr.status, xhr.responseText);
            let errorMsg = 'Failed to add book to wishlist';
            if (xhr.status === 403) {
              errorMsg = 'Please log in to add books to your wishlist';
            }
            showToast(errorMsg, 'error');
            $btn.find('.btn-text').text(originalText);
            $btn.prop('disabled', false);
          }
        });
      });
      
      // Toast notification function
      function showToast(message, type = 'success') {
        console.log('Showing toast:', type, message);
        
        // Remove any existing toasts
        $('.toast-notification').remove();
        
        const toast = $('<div class="toast-notification toast-' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        console.log('Toast appended to body');
        
        // Trigger animation
        setTimeout(function() {
          toast.addClass('show');
        }, 10);
        
        // Remove after 4 seconds
        setTimeout(function() {
          toast.removeClass('show');
          setTimeout(function() {
            toast.remove();
          }, 300);
        }, 4000);
      }
    }
  };

})(jQuery, Drupal, once);
