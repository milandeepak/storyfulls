(function ($, Drupal, drupalSettings) {
  'use strict';

  console.log('Books listing JS file loaded');

  Drupal.behaviors.booksListing = {
    attach: function (context, settings) {
      console.log('Drupal.behaviors.booksListing.attach called', context);
      
      // Only run once on document
      if (context !== document) {
        console.log('Skipping - context is not document');
        return;
      }

      console.log('Initializing books listing behavior');

      // Initialize variables - default to all age groups
      let currentAge = 'all';
      let currentSearch = '';
      let currentGenre = '';
      let currentPage = 1;
      const booksPerPage = 24; // Show 24 books per page (4x6 grid)

      // Check URL parameters for age group and search
      const urlParams = new URLSearchParams(window.location.search);
      const ageFromUrl = urlParams.get('age');
      const searchFromUrl = urlParams.get('keys');
      
      if (ageFromUrl) {
        // Age group passed from homepage
        currentAge = ageFromUrl;
        console.log('Pre-selected age from URL:', ageFromUrl);
        
        // Set the active age group in the UI
        $('.age-group-item').removeClass('active');
        $('.age-group-item[data-age="' + ageFromUrl + '"]').addClass('active');
      } else {
        // No age specified, show all - no age group is selected
        $('.age-group-item').removeClass('active');
        console.log('No age specified, showing all books');
      }
      
      if (searchFromUrl) {
        currentSearch = searchFromUrl.toLowerCase().trim();
        $('#book-search').val(searchFromUrl);
        console.log('Pre-filled search from URL:', searchFromUrl);
      }

      console.log('Initial currentAge:', currentAge);
      console.log('Age group items found:', $('.age-group-item').length);

      // Initial filter on page load
      filterBooks();

      // Age group filter - use direct event delegation on document
      $(document).off('click.ageFilter').on('click.ageFilter', '.age-group-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('=== AGE FILTER CLICKED ===');
        console.log('Clicked element:', this);
        console.log('Data-age attribute:', $(this).attr('data-age'));
        console.log('Data-age via .data():', $(this).data('age'));
        
        $('.age-group-item').removeClass('active');
        $(this).addClass('active');
        currentAge = $(this).data('age');
        currentPage = 1; // Reset to first page when changing age
        
        console.log('New currentAge:', currentAge);
        console.log('Active class applied to:', $('.age-group-item.active').data('age'));
        
        filterBooks();
      });

      console.log('Age filter click handler registered');

      // Apply filters button
      $(document).off('click.applyFilters').on('click.applyFilters', '#apply-filters', function(e) {
        e.preventDefault();
        currentSearch = $('#book-search').val().toLowerCase().trim();
        currentGenre = $('#genre-filter').val();
        currentPage = 1; // Reset to first page when applying filters
        console.log('Apply clicked - Search:', currentSearch, 'Genre:', currentGenre);
        filterBooks();
      });

      // Enter key on search input
      $(document).off('keypress.searchEnter').on('keypress.searchEnter', '#book-search', function(e) {
        if (e.which === 13) {
          e.preventDefault();
          $('#apply-filters').click();
        }
      });

      // Pagination click handlers
      $(document).off('click.pagination').on('click.pagination', '.pagination-btn', function(e) {
        e.preventDefault();
        const action = $(this).data('page');
        
        if (action === 'prev' && currentPage > 1) {
          currentPage--;
          filterBooks();
          scrollToTop();
        } else if (action === 'next') {
          const totalVisible = $('.book-item:visible').length;
          const totalPages = Math.ceil(totalVisible / booksPerPage);
          if (currentPage < totalPages) {
            currentPage++;
            filterBooks();
            scrollToTop();
          }
        } else if (typeof action === 'number') {
          currentPage = action;
          filterBooks();
          scrollToTop();
        }
      });

      // Scroll to top of books grid
      function scrollToTop() {
        $('html, body').animate({
          scrollTop: $('#books-grid').offset().top - 100
        }, 300);
      }

      // Filter books function
      function filterBooks() {
        console.log('=== FILTERING BOOKS ===');
        console.log('Current filters - Age:', currentAge, 'Genre:', currentGenre, 'Search:', currentSearch);
        
        let visibleCount = 0;
        const allBooks = $('.book-item');
        const matchedBooks = [];
        console.log('Total books found:', allBooks.length);

        // First pass: filter and collect matched books
        allBooks.each(function(index) {
          const $book = $(this);
          const bookAge = String($book.data('age')).trim();
          const bookGenres = $book.data('genre') ? $book.data('genre').toString().split(',') : [];
          const bookTitle = String($book.data('title') || '').toLowerCase();
          const bookAuthor = String($book.data('author') || '').toLowerCase();

          if (index < 3) {
            console.log('Book', index, '- Title:', bookTitle, 'Age:', bookAge, 'Matches:', bookAge === currentAge);
          }

          let showBook = true;

          // Filter by age (always filter by selected age, unless "all")
          if (currentAge !== 'all' && bookAge !== currentAge) {
            showBook = false;
          }

          // Filter by genre
          if (currentGenre && !bookGenres.includes(currentGenre)) {
            showBook = false;
          }

          // Filter by search
          if (currentSearch) {
            if (!bookTitle.includes(currentSearch) && !bookAuthor.includes(currentSearch)) {
              showBook = false;
            }
          }

          if (showBook) {
            matchedBooks.push($book);
            visibleCount++;
          }
        });

        console.log('Matched books:', visibleCount);

        // Hide all books first
        allBooks.hide();

        // Calculate pagination
        const totalPages = Math.ceil(visibleCount / booksPerPage);
        const startIndex = (currentPage - 1) * booksPerPage;
        const endIndex = Math.min(startIndex + booksPerPage, visibleCount);

        // Show only books for current page
        for (let i = startIndex; i < endIndex; i++) {
          matchedBooks[i].show();
        }

        console.log('Showing books', startIndex + 1, 'to', endIndex, 'of', visibleCount);

        // Update pagination controls
        updatePagination(visibleCount, totalPages);

        // Show/hide no results message
        if (visibleCount === 0) {
          $('#no-results').show();
          $('#pagination-controls').hide();
        } else {
          $('#no-results').hide();
        }
      }

      // Update pagination controls
      function updatePagination(totalBooks, totalPages) {
        const $pagination = $('#pagination-controls');
        
        if (totalPages <= 1) {
          $pagination.hide();
          return;
        }

        $pagination.show();
        $pagination.empty();

        // Previous button
        const prevDisabled = currentPage === 1 ? ' disabled' : '';
        $pagination.append(`<button class="pagination-btn${prevDisabled}" data-page="prev">Previous</button>`);

        // Page numbers (show max 5 pages)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
          startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
          $pagination.append(`<button class="pagination-btn" data-page="1">1</button>`);
          if (startPage > 2) {
            $pagination.append(`<span class="pagination-ellipsis">...</span>`);
          }
        }

        for (let i = startPage; i <= endPage; i++) {
          const activeClass = i === currentPage ? ' active' : '';
          $pagination.append(`<button class="pagination-btn${activeClass}" data-page="${i}">${i}</button>`);
        }

        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            $pagination.append(`<span class="pagination-ellipsis">...</span>`);
          }
          $pagination.append(`<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`);
        }

        // Next button
        const nextDisabled = currentPage === totalPages ? ' disabled' : '';
        $pagination.append(`<button class="pagination-btn${nextDisabled}" data-page="next">Next</button>`);

        // Showing X-Y of Z books
        const startIndex = (currentPage - 1) * booksPerPage + 1;
        const endIndex = Math.min(currentPage * booksPerPage, totalBooks);
        $pagination.append(`<span class="pagination-info">Showing ${startIndex}-${endIndex} of ${totalBooks} books</span>`);
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
