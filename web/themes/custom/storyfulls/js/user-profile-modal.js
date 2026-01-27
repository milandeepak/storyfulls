/**
 * @file
 * User Profile Modal Functionality
 * Handles all modal interactions, AJAX submissions, and UI updates for user profile page
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.userProfileModal = {
    attach: function (context, settings) {
      
      // ==============================
      // Modal Open/Close Functionality
      // ==============================
      
      // Open modal when edit button is clicked
      $('[data-modal]', context).once('profile-modal-trigger').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent parent link clicks
        const modalName = $(this).data('modal');
        const modalId = modalName + '-modal';
        
        // Show the modal
        $('#' + modalId).fadeIn(200);
        
        // Prevent body scroll when modal is open
        $('body').css('overflow', 'hidden');
        
        // Initialize autocomplete for this modal if needed
        if (modalName === 'edit-authors' || modalName === 'edit-books') {
          initializeAutocomplete(modalName);
        }
        
        // Initialize sortable for draggable fields
        if (modalName === 'edit-authors' || modalName === 'edit-books') {
          initializeSortable(modalName);
        }
      });
      
      // Close modal when X button or Cancel is clicked
      $('[data-close]', context).once('profile-modal-close').on('click', function(e) {
        e.preventDefault();
        const modalId = $(this).data('close');
        closeModal(modalId);
      });
      
      // Close modal when clicking outside
      $('.modal-overlay', context).once('profile-modal-overlay').on('click', function(e) {
        if ($(e.target).hasClass('modal-overlay')) {
          closeModal($(this).attr('id'));
        }
      });
      
      // Close modal on ESC key
      $(document).once('profile-modal-esc').on('keyup', function(e) {
        if (e.key === 'Escape') {
          $('.modal-overlay:visible').each(function() {
            closeModal($(this).attr('id'));
          });
        }
      });
      
      // ==============================
      // Form Submission Handlers
      // ==============================
      
      // Edit Profile Form
      $('#edit-profile-form', context).once('profile-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const userId = $form.data('user-id');
        const formData = new FormData(this);
        
        // Show loading state
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
          url: '/storyfulls/profile/update',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.success) {
              // Update UI with new values
              updateProfileUI(response.data);
              
              // Close modal
              closeModal('edit-profile-modal');
              
              // Show success message
              showMessage('Profile updated successfully!', 'success');
            } else {
              showMessage(response.message || 'Error updating profile', 'error');
            }
          },
          error: function() {
            showMessage('Error updating profile. Please try again.', 'error');
          },
          complete: function() {
            $submitBtn.prop('disabled', false).text(originalText);
          }
        });
      });
      
      // Edit Favorite Authors Form
      $('#edit-authors-form', context).once('authors-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const authors = [];
        
        // Collect all author values in order
        $form.find('input[name^="author_"]').each(function() {
          const val = $(this).val().trim();
          if (val) {
            authors.push(val);
          }
        });
        
        saveFavorites('authors', authors, 'edit-authors-modal');
      });
      
      // Edit Favorite Books Form
      $('#edit-books-form', context).once('books-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const books = [];
        
        // Collect all book values in order
        $form.find('input[name^="book_"]').each(function() {
          const val = $(this).val().trim();
          if (val) {
            books.push(val);
          }
        });
        
        saveFavorites('books', books, 'edit-books-modal');
      });
      
      // Edit Favorite Genres Form
      $('#edit-genres-form', context).once('genres-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const genres = [];
        
        // Collect checked genres
        $form.find('input[name="genres[]"]:checked').each(function() {
          genres.push($(this).val());
        });
        
        saveFavorites('genres', genres, 'edit-genres-modal');
      });
      
      // Edit Week's Pick Form
      $('#edit-weeks-pick-form', context).once('weeks-pick-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.text();
        
        const data = {
          book: $form.find('input[name="weeks_pick_book"]').val(),
          author: $form.find('input[name="weeks_pick_author"]').val()
        };
        
        $submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
          url: '/storyfulls/profile/update-weeks-pick',
          type: 'POST',
          data: JSON.stringify(data),
          contentType: 'application/json',
          success: function(response) {
            if (response.success) {
              // Update UI
              $('.weeks-pick-card .card-subtitle').text(data.book || 'Book Name');
              
              closeModal('edit-weeks-pick-modal');
              showMessage('Week\'s pick updated successfully!', 'success');
            } else {
              showMessage(response.message || 'Error updating week\'s pick', 'error');
            }
          },
          error: function() {
            showMessage('Error updating week\'s pick. Please try again.', 'error');
          },
          complete: function() {
            $submitBtn.prop('disabled', false).text(originalText);
          }
        });
      });
      
      // Edit Wishlist Form
      $('#edit-wishlist-form', context).once('wishlist-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const books = [];
        $(this).find('.wishlist-item').each(function() {
          books.push($(this).data('book-id'));
        });
        
        saveBookList('wishlist', books, 'edit-wishlist-modal');
      });
      
      // Edit Books Read Form
      $('#edit-books-read-form', context).once('books-read-form-submit').on('submit', function(e) {
        e.preventDefault();
        
        const books = [];
        $(this).find('.books-read-item').each(function() {
          books.push($(this).data('book-id'));
        });
        
        saveBookList('books_read', books, 'edit-books-read-modal');
      });
      
      // ==============================
      // Book Search and Add (for wishlist/books read)
      // ==============================
      
      // Search books for wishlist/read lists
      $('.book-search-input', context).once('book-search').on('keyup', function() {
        const $input = $(this);
        const query = $input.val().trim();
        const listType = $input.data('list-type');
        
        if (query.length < 2) {
          $input.siblings('.book-search-results').empty();
          return;
        }
        
        // Debounce search
        clearTimeout($input.data('timer'));
        $input.data('timer', setTimeout(function() {
          searchBooks(query, listType, $input);
        }, 300));
      });
      
      // Add book to list
      $(document).on('click', '.add-book-to-list', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const bookId = $btn.data('book-id');
        const bookTitle = $btn.data('book-title');
        const bookCover = $btn.data('book-cover');
        const listType = $btn.data('list-type');
        
        addBookToList(bookId, bookTitle, bookCover, listType);
        
        // Clear search
        $('.book-search-input[data-list-type="' + listType + '"]').val('');
        $('.book-search-results').empty();
      });
      
      // Remove book from list
      $(document).on('click', '.remove-book-from-list', function(e) {
        e.preventDefault();
        $(this).closest('.book-list-item').fadeOut(200, function() {
          $(this).remove();
        });
      });
      
      // ==============================
      // Helper Functions
      // ==============================
      
      /**
       * Close modal
       */
      function closeModal(modalId) {
        $('#' + modalId).fadeOut(200);
        $('body').css('overflow', 'auto');
      }
      
      /**
       * Update profile UI with new data
       */
      function updateProfileUI(data) {
        if (data.first_name && data.last_name) {
          $('.profile-greeting').text('Hi, ' + data.first_name + ' ' + data.last_name);
          
          // Update avatar initials if no picture
          if (!data.picture_url) {
            $('.avatar-initials').text(data.first_name.charAt(0) + data.last_name.charAt(0));
          }
        }
        
        if (data.age) {
          $('.detail-item:has(.detail-icon:contains("🎂")) .detail-text').text(data.age + ' Years');
        }
        
        if (data.currently_reading) {
          $('.detail-item:has(.detail-icon:contains("📖")) .detail-text').text('Currently reading - ' + data.currently_reading);
        }
        
        if (data.picture_url) {
          // Replace placeholder with actual image or update existing image
          const $pictureCard = $('.profile-picture-card');
          $pictureCard.find('.profile-avatar-placeholder').remove();
          $pictureCard.find('.profile-avatar').remove();
          $pictureCard.prepend('<img src="' + data.picture_url + '" alt="Profile picture" class="profile-avatar">');
        }
      }
      
      /**
       * Save favorites (authors, books, or genres)
       */
      function saveFavorites(type, values, modalId) {
        const $submitBtn = $('#' + modalId.replace('-modal', '-form') + ' button[type="submit"]');
        const originalText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
          url: '/storyfulls/profile/update-favorites',
          type: 'POST',
          data: JSON.stringify({
            type: type,
            values: values
          }),
          contentType: 'application/json',
          success: function(response) {
            if (response.success) {
              // Update UI based on type
              updateFavoritesUI(type, values);
              
              closeModal(modalId);
              showMessage('Favorites updated successfully!', 'success');
            } else {
              showMessage(response.message || 'Error updating favorites', 'error');
            }
          },
          error: function() {
            showMessage('Error updating favorites. Please try again.', 'error');
          },
          complete: function() {
            $submitBtn.prop('disabled', false).text(originalText);
          }
        });
      }
      
      /**
       * Update favorites UI
       */
      function updateFavoritesUI(type, values) {
        let $container;
        
        if (type === 'authors') {
          $container = $('.favorite-card:has(.favorite-card-title:contains("Authors")) .favorite-list');
        } else if (type === 'books') {
          $container = $('.favorite-card:has(.favorite-card-title:contains("Books")) .favorite-list');
        } else if (type === 'genres') {
          $container = $('.favorite-card:has(.favorite-card-title:contains("Genres")) .favorite-list.genres-list');
        }
        
        if (!$container.length) return;
        
        $container.empty();
        
        if (values.length === 0) {
          $container.append('<p class="no-favorites">No favorite ' + type + ' yet</p>');
        } else {
          values.forEach(function(value) {
            if (type === 'genres') {
              $container.append('<div class="genre-tag">' + value + '</div>');
            } else {
              $container.append('<div class="favorite-item">' + value + '</div>');
            }
          });
        }
      }
      
      /**
       * Save book list (wishlist or books read)
       */
      function saveBookList(type, books, modalId) {
        const $submitBtn = $('#' + modalId.replace('-modal', '-form') + ' button[type="submit"]');
        const originalText = $submitBtn.text();
        
        $submitBtn.prop('disabled', true).text('Saving...');
        
        $.ajax({
          url: '/storyfulls/profile/update-book-list',
          type: 'POST',
          data: JSON.stringify({
            type: type,
            books: books
          }),
          contentType: 'application/json',
          success: function(response) {
            if (response.success) {
              closeModal(modalId);
              showMessage('Book list updated successfully!', 'success');
              
              // Optionally reload the page to show updated book covers
              // location.reload();
            } else {
              showMessage(response.message || 'Error updating book list', 'error');
            }
          },
          error: function() {
            showMessage('Error updating book list. Please try again.', 'error');
          },
          complete: function() {
            $submitBtn.prop('disabled', false).text(originalText);
          }
        });
      }
      
      /**
       * Search books
       */
      function searchBooks(query, listType, $input) {
        $.ajax({
          url: '/storyfulls/book/search',
          type: 'GET',
          data: { q: query },
          success: function(response) {
            const $results = $input.siblings('.book-search-results');
            $results.empty();
            
            if (response.length === 0) {
              $results.append('<div class="search-result-item">No books found</div>');
            } else {
              response.forEach(function(book) {
                $results.append(
                  '<div class="search-result-item">' +
                    '<img src="' + book.cover_url + '" alt="' + book.title + '" class="search-result-cover">' +
                    '<span class="search-result-title">' + book.title + '</span>' +
                    '<button class="add-book-to-list" data-book-id="' + book.id + '" ' +
                      'data-book-title="' + book.title + '" ' +
                      'data-book-cover="' + book.cover_url + '" ' +
                      'data-list-type="' + listType + '">Add</button>' +
                  '</div>'
                );
              });
            }
          }
        });
      }
      
      /**
       * Add book to list UI
       */
      function addBookToList(bookId, bookTitle, bookCover, listType) {
        const itemClass = listType === 'wishlist' ? 'wishlist-item' : 'books-read-item';
        const $container = $('#edit-' + listType.replace('_', '-') + '-form .book-list-container');
        
        // Check if book already in list
        if ($container.find('[data-book-id="' + bookId + '"]').length > 0) {
          showMessage('This book is already in your list', 'warning');
          return;
        }
        
        $container.append(
          '<div class="book-list-item ' + itemClass + '" data-book-id="' + bookId + '">' +
            '<img src="' + bookCover + '" alt="' + bookTitle + '" class="book-list-cover">' +
            '<span class="book-list-title">' + bookTitle + '</span>' +
            '<button class="remove-book-from-list">&times;</button>' +
          '</div>'
        );
      }
      
      /**
       * Show message notification
       */
      function showMessage(message, type) {
        // Remove any existing messages
        $('.profile-message').remove();
        
        const messageClass = 'profile-message profile-message--' + type;
        const $message = $('<div class="' + messageClass + '">' + message + '</div>');
        
        $('body').append($message);
        
        // Position at top center
        $message.css({
          position: 'fixed',
          top: '20px',
          left: '50%',
          transform: 'translateX(-50%)',
          zIndex: 10001,
          padding: '15px 25px',
          borderRadius: '8px',
          backgroundColor: type === 'success' ? '#4CAF50' : (type === 'error' ? '#f44336' : '#ff9800'),
          color: 'white',
          fontWeight: '500',
          boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
        });
        
        // Fade in
        $message.hide().fadeIn(200);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
          $message.fadeOut(200, function() {
            $(this).remove();
          });
        }, 3000);
      }
      
      /**
       * Initialize autocomplete for author/book inputs
       */
      function initializeAutocomplete(modalName) {
        const isAuthors = modalName === 'edit-authors';
        const endpoint = isAuthors ? '/storyfulls/autocomplete/authors' : '/storyfulls/autocomplete/books';
        const inputSelector = isAuthors ? 'input[name^="author_"]' : 'input[name^="book_"]';
        
        $('#' + modalName + '-form ' + inputSelector).autocomplete({
          source: function(request, response) {
            $.ajax({
              url: endpoint,
              data: { q: request.term },
              success: function(data) {
                response(data);
              }
            });
          },
          minLength: 2,
          select: function(event, ui) {
            $(this).val(ui.item.label);
            return false;
          }
        });
      }
      
      /**
       * Initialize sortable for draggable inputs
       */
      function initializeSortable(modalName) {
        const containerSelector = modalName === 'edit-authors' ? '.authors-sortable' : '.books-sortable';
        
        $('#' + modalName + '-form ' + containerSelector).sortable({
          handle: '.drag-handle',
          placeholder: 'sortable-placeholder',
          axis: 'y',
          cursor: 'move',
          opacity: 0.8
        });
      }
      
    }
  };

})(jQuery, Drupal);
