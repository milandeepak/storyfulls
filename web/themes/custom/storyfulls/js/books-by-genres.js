/**
 * @file
 * Books by Genres functionality
 */

(function($, Drupal, drupalSettings) {
  'use strict';

  console.log('Books by Genres JS loaded');

  // This will be populated from drupalSettings
  let booksByGenre = {};
  
  // Sample book data for fallback (if drupalSettings not available)
  const sampleBooksByGenre = {
    'sports': [
      {
        title: 'Cristiano Ronaldo',
        cover: '/themes/custom/storyfulls/images/genres/sports/cristiano.jpg',
        url: '/books/cristiano-ronaldo'
      },
      {
        title: 'Sports Heroes',
        cover: '/themes/custom/storyfulls/images/genres/sports/sports-heroes.jpg',
        url: '/books/sports-heroes'
      },
      {
        title: 'Football Stories',
        cover: '/themes/custom/storyfulls/images/genres/sports/football.jpg',
        url: '/books/football-stories'
      },
      {
        title: 'Olympic Champions',
        cover: '/themes/custom/storyfulls/images/genres/sports/olympics.jpg',
        url: '/books/olympic-champions'
      },
      {
        title: 'Basketball Legends',
        cover: '/themes/custom/storyfulls/images/genres/sports/basketball.jpg',
        url: '/books/basketball-legends'
      },
      {
        title: 'Tennis Stars',
        cover: '/themes/custom/storyfulls/images/genres/sports/tennis.jpg',
        url: '/books/tennis-stars'
      },
      {
        title: 'Cricket Heroes',
        cover: '/themes/custom/storyfulls/images/genres/sports/cricket.jpg',
        url: '/books/cricket-heroes'
      },
      {
        title: 'Swimming Champions',
        cover: '/themes/custom/storyfulls/images/genres/sports/swimming.jpg',
        url: '/books/swimming-champions'
      }
    ],
    'picture-books': [
      {
        title: 'Cuddle Bug',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/cuddlebug.jpg',
        url: '/books/cuddle-bug'
      },
      {
        title: 'The Giving Tree',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/giving-tree.jpg',
        url: '/books/giving-tree'
      },
      {
        title: 'Gupi Diaries',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/gupi.jpg',
        url: '/books/gupi-diaries'
      },
      {
        title: 'Where The Wild Things Are',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/wild-things.jpg',
        url: '/books/wild-things'
      },
      {
        title: 'The Very Hungry Caterpillar',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/caterpillar.jpg',
        url: '/books/hungry-caterpillar'
      },
      {
        title: 'Goodnight Moon',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/goodnight-moon.jpg',
        url: '/books/goodnight-moon'
      },
      {
        title: 'Brown Bear',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/brown-bear.jpg',
        url: '/books/brown-bear'
      },
      {
        title: 'Corduroy',
        cover: '/themes/custom/storyfulls/images/genres/picture-books/corduroy.jpg',
        url: '/books/corduroy'
      }
    ],
    'non-fiction': [
      {
        title: 'I Am Malala',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/malala.jpg',
        url: '/books/i-am-malala'
      },
      {
        title: 'National Geographic Kids',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/natgeo.jpg',
        url: '/books/natgeo-kids'
      },
      {
        title: 'Who Was Series',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/who-was.jpg',
        url: '/books/who-was'
      },
      {
        title: 'The Magic School Bus',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/magic-bus.jpg',
        url: '/books/magic-school-bus'
      },
      {
        title: 'Amazing Animals',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/animals.jpg',
        url: '/books/amazing-animals'
      },
      {
        title: 'Space Encyclopedia',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/space.jpg',
        url: '/books/space-encyclopedia'
      },
      {
        title: 'Human Body',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/human-body.jpg',
        url: '/books/human-body'
      },
      {
        title: 'Dinosaurs',
        cover: '/themes/custom/storyfulls/images/genres/non-fiction/dinosaurs.jpg',
        url: '/books/dinosaurs'
      }
    ],
    'mystery': [
      {
        title: 'Nancy Drew',
        cover: '/themes/custom/storyfulls/images/genres/mystery/nancy-drew.jpg',
        url: '/books/nancy-drew'
      },
      {
        title: 'Hardy Boys',
        cover: '/themes/custom/storyfulls/images/genres/mystery/hardy-boys.jpg',
        url: '/books/hardy-boys'
      },
      {
        title: 'Encyclopedia Brown',
        cover: '/themes/custom/storyfulls/images/genres/mystery/encyclopedia-brown.jpg',
        url: '/books/encyclopedia-brown'
      },
      {
        title: 'Sherlock Holmes',
        cover: '/themes/custom/storyfulls/images/genres/mystery/sherlock.jpg',
        url: '/books/sherlock-holmes'
      },
      {
        title: 'The 39 Clues',
        cover: '/themes/custom/storyfulls/images/genres/mystery/39-clues.jpg',
        url: '/books/39-clues'
      },
      {
        title: 'Cam Jansen',
        cover: '/themes/custom/storyfulls/images/genres/mystery/cam-jansen.jpg',
        url: '/books/cam-jansen'
      },
      {
        title: 'A to Z Mysteries',
        cover: '/themes/custom/storyfulls/images/genres/mystery/a-to-z.jpg',
        url: '/books/a-to-z-mysteries'
      },
      {
        title: 'Nate The Great',
        cover: '/themes/custom/storyfulls/images/genres/mystery/nate.jpg',
        url: '/books/nate-the-great'
      }
    ],
    'comics': [
      {
        title: 'Dog Man',
        cover: '/themes/custom/storyfulls/images/genres/comics/dog-man.jpg',
        url: '/books/dog-man'
      },
      {
        title: 'Captain Underpants',
        cover: '/themes/custom/storyfulls/images/genres/comics/captain-underpants.jpg',
        url: '/books/captain-underpants'
      },
      {
        title: 'Diary of a Wimpy Kid',
        cover: '/themes/custom/storyfulls/images/genres/comics/wimpy-kid.jpg',
        url: '/books/wimpy-kid'
      },
      {
        title: 'Big Nate',
        cover: '/themes/custom/storyfulls/images/genres/comics/big-nate.jpg',
        url: '/books/big-nate'
      },
      {
        title: 'Bone',
        cover: '/themes/custom/storyfulls/images/genres/comics/bone.jpg',
        url: '/books/bone'
      },
      {
        title: 'Amulet',
        cover: '/themes/custom/storyfulls/images/genres/comics/amulet.jpg',
        url: '/books/amulet'
      },
      {
        title: 'Tintin',
        cover: '/themes/custom/storyfulls/images/genres/comics/tintin.jpg',
        url: '/books/tintin'
      },
      {
        title: 'Asterix',
        cover: '/themes/custom/storyfulls/images/genres/comics/asterix.jpg',
        url: '/books/asterix'
      }
    ],
    'comedy': [
      {
        title: 'Bad Girls',
        cover: '/themes/custom/storyfulls/images/genres/comedy/bad-girls.jpg',
        url: '/books/bad-girls'
      },
      {
        title: 'The Runaway Bunny',
        cover: '/themes/custom/storyfulls/images/genres/comedy/runaway-bunny.jpg',
        url: '/books/runaway-bunny'
      },
      {
        title: 'The Feather Chase',
        cover: '/themes/custom/storyfulls/images/genres/comedy/feather-chase.jpg',
        url: '/books/feather-chase'
      },
      {
        title: 'Junie B Jones',
        cover: '/themes/custom/storyfulls/images/genres/comedy/junie-b.jpg',
        url: '/books/junie-b-jones'
      },
      {
        title: 'Amelia Bedelia',
        cover: '/themes/custom/storyfulls/images/genres/comedy/amelia-bedelia.jpg',
        url: '/books/amelia-bedelia'
      },
      {
        title: 'Wayside School',
        cover: '/themes/custom/storyfulls/images/genres/comedy/wayside.jpg',
        url: '/books/wayside-school'
      },
      {
        title: 'Bunnicula',
        cover: '/themes/custom/storyfulls/images/genres/comedy/bunnicula.jpg',
        url: '/books/bunnicula'
      },
      {
        title: 'The BFG',
        cover: '/themes/custom/storyfulls/images/genres/comedy/bfg.jpg',
        url: '/books/bfg'
      }
    ]
  };

  // Pastel background colors for book covers (cycle through them)
  const pastelColors = [
    '#FFE4E1', // Misty Rose
    '#E0F8E0', // Light Green
    '#E6E6FA', // Lavender
    '#FFE4B5', // Moccasin
    '#E0F2F7', // Light Blue
    '#FFF0F5'  // Lavender Blush
  ];

  $(document).ready(function() {
    console.log('Books by Genres: Document ready');

    // Check if the section exists on this page
    if ($('.books-by-genres-section').length === 0) {
      console.log('Books by Genres section not found on this page');
      return;
    }

    console.log('Books by Genres section found, initializing...');

    // Get books data from drupalSettings
    console.log('drupalSettings:', drupalSettings);
    booksByGenre = drupalSettings.booksByGenre || sampleBooksByGenre;
    
    console.log('booksByGenre keys:', Object.keys(booksByGenre));
    console.log('booksByGenre data (first 2):', Object.keys(booksByGenre).slice(0, 2).reduce((obj, key) => {
      obj[key] = booksByGenre[key];
      return obj;
    }, {}));
    
    if (Object.keys(booksByGenre).length === 0) {
      console.warn('WARNING: No booksByGenre data found, using sample data');
      booksByGenre = sampleBooksByGenre;
    }
    
    console.log('Books data loaded successfully!');

    // Load initial genre - use the first available genre or 'sports' as fallback
    const initialGenre = Object.keys(booksByGenre)[0] || 'sports';
    console.log('Loading initial genre:', initialGenre);
    loadGenreBooks(initialGenre);
    
    // Set the first tab as active
    $('.genre-tab').first().addClass('active');

    // Genre tab click handler
    $('.genre-tab').on('click', function() {
      console.log('Genre tab clicked:', $(this).data('genre'));
      
      // If this is the More Options button, toggle dropdown
      if ($(this).hasClass('more-options')) {
        const dropdown = $('#genre-dropdown-menu');
        dropdown.toggleClass('show');
        $(this).toggleClass('active');
        return;
      }
      
      // Remove active class from all tabs (including dropdown items)
      $('.genre-tab').removeClass('active');
      
      // Add active class to clicked tab
      $(this).addClass('active');
      
      // Close dropdown if open
      $('#genre-dropdown-menu').removeClass('show');
      $('#more-options-btn').removeClass('active');
      
      // Get selected genre
      const genre = $(this).data('genre');
      
      // Load books for selected genre
      loadGenreBooks(genre);
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(event) {
      if (!$(event.target).closest('.genre-more-options-wrapper').length) {
        $('#genre-dropdown-menu').removeClass('show');
        // Only remove active from More Options if no genre from dropdown is active
        if (!$('.genre-dropdown-item.active').length) {
          $('#more-options-btn').removeClass('active');
        }
      }
    });

    // Scroll arrow click handler
    $('#scroll-right-arrow').on('click', function() {
      console.log('Scroll arrow clicked');
      const grid = $('#genre-books-grid');
      const scrollAmount = 600; // Scroll by ~2 books
      grid.animate({
        scrollLeft: grid.scrollLeft() + scrollAmount
      }, 400);
    });

    // Monitor scroll position to show/hide arrow
    $('#genre-books-grid').on('scroll', function() {
      const grid = $(this);
      const scrollLeft = grid.scrollLeft();
      const scrollWidth = grid[0].scrollWidth;
      const clientWidth = grid[0].clientWidth;
      
      // Hide arrow if scrolled to the end
      if (scrollLeft + clientWidth >= scrollWidth - 10) {
        $('#scroll-right-arrow').addClass('hidden');
      } else {
        $('#scroll-right-arrow').removeClass('hidden');
      }
    });
  });

  /**
   * Load books for a specific genre
   */
  function loadGenreBooks(genre) {
    console.log('Loading books for genre:', genre);
    
    const grid = $('#genre-books-grid');
    const loading = $('#genre-loading');
    const noBooks = $('#genre-no-books');
    const scrollArrow = $('#scroll-right-arrow');
    
    // Show loading state
    grid.hide();
    noBooks.hide();
    scrollArrow.hide();
    loading.show();
    
    // Simulate API delay (remove in production if using real API)
    setTimeout(function() {
      const books = booksByGenre[genre] || [];
      
      if (books.length === 0) {
        // No books available
        grid.hide();
        loading.hide();
        scrollArrow.hide();
        noBooks.show();
        return;
      }
      
      // Clear existing books
      grid.empty();
      
      // Add books to grid
      books.forEach(function(book, index) {
        const bgColor = pastelColors[index % pastelColors.length];
        const coverUrl = book.cover_url || book.cover || ''; // Support both cover_url and cover
        
        console.log('Book:', book.title, 'Cover URL:', coverUrl);
        
        let bookCard;
        if (coverUrl) {
          bookCard = `
            <a href="${book.url}" class="genre-book-card">
              <div class="genre-book-cover-wrapper" style="background: ${bgColor};">
                <img src="${coverUrl}" 
                     alt="${book.title}" 
                     class="genre-book-cover"
                     onerror="console.error('Failed to load image:', '${coverUrl}'); this.style.display='none'; this.parentElement.innerHTML='<div class=\\'genre-book-cover-placeholder\\'>${book.title}</div>';">
              </div>
            </a>
          `;
        } else {
          // No cover available, show placeholder
          console.warn('No cover URL for book:', book.title);
          bookCard = `
            <a href="${book.url}" class="genre-book-card">
              <div class="genre-book-cover-wrapper" style="background: ${bgColor};">
                <div class="genre-book-cover-placeholder">${book.title}</div>
              </div>
            </a>
          `;
        }
        grid.append(bookCard);
      });
      
      // Hide loading, show grid
      loading.hide();
      grid.show();
      
      // Check if scroll arrow is needed - wait for DOM to fully render
      setTimeout(function() {
        const gridElement = grid[0];
        const isScrollable = gridElement.scrollWidth > gridElement.clientWidth + 5; // 5px tolerance
        
        console.log('=== Scroll Arrow Check ===');
        console.log('Grid scrollWidth:', gridElement.scrollWidth);
        console.log('Grid clientWidth:', gridElement.clientWidth);
        console.log('Is scrollable:', isScrollable);
        console.log('Books loaded:', books.length);
        
        if (isScrollable) {
          scrollArrow.removeClass('hidden');
          console.log('✓ Scroll arrow SHOWN');
        } else {
          scrollArrow.addClass('hidden');
          console.log('✗ Scroll arrow HIDDEN - no overflow');
        }
        
        // Reset scroll position
        grid.scrollLeft(0);
      }, 200); // Increased timeout to ensure rendering is complete
      
      console.log(`Loaded ${books.length} books for genre: ${genre}`);
    }, 300);
  }

})(jQuery, Drupal, drupalSettings);
