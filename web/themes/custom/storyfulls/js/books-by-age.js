(function ($, Drupal, drupalSettings) {
  'use strict';

  console.log('=== BOOKS BY AGE: Script file loaded ===');

  Drupal.behaviors.booksByAge = {
    attach: function (context, settings) {
      console.log('=== BOOKS BY AGE: Attach called ===');
      console.log('Context:', context);
      console.log('Looking for #filtered-books-grid...');
      
      // Only run on pages that have the filtered books grid (homepage)
      var $grid = $('#filtered-books-grid', context);
      console.log('Grid found:', $grid.length);
      
      if ($grid.length === 0) {
        console.log('=== BOOKS BY AGE: Skipping - grid not found ===');
        return;
      }
      
      console.log('=== BOOKS BY AGE SCRIPT ATTACHED ===');
      
      // Track currently selected age group
      var currentAgeGroup = 'general';
      
      // Get books data from drupalSettings
      console.log('drupalSettings:', drupalSettings);
      var booksByAge = drupalSettings.booksByAge || {};
      
      console.log('booksByAge keys:', Object.keys(booksByAge));
      console.log('booksByAge data:', booksByAge);
      
      if (Object.keys(booksByAge).length === 0) {
        console.error('ERROR: No booksByAge data found!');
        console.error('Available drupalSettings:', Object.keys(drupalSettings));
        return;
      }
      
      console.log('Books data loaded successfully!');
      
      // Function to update the View All link
      function updateViewAllLink(ageGroup) {
        var $viewAllLink = $('#age-view-all-link');
        if (ageGroup === 'general') {
          $viewAllLink.attr('href', '/books');
        } else {
          $viewAllLink.attr('href', '/books?age=' + ageGroup);
        }
      }
      
      // Function to display books for a specific age
      function showBooksForAge(selectedAge) {
        console.log('>>> showBooksForAge called with:', selectedAge);
        
        currentAgeGroup = selectedAge;
        updateViewAllLink(selectedAge);
        
        var booksForAge = booksByAge[selectedAge] || [];
        
        console.log('>>> Found ' + booksForAge.length + ' books for age ' + selectedAge);
        console.log('>>> Available age groups:', Object.keys(booksByAge));
        console.log('>>> Sample book:', booksForAge[0]);
        
        // Clear the filtered books grid
        $grid.empty();
        
        if (booksForAge.length > 0) {
          console.log('>>> Adding books to grid...');
          
          booksForAge.forEach(function(book, index) {
            console.log('>>> Adding book', index, ':', book.title);
            
            var bookCard = $('<a></a>')
              .attr('href', book.url)
              .addClass('book-card');
            
            if (book.cover_url) {
              $('<img>')
                .attr('src', book.cover_url)
                .attr('alt', book.title)
                .attr('loading', 'lazy')
                .addClass('book-cover')
                .appendTo(bookCard);
            } else {
              $('<div></div>')
                .addClass('book-cover-placeholder')
                .text('No Cover')
                .appendTo(bookCard);
            }
            
            $('<div></div>')
              .addClass('book-card-title')
              .text(book.title)
              .appendTo(bookCard);
            
            $grid.append(bookCard);
          });
          
          console.log('>>> Books added successfully!');
          console.log('>>> Grid HTML:', $grid.html().substring(0, 200));
        } else {
          console.log('>>> No books found, showing message');
          $grid.append('<p class="no-books">No books available for this age group yet.</p>');
        }
        
        // Show filtered section
        $('#age-filtered-books').show();
        console.log('>>> Section displayed');
      }
      
      // Handle age group clicks - only for homepage age section
      // Use off/on to prevent duplicate handlers
      $('.books-by-age-section .age-group-item', context).off('click.booksbyage').on('click.booksbyage', function(e) {
        e.preventDefault();
        var selectedAge = $(this).data('age');
        
        console.log('>>> Age avatar clicked:', selectedAge);
        
        // Don't process if it's the "View All" item
        if ($(this).hasClass('view-all-link')) {
          return;
        }
        
        // Remove active class from all age items in this section
        $('.books-by-age-section .age-group-item').removeClass('active');
        
        // Add active class to clicked item
        $(this).addClass('active');
        
        // Show books for this age
        showBooksForAge(selectedAge);
      });
      
      // **DEFAULT: Show general books on page load**
      console.log('>>> Loading general books initially');
      showBooksForAge('general');
    }
  };

})(jQuery, Drupal, drupalSettings);
