(function ($, Drupal) {
  'use strict';

  // Global state variables
  var reviewersData = [];
  var filteredData = [];
  var currentPage = 0;
  var itemsPerPage = 12;
  var dataLoaded = false;
  
  var writersData = [];
  var filteredWritersData = [];
  var currentWritersPage = 0;
  var writersDataLoaded = false;

  var artistsData = [];
  var filteredArtistsData = [];
  var currentArtistsPage = 0;
  var artistsDataLoaded = false;

  // Document ready handler - primary implementation
  $(document).ready(function() {
    console.log('Young Writers: Document ready');
    
    // Book Reviews Reveal Handler
    var $cardLink = $('.card-link[data-reveal-target="book-reviews-reveal"]');
    console.log('Found card links:', $cardLink.length);
    
    $cardLink.on('click', function(e) {
      e.preventDefault();
      console.log('Book Reviews card clicked!');
      
      var $revealSection = $('#book-reviews-reveal');
      console.log('Reveal section found:', $revealSection.length);
      
      if ($revealSection.is(':visible')) {
        // Hide if already visible
        console.log('Hiding reveal section');
        $revealSection.fadeOut(300);
        return;
      }
      
      // Close other reveal sections
      $('.reviewers-reveal-section').not($revealSection).hide();
      
      // Show loading state
      console.log('Showing reveal section');
      $revealSection.fadeIn(300);
      
      // Only load data once
      if (!dataLoaded) {
        $('.reviewers-grid:not(.stories-poetry-grid)').html('<div class="loading-spinner">Loading reviewers...</div>');
        
        // Fetch reviewers data
        console.log('Fetching reviewers data...');
        $.ajax({
          url: '/young-writers/api/get-reviewers',
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            console.log('Reviewers data received:', data);
            reviewersData = data;
            filteredData = data;
            currentPage = 0;
            dataLoaded = true;
            renderReviewers();
          },
          error: function(xhr, status, error) {
            console.error('Error loading reviewers:', status, error);
            $('.reviewers-grid:not(.stories-poetry-grid)').html('<div class="error-message">Unable to load reviewers. Please try again later.</div>');
          }
        });
      }
    });

    // Stories & Poetry Reveal Handler
    var $storyCardLink = $('.card-link[data-reveal-target="stories-poetry-reveal"]');
    
    $storyCardLink.on('click', function(e) {
      e.preventDefault();
      console.log('Stories & Poetry card clicked!');
      
      var $revealSection = $('#stories-poetry-reveal');
      
      if ($revealSection.is(':visible')) {
        $revealSection.fadeOut(300);
        return;
      }
      
      // Close other reveal sections
      $('.reviewers-reveal-section').not($revealSection).hide();
      
      $revealSection.fadeIn(300);
      
      if (!writersDataLoaded) {
        $('#stories-poetry-grid').html('<div class="loading-spinner">Loading writers...</div>');
        
        // Fetch writers data from the dedicated API
         $.ajax({
          url: '/young-writers/api/get-writers', 
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            console.log('Writers data received:', data);
            writersData = data;
            filteredWritersData = data;
            currentWritersPage = 0;
            writersDataLoaded = true;
            renderWriters();
          },
          error: function(xhr, status, error) {
            console.error('Error loading writers:', status, error);
            $('#stories-poetry-grid').html('<div class="error-message">Unable to load writers.</div>');
          }
        });
      }
    });

    // Junior Artists Reveal Handler
    var $artistCardLink = $('.card-link[data-reveal-target="junior-artists-reveal"]');
    
    $artistCardLink.on('click', function(e) {
      e.preventDefault();
      console.log('Junior Artists card clicked!');
      
      var $revealSection = $('#junior-artists-reveal');
      
      if ($revealSection.is(':visible')) {
        $revealSection.fadeOut(300);
        return;
      }
      
      // Close other reveal sections
      $('.reviewers-reveal-section').not($revealSection).hide();
      
      $revealSection.fadeIn(300);
      
      if (!artistsDataLoaded) {
        $('#junior-artists-grid').html('<div class="loading-spinner">Loading artists...</div>');
        
        // Fetch artists data
         $.ajax({
          url: '/young-writers/api/get-junior-artists', 
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            console.log('Artists data received:', data);
            artistsData = data;
            filteredArtistsData = data;
            currentArtistsPage = 0;
            artistsDataLoaded = true;
            renderArtists();
          },
          error: function(xhr, status, error) {
            console.error('Error loading artists:', status, error);
            $('#junior-artists-grid').html('<div class="error-message">Unable to load artists.</div>');
          }
        });
      }
    });

    // Search functionality for Reviewers
    $('#reviewer-search').on('input', function() {
      var searchTerm = $(this).val().toLowerCase();
      
      if (searchTerm === '') {
        filteredData = reviewersData;
        currentPage = 0;
        renderReviewers();
        return;
      }
      
      filteredData = reviewersData.filter(function(user) {
        return user.name.toLowerCase().indexOf(searchTerm) !== -1;
      });
      
      currentPage = 0;
      renderReviewers();
    });

    // Search functionality for Writers
    $('#story-writer-search').on('input', function() {
      var searchTerm = $(this).val().toLowerCase();
      
      if (searchTerm === '') {
        filteredWritersData = writersData;
        currentWritersPage = 0;
        renderWriters();
        return;
      }
      
      filteredWritersData = writersData.filter(function(user) {
        return user.name.toLowerCase().indexOf(searchTerm) !== -1;
      });
      
      currentWritersPage = 0;
      renderWriters();
    });

    // Search functionality for Artists
    $('#artist-search').on('input', function() {
      var searchTerm = $(this).val().toLowerCase();
      
      if (searchTerm === '') {
        filteredArtistsData = artistsData;
        currentArtistsPage = 0;
        renderArtists();
        return;
      }
      
      filteredArtistsData = artistsData.filter(function(user) {
        return user.name.toLowerCase().indexOf(searchTerm) !== -1;
      });
      
      currentArtistsPage = 0;
      renderArtists();
    });

    // Navigation buttons (Reviewers)
    $('#reviewers-prev').on('click', function() {
      if (currentPage > 0) {
        currentPage--;
        renderReviewers();
      }
    });

    $('#reviewers-next').on('click', function() {
      var maxPage = Math.ceil(filteredData.length / itemsPerPage) - 1;
      if (currentPage < maxPage) {
        currentPage++;
        renderReviewers();
      }
    });

    // Navigation buttons (Writers)
    $('#writers-prev').on('click', function() {
      if (currentWritersPage > 0) {
        currentWritersPage--;
        renderWriters();
      }
    });

    $('#writers-next').on('click', function() {
      var maxPage = Math.ceil(filteredWritersData.length / itemsPerPage) - 1;
      if (currentWritersPage < maxPage) {
        currentWritersPage++;
        renderWriters();
      }
    });
    
    // Navigation buttons (Artists)
    $('#artists-prev').on('click', function() {
      if (currentArtistsPage > 0) {
        currentArtistsPage--;
        renderArtists();
      }
    });

    $('#artists-next').on('click', function() {
      var maxPage = Math.ceil(filteredArtistsData.length / itemsPerPage) - 1;
      if (currentArtistsPage < maxPage) {
        currentArtistsPage++;
        renderArtists();
      }
    });


    // Share your work button
    $('.share-your-work-btn').on('click', function(e) {
      e.preventDefault();
      
      // Check for data-type attribute first (set in template)
      var contentType = $(this).data('type');
      if (contentType === 'story_poetry') {
         window.location.href = '/node/add/story_poetry';
         return;
      } else if (contentType === 'junior_artist') {
         window.location.href = '/node/add/junior_artist';
         return;
      }
      
      // Fallback to URL detection or default behavior
      if (window.location.pathname.includes('stories-poetry') || window.location.pathname.includes('inner-thoughts')) {
        window.location.href = '/node/add/story_poetry';
      } else if (window.location.pathname.includes('junior-artists') || window.location.pathname.includes('my-canvas')) {
        window.location.href = '/node/add/junior_artist';
      } else {
        // Default for main page or book reviews section
        window.location.href = '/node/add/book_review';
      }
    });

    // Like Button Handler (Stories & Poetry)
    $(document).on('click', '.like-btn', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var $countSpan = $btn.find('.like-count');
      var $icon = $btn.find('.like-icon');
      var nodeId = $btn.data('id');
      
      // Send AJAX request
      $.ajax({
        url: '/young-writers/api/like/' + nodeId,
        method: 'POST',
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            console.log('Like updated:', response.action);
            $countSpan.text(response.count);
            
            // Toggle visual state based on response
            if (response.action === 'liked') {
              $btn.addClass('liked');
              $icon.attr('src', '/themes/custom/storyfulls/images/heart-icon-red.png');
            } else {
              $btn.removeClass('liked');
              $icon.attr('src', '/themes/custom/storyfulls/images/heart-icon.png');
            }
          }
        },
        error: function(xhr, status, error) {
          console.error('Error updating like:', status, error);
          if (xhr.status === 403) {
            alert('Please login to like content.');
          }
        }
      });
    });

    // Share Button Handler (Stories & Poetry)
    $(document).on('click', '.share-btn', function(e) {
      e.preventDefault();
      var shareUrl = $(this).data('url');
      
      // Use Drupal.dialog if available, otherwise fallback to alert/prompt
      if (Drupal.dialog) {
         var $dialogContent = $('<div></div>').append(
             '<p>Share this story:</p>' +
             '<input type="text" value="' + shareUrl + '" style="width:100%; padding:5px; margin-bottom:10px;" readonly>' +
             '<button class="button copy-link-btn">Copy Link</button>'
         );

         var dialog = Drupal.dialog($dialogContent, {
             title: 'Share Story',
             width: 400,
             buttons: [{
                 text: 'Close',
                 click: function() {
                     $(this).dialog('close');
                 }
             }]
         });
         
         dialog.showModal();
         
         // Attach copy functionality within the dialog context
         // Note: We need to use delegated event or attach after render. 
         // Since dialog content is in DOM now:
         $dialogContent.find('.copy-link-btn').on('click', function() {
             var copyText = $dialogContent.find('input')[0];
             copyText.select();
             copyText.setSelectionRange(0, 99999); /* For mobile devices */
             document.execCommand("copy");
             $(this).text('Copied!');
             setTimeout(() => { $(this).text('Copy Link'); }, 2000);
         });

      } else {
         // Fallback
         prompt("Copy this link to share:", shareUrl);
      }
    });
  });

  // Render Writers grid (Stories & Poetry)
  function renderWriters() {
    var $grid = $('#stories-poetry-grid');
    $grid.empty();
    
    if (filteredWritersData.length === 0) {
      $grid.html('<div class="no-reviewers">No writers found.</div>');
      return;
    }
    
    var start = currentWritersPage * itemsPerPage;
    var end = Math.min(start + itemsPerPage, filteredWritersData.length);
    var pageData = filteredWritersData.slice(start, end);
    
    pageData.forEach(function(user) {
      // Use the specific class for stories user card
      var $card = $('<div class="stories-user-card"></div>');
      $card.css('background-image', 'url(/themes/custom/storyfulls/images/storiesandpoetryusercard.png)');
      
      // Construct the card content based on the design
      // Image cropped to bottom 40% handled by CSS
      // Semicircle overlay handled by CSS/HTML structure
      
      var $link = $('<a href="/young-writers/stories-poetry/user/' + user.id + '" class="stories-card-link"></a>');
      
      var $overlay = $('<div class="stories-card-overlay"></div>');
      var $semicircle = $('<div class="stories-card-semicircle"></div>');
      
      // Ideally we should show the title of their latest story, but we only have user data here
      // Placeholder title
      $semicircle.append('<h3 class="story-title-preview">Stories & Poems</h3>');
      $semicircle.append('<div class="story-author-preview">By<br>' + escapeHtml(user.name) + '</div>');
      
      $overlay.append($semicircle);
      $link.append($overlay);
      $card.append($link);
      
      $grid.append($card);
    });
    
    // Update navigation buttons
    $('#writers-prev').prop('disabled', currentWritersPage === 0);
    $('#writers-next').prop('disabled', end >= filteredWritersData.length);
  }

  // Render Artists grid (Junior Artists)
  function renderArtists() {
    var $grid = $('#junior-artists-grid');
    $grid.empty();
    
    if (filteredArtistsData.length === 0) {
      $grid.html('<div class="no-reviewers">No artists found.</div>');
      return;
    }
    
    var start = currentArtistsPage * itemsPerPage;
    var end = Math.min(start + itemsPerPage, filteredArtistsData.length);
    var pageData = filteredArtistsData.slice(start, end);
    
    pageData.forEach(function(user) {
      var $card = $('<div class="junior-artist-card"></div>');
      $card.css('background-image', 'url(/themes/custom/storyfulls/images/usercanvascard.png)');
      
      var $link = $('<a href="/young-writers/junior-artists/user/' + user.id + '" class="artist-card-link"></a>');
      
      var $overlay = $('<div class="artist-card-overlay"></div>');
      var $canvasText = $('<div class="canvas-text">CANVAS</div>');
      var $semicircle = $('<div class="artist-info-semicircle"></div>');
      
      $semicircle.append('<div class="artist-name">' + escapeHtml(user.name) + '</div>');
      $semicircle.append('<div class="artist-age">' + escapeHtml(user.age) + '</div>');
      
      $overlay.append($canvasText);
      $overlay.append($semicircle);
      $link.append($overlay);
      $card.append($link);
      
      $grid.append($card);
    });
    
    // Update navigation buttons
    $('#artists-prev').prop('disabled', currentArtistsPage === 0);
    $('#artists-next').prop('disabled', end >= filteredArtistsData.length);
  }

  // Render reviewers grid
  function renderReviewers() {
    console.log('Rendering reviewers, filteredData length:', filteredData.length);
    
    var $grid = $('.reviewers-grid');
    $grid.empty();
    
    if (filteredData.length === 0) {
      $grid.html('<div class="no-reviewers">No reviewers found yet. Be the first to share a review!</div>');
      return;
    }
    
    var start = currentPage * itemsPerPage;
    var end = Math.min(start + itemsPerPage, filteredData.length);
    var pageData = filteredData.slice(start, end);
    
    console.log('Rendering', pageData.length, 'reviewer cards');
    
    pageData.forEach(function(user) {
      var $card = $('<div class="reviewer-card"></div>');
      
      var $cardInner = $('<div class="reviewer-card-inner"></div>');
      
      // Avatar with click handler
      var $avatarLink = $('<a href="/young-writers/book-reviews/user/' + user.id + '" class="reviewer-avatar-link"></a>');
      var $avatar = $('<div class="reviewer-avatar"></div>');
      $avatar.css('background-image', 'url(' + user.avatar + ')');
      $avatarLink.append($avatar);
      
      // Badge
      var $badge = $('<div class="reviewer-badge"></div>');
      $badge.css('background-image', 'url(' + user.badge + ')');
      $badge.attr('title', user.badge_name);
      
      // Info section
      var $info = $('<div class="reviewer-info"></div>');
      $info.append('<h3 class="reviewer-name">' + escapeHtml(user.name) + '</h3>');
      $info.append('<p class="reviewer-age">' + escapeHtml(user.age) + '</p>');
      
      $cardInner.append($avatarLink);
      $cardInner.append($badge);
      $cardInner.append($info);
      $card.append($cardInner);
      
      $grid.append($card);
    });
    
    // Update navigation buttons
    $('#reviewers-prev').prop('disabled', currentPage === 0);
    $('#reviewers-next').prop('disabled', end >= filteredData.length);
    
    console.log('Rendering complete');
  }

  // Helper function to escape HTML
  function escapeHtml(text) {
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  // Scroll to grid smoothly
  function scrollToGrid() {
    var $grid = $('#reviewers-grid');
    if ($grid.length) {
      $('html, body').animate({
        scrollTop: $grid.offset().top - 100
      }, 400);
    }
  }

  // Drupal behaviors for compatibility
  Drupal.behaviors.youngWriters = {
    attach: function (context, settings) {
      console.log('Young Writers behavior attached (Drupal)');
      // Main functionality is handled by document.ready above
    }
  };

})(jQuery, Drupal);
