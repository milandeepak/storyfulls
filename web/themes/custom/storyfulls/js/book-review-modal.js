/**
 * @file
 * Book Review Modal functionality.
 */

(function ($, Drupal) {
  'use strict';

  console.log('Book review modal JS loaded');

  /**
   * Handle book review modal.
   */
  Drupal.behaviors.bookReviewModal = {
    attach: function (context, settings) {
      console.log('Attaching book review modal behavior');
      
      // Handle "Write a Review" button click - use once via jQuery
      $('.write-review-btn', context).once('review-modal-trigger').each(function() {
        console.log('Found review button:', this);
        
        $(this).on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const $button = $(this);
          const bookId = $button.data('book-id');
          
          console.log('Button clicked! Book ID:', bookId);
          
          if (!bookId) {
            console.error('No book ID found');
            alert('Error: No book ID found');
            return;
          }

          // Load the form and show it in our custom modal
          console.log('Loading form from:', '/storyfulls/review/form/' + bookId);
          
          $.ajax({
            url: '/storyfulls/review/form/' + bookId,
            type: 'GET',
            beforeSend: function() {
              console.log('AJAX request starting...');
            },
            success: function(html) {
              console.log('Form loaded successfully, length:', html.length);
              openCustomModal(html);
            },
            error: function(xhr, status, error) {
              console.error('AJAX error:', {xhr: xhr, status: status, error: error});
              alert('Error opening review form: ' + error);
            }
          });
        });
      });

      // Handle modal close - using event delegation
      $(document).on('click', '.custom-modal-overlay', function(e) {
        if (e.target === this) {
          console.log('Closing modal - overlay click');
          closeCustomModal();
        }
      });

      $(document).on('click', '.custom-modal-close, .close-modal-btn', function(e) {
        e.preventDefault();
        console.log('Closing modal - close button');
        closeCustomModal();
      });

      // Handle ESC key
      $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('.custom-modal-overlay').length) {
          console.log('Closing modal - ESC key');
          closeCustomModal();
        }
      });

      // Handle review form star rating interaction
      $(document).on('change', '.review-rating-select', function() {
        const $select = $(this);
        const rating = $select.val();
        console.log('Rating selected:', rating);
        
        // Visual feedback
        $select.addClass('selected');
      });
    }
  };

  /**
   * Open custom modal.
   */
  function openCustomModal(content) {
    console.log('Opening modal with content');
    
    // Remove existing modal if any
    $('.custom-modal-overlay').remove();
    
    // Create modal HTML
    const modalHtml = '<div class="custom-modal-overlay">' +
      '<div class="custom-modal-container">' +
      '<button class="custom-modal-close" type="button" aria-label="Close">&times;</button>' +
      '<div class="custom-modal-content">' +
      content +
      '</div>' +
      '</div>' +
      '</div>';
    
    $('body').append(modalHtml);
    console.log('Modal HTML appended to body');
    
    // Trigger animation
    setTimeout(function() {
      $('.custom-modal-overlay').addClass('show');
      console.log('Modal animation triggered');
    }, 10);
    
    // Prevent body scroll
    $('body').css('overflow', 'hidden');
  }

  /**
   * Close custom modal.
   */
  function closeCustomModal() {
    console.log('Closing modal');
    $('.custom-modal-overlay').removeClass('show');
    setTimeout(function() {
      $('.custom-modal-overlay').remove();
      $('body').css('overflow', '');
      console.log('Modal removed');
    }, 300);
  }

  /**
   * Global function to handle successful review submission.
   * Called from AJAX response.
   */
  window.reviewSubmitted = function(reviewId) {
    console.log('Review submitted:', reviewId);
    closeCustomModal();
    
    // Show success message
    alert('Thank you! Your review has been submitted successfully.');
    
    // Reload page to show new review
    setTimeout(function() {
      location.reload();
    }, 1000);
  };

})(jQuery, Drupal);
