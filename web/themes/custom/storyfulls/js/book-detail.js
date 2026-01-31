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
      $('.read-later-btn', context).once('read-later').on('click', function(e) {
        const $button = $(this);
        const bookId = $(this).data('book-id');
        
        // Check if button is disabled and show appropriate message
        if ($button.prop('disabled')) {
          const reason = $button.data('reason');
          let message = '';
          
          switch(reason) {
            case 'already-in-wishlist':
              message = 'This book is already in your Read Later list.';
              break;
            case 'already-read':
              message = 'You cannot add this book to Read Later because you have already marked it as read.';
              break;
            case 'already-reviewed':
              message = 'You cannot add this book to Read Later because you have already reviewed it.';
              break;
            default:
              message = 'This action is not available for this book.';
          }
          
          showToast(message, 'info');
          return;
        }
        
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
              $button.html('<span class="btn-icon-circle"><img src="/themes/custom/storyfulls/images/readlatericon.png" alt="Read Later" class="btn-icon-img"></span><span class="btn-text">Already in Read Later</span>');
            } else {
              if (response.conflict) {
                showToast(response.message, 'error');
              } else if (response.already_added) {
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
      $('.already-read-btn', context).once('already-read').on('click', function(e) {
        const $button = $(this);
        const bookId = $(this).data('book-id');
        
        // Check if button is disabled and show appropriate message
        if ($button.prop('disabled')) {
          const reason = $button.data('reason');
          let message = '';
          
          switch(reason) {
            case 'in-wishlist':
              message = 'You cannot mark this book as read because it is in your Read Later list. Please remove it from Read Later first.';
              break;
            case 'already-in-list':
              message = 'This book is already in your Books I\'ve Read list.';
              break;
            case 'already-reviewed':
              message = 'This book is already marked as read (you have reviewed it).';
              break;
            default:
              message = 'This action is not available for this book.';
          }
          
          showToast(message, 'info');
          return;
        }
        
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
              // Update button state
              $button.html('<span class="btn-icon-circle"><img src="/themes/custom/storyfulls/images/alreadyreadicon.png" alt="Already Read" class="btn-icon-img"></span><span class="btn-text">Already Marked as Read</span>');
            } else {
              if (response.conflict) {
                showToast(response.message, 'error');
              } else if (response.already_added) {
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
      
      // Review text truncation and "Read more" handler
      $('.review-text', context).once('review-truncate').each(function() {
        const $reviewText = $(this);
        const $readMoreLink = $reviewText.closest('.review-text-wrapper').find('.review-read-more');
        
        // Check if text is truncated (comparing scroll height to client height)
        if (this.scrollHeight > this.clientHeight) {
          $readMoreLink.show();
        }
      });
      
      // Handle "Write a Review" button click when user has already reviewed
      $('.already-reviewed-btn', context).once('already-reviewed').on('click', function(e) {
        e.preventDefault();
        const alreadyReviewed = $(this).data('already-reviewed');
        const inWishlist = $(this).data('in-wishlist');
        
        if (alreadyReviewed === true || alreadyReviewed === 'true') {
          showToast('✓ You\'ve already reviewed this book. Thank you for your feedback!', 'info');
        } else if (inWishlist === true || inWishlist === 'true') {
          showToast('You cannot review this book while it is in your Read Later list. Please mark it as read first.', 'info');
        }
      });
      
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
