/**
 * Edit Favorites Pages JavaScript
 */

(function ($, Drupal, once, Sortable) {
  'use strict';

  // Helper: Create new author row
  function createAuthorRow() {
    const row = document.createElement('div');
    row.className = 'favorite-item-row';
    row.innerHTML = `
      <span class="drag-handle">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M9 5H11V7H9V5ZM13 5H15V7H13V5ZM9 11H11V13H9V11ZM13 11H15V13H13V11ZM9 17H11V19H9V17ZM13 17H15V19H13V17Z" fill="currentColor"/>
        </svg>
      </span>
      <input type="text" class="favorite-input" placeholder="Author name">
      <button type="button" class="remove-favorite-btn" aria-label="Remove">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    `;
    return row;
  }

  // Helper: Initialize author autocomplete
  function initAuthorAutocomplete($input) {
    $input.autocomplete({
      source: function(request, response) {
        $.ajax({
          url: '/storyfulls/autocomplete/authors',
          data: { q: request.term },
          dataType: 'json',
          success: function(data) {
            response($.map(data, function(item) {
              return {
                label: item.label,
                value: item.value
              };
            }));
          },
          error: function() {
            response([]);
          }
        });
      },
      minLength: 2,
      delay: 300
    });
  }

  Drupal.behaviors.editFavorites = {
    attach: function (context, settings) {
      
      // ===== EDIT AUTHORS PAGE =====
      
      // Make authors list sortable using SortableJS
      once('authors-sortable', '#authorsList', context).forEach(function(list) {
        new Sortable(list, {
          handle: '.drag-handle',
          animation: 150,
          ghostClass: 'sortable-ghost',
          dragClass: 'sortable-drag',
        });
      });
      
      // Add new author field
      once('add-author-btn', '#addAuthorBtn', context).forEach(function(button) {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          
          const list = document.getElementById('authorsList');
          if (!list) {
            console.error('Authors list not found');
            return;
          }
          
          const itemCount = list.querySelectorAll('.favorite-item-row').length;
          
          if (itemCount >= 5) {
            alert('You can only add up to 5 favorite authors.');
            return;
          }
          
          const newRow = createAuthorRow();
          list.appendChild(newRow);
          
          // Initialize autocomplete for the new input
          initAuthorAutocomplete($(newRow).find('.favorite-input'));
        });
      });
      
      // Initialize autocomplete for existing author inputs
      once('author-autocomplete-init', '#authorsList', context).forEach(function(list) {
        $(list).find('.favorite-input').each(function() {
          initAuthorAutocomplete($(this));
        });
      });
      
      // Remove author
      $(context).on('click', '.remove-favorite-btn', function() {
        const row = $(this).closest('.favorite-item-row');
        const list = document.getElementById('authorsList');
        
        if (list && list.querySelectorAll('.favorite-item-row').length <= 1) {
          alert('You must have at least one author field.');
          return;
        }
        
        row.fadeOut(300, function() {
          $(this).remove();
        });
      });
      
      // Submit authors form
      once('submit-authors', '#editAuthorsForm', context).forEach(function(form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const inputs = form.querySelectorAll('.favorite-input');
          const authors = [];
          
          inputs.forEach(function(input) {
            const value = input.value.trim();
            if (value) {
              authors.push(value);
            }
          });
          
          // Set hidden field value
          document.getElementById('authorsData').value = JSON.stringify(authors);
          
          // Submit form
          form.submit();
        });
      });
      
      // ===== EDIT BOOKS PAGE =====
      
      // Book search with debounce
      once('book-search', '#bookSearchInput', context).forEach(function(input) {
        let searchTimeout;
        
        input.addEventListener('input', function(e) {
          clearTimeout(searchTimeout);
          const query = e.target.value.trim();
          
          if (query.length < 2) {
            document.getElementById('bookSearchResults').classList.remove('show');
            return;
          }
          
          searchTimeout = setTimeout(function() {
            searchBooks(query);
          }, 300);
        });
      });
      
      // Remove book
      $(context).on('click', '.remove-book-btn', function() {
        const card = $(this).closest('.book-item-card');
        card.fadeOut(300, function() {
          $(this).remove();
        });
      });
      
      // Submit books form
      once('submit-books', '#editBooksForm', context).forEach(function(form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const cards = form.querySelectorAll('.book-item-card');
          const bookIds = [];
          
          cards.forEach(function(card) {
            const id = card.getAttribute('data-id');
            if (id) {
              bookIds.push(id);
            }
          });
          
          // Set hidden field value
          document.getElementById('booksData').value = JSON.stringify(bookIds);
          
          // Submit form
          form.submit();
        });
      });
      
      // ===== EDIT GENRES PAGE =====
      
      // Handle checkbox change to update visual state
      // Since checkbox is inside label, clicking the label will automatically toggle it
      $(context).find('.genre-checkbox-card input[type="checkbox"]').on('change', function() {
        const card = $(this).closest('.genre-checkbox-card');
        if (this.checked) {
          card.addClass('selected');
        } else {
          card.removeClass('selected');
        }
      });
      
    }
  };
  
  // Helper: Search books
  function searchBooks(query) {
    $.ajax({
      url: '/storyfulls/book/search',
      data: { q: query },
      method: 'GET',
      success: function(results) {
        displaySearchResults(results);
      },
      error: function() {
        console.error('Failed to search books');
      }
    });
  }
  
  // Helper: Display search results
  function displaySearchResults(results) {
    const resultsContainer = document.getElementById('bookSearchResults');
    
    if (!results || results.length === 0) {
      resultsContainer.innerHTML = '<p class="no-results">No books found.</p>';
      resultsContainer.classList.add('show');
      return;
    }
    
    const existingIds = Array.from(document.querySelectorAll('.book-item-card')).map(card => card.getAttribute('data-id'));
    
    let html = '';
    results.forEach(function(book) {
      const isAdded = existingIds.includes(book.id.toString());
      const addedClass = isAdded ? 'added' : '';
      
      html += `
        <div class="search-result-item ${addedClass}" data-id="${book.id}" data-title="${book.title}" data-cover="${book.cover_url || ''}">
          <div class="search-result-cover">
            ${book.cover_url ? `<img src="${book.cover_url}" alt="${book.title}">` : '<div class="book-cover-placeholder"></div>'}
          </div>
          <div class="search-result-info">
            <h4 class="search-result-title">${book.title}</h4>
          </div>
        </div>
      `;
    });
    
    resultsContainer.innerHTML = html;
    resultsContainer.classList.add('show');
    
    // Add click handlers to results
    resultsContainer.querySelectorAll('.search-result-item:not(.added)').forEach(function(item) {
      item.addEventListener('click', function() {
        addBookToList(this);
      });
    });
  }
  
  // Helper: Add book to list
  function addBookToList(resultItem) {
    const booksList = document.getElementById('booksList');
    const itemCount = booksList.querySelectorAll('.book-item-card').length;
    
    if (itemCount >= 5) {
      alert('You can only add up to 5 favorite books.');
      return;
    }
    
    const id = resultItem.getAttribute('data-id');
    const title = resultItem.getAttribute('data-title');
    const coverUrl = resultItem.getAttribute('data-cover');
    
    const bookCard = document.createElement('div');
    bookCard.className = 'book-item-card';
    bookCard.setAttribute('data-id', id);
    bookCard.innerHTML = `
      <div class="book-cover">
        ${coverUrl ? `<img src="${coverUrl}" alt="${title}">` : `
          <div class="book-cover-placeholder">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
              <path d="M10 6H30V34H10V6Z" stroke="currentColor" stroke-width="2"/>
              <path d="M14 12H26M14 18H26M14 24H22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
        `}
      </div>
      <div class="book-info">
        <h4 class="book-title">${title}</h4>
      </div>
      <button type="button" class="remove-book-btn" aria-label="Remove">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    `;
    
    booksList.appendChild(bookCard);
    
    // Mark as added in search results
    resultItem.classList.add('added');
    
    // Clear search
    document.getElementById('bookSearchInput').value = '';
    document.getElementById('bookSearchResults').classList.remove('show');
  }

})(jQuery, Drupal, once, Sortable);
