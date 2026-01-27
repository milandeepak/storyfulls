(function ($, Drupal) {
  'use strict';

  // Global state variables
  var reviewersData = [];
  var filteredData = [];
  var currentPage = 0;
  var itemsPerPage = 12;
  var dataLoaded = false;

  // Document ready handler - primary implementation
  $(document).ready(function() {
    console.log('Young Writers: Document ready');
    
    var $cardLink = $('.card-link[data-reveal-target="book-reviews-reveal"]');
    console.log('Found card links:', $cardLink.length);
    
    // Book Reviews Card Click Handler
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
      
      // Show loading state
      console.log('Showing reveal section');
      $revealSection.fadeIn(300);
      
      // Only load data once
      if (!dataLoaded) {
        $('.reviewers-grid').html('<div class="loading-spinner">Loading reviewers...</div>');
        
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
            console.error('Response:', xhr.responseText);
            $('.reviewers-grid').html('<div class="error-message">Unable to load reviewers. Please try again later.</div>');
          }
        });
      }
    });

    // Search functionality
    $('#reviewer-search').on('input', function() {
      var searchTerm = $(this).val().toLowerCase();
      console.log('Search term:', searchTerm);
      
      if (searchTerm === '') {
        // Reset to full list
        filteredData = reviewersData;
        currentPage = 0;
        renderReviewers();
        return;
      }
      
      // Filter reviewers
      filteredData = reviewersData.filter(function(user) {
        return user.name.toLowerCase().indexOf(searchTerm) !== -1;
      });
      
      console.log('Filtered results:', filteredData.length);
      currentPage = 0;
      renderReviewers();
    });

    // Navigation buttons
    $('#reviewers-prev').on('click', function() {
      if (currentPage > 0) {
        currentPage--;
        renderReviewers();
        scrollToGrid();
      }
    });

    $('#reviewers-next').on('click', function() {
      var maxPage = Math.ceil(filteredData.length / itemsPerPage) - 1;
      if (currentPage < maxPage) {
        currentPage++;
        renderReviewers();
        scrollToGrid();
      }
    });

    // Share your work button
    $('.share-your-work-btn').on('click', function() {
      window.location.href = '/node/add/book_review';
    });

    // Like Button Handler (Stories & Poetry)
    $(document).on('click', '.like-btn', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var $countSpan = $btn.find('span');
      var currentCount = parseInt($countSpan.text(), 10);
      
      if ($btn.hasClass('liked')) {
        $btn.removeClass('liked');
        $countSpan.text(currentCount - 1);
      } else {
        $btn.addClass('liked');
        $countSpan.text(currentCount + 1);
        // Here you would typically send an AJAX request to the server
        console.log('Post liked!');
      }
    });

    // Share Button Handler (Stories & Poetry)
    $(document).on('click', '.share-btn', function(e) {
      e.preventDefault();
      // Simple clipboard copy or share modal would go here
      alert('Share functionality coming soon!');
    });
  });

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
