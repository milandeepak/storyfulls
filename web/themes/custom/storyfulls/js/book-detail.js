/**
 * Book Detail Page JavaScript
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.bookDetail = {
    attach: function (context, settings) {
      
      // Share Book Function
      window.shareBook = function() {
        const bookTitle = document.querySelector('.book-detail-title') ? document.querySelector('.book-detail-title').textContent : 'this book';
        const bookUrl = window.location.href;
        
        // Check if Web Share API is available
        if (navigator.share) {
          navigator.share({
            title: bookTitle,
            text: 'Check out ' + bookTitle + ' on Storyfulls!',
            url: bookUrl,
          })
          .then(() => console.log('Successful share'))
          .catch((error) => console.log('Error sharing', error));
        } else {
          // Fallback: Copy to clipboard
          const tempInput = document.createElement('input');
          tempInput.value = bookUrl;
          document.body.appendChild(tempInput);
          tempInput.select();
          document.execCommand('copy');
          document.body.removeChild(tempInput);
          
          // Show a notification
          alert('Link copied to clipboard!');
        }
      };
      
      // Read Later Button
      $('.read-later-btn', context).once('read-later').on('click', function() {
        const $button = $(this);
        const bookId = $(this).data('book-id');
        
        if (!bookId) {
          console.error('Book ID not found');
          return;
        }
        
        // Disable button during request
        $button.prop('disabled', true);
        
        $.ajax({
          url: '/storyfulls/wishlist/add',
          method: 'POST',
          data: {
            book_id: bookId
          },
          success: function(response) {
            if (response.success) {
              showToast(response.message, 'success');
              // Update button state
              $button.html('<span class="btn-icon">✓</span><span class="btn-text">Added to Read Later</span>');
            } else {
              if (response.already_added) {
                showToast(response.message, 'info');
              } else {
                showToast(response.message, 'error');
              }
              $button.prop('disabled', false);
            }
          },
          error: function(xhr) {
            let message = 'Failed to add book. Please try again.';
            if (xhr.status === 403) {
              message = 'Please log in to add books to your wishlist.';
            }
            showToast(message, 'error');
            $button.prop('disabled', false);
          }
        });
      });
      
      // Already Read Button
      $('.already-read-btn', context).once('already-read').on('click', function() {
        const $button = $(this);
        const bookId = $(this).data('book-id');
        
        if (!bookId) {
          console.error('Book ID not found');
          return;
        }
        
        // Disable button during request
        $button.prop('disabled', true);
        
        $.ajax({
          url: '/storyfulls/books-read/add',
          method: 'POST',
          data: {
            book_id: bookId
          },
          success: function(response) {
            if (response.success) {
              showToast(response.message, 'success');
              // Optionally update button state
              $button.html('<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M15 5L7 13L3 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Added to Books Read');
            } else {
              if (response.already_added) {
                showToast(response.message, 'info');
              } else {
                showToast(response.message, 'error');
              }
              $button.prop('disabled', false);
            }
          },
          error: function(xhr) {
            let message = 'Failed to add book. Please try again.';
            if (xhr.status === 403) {
              message = 'Please log in to add books to your reading list.';
            }
            showToast(message, 'error');
            $button.prop('disabled', false);
          }
        });
      });
      
      // Toast notification for review submission
      if (typeof drupalSettings !== 'undefined' && drupalSettings.showReviewToast) {
        setTimeout(function() {
          showReviewToast();
        }, 500); // Slight delay so it appears after page load
      }
      
    }
  };
  
  /**
   * Show toast notification for review submission
   */
  function showReviewToast() {
    showToast('✓ Review submitted successfully!', 'success');
  }
  
  /**
   * Generic toast notification function
   */
  function showToast(message, type) {
    type = type || 'success';
    const iconMap = {
      success: '✓',
      error: '✗',
      info: 'ℹ'
    };
    const icon = iconMap[type] || '✓';
    
    var toast = $('<div class="toast-notification toast-' + type + '">' + icon + ' ' + message + '</div>');
    $('body').append(toast);
    
    setTimeout(function() {
      toast.addClass('show');
    }, 100);
    
    setTimeout(function() {
      toast.removeClass('show');
      setTimeout(function() {
        toast.remove();
      }, 300);
    }, 4000);
  }

})(jQuery, Drupal);
