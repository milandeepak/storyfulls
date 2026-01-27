/**
 * @file
 * JavaScript for write review page - handles star rating interaction.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.writeReviewStarRating = {
    attach: function (context, settings) {
      const starRatingContainer = once('star-rating', '#starRating', context);
      
      if (starRatingContainer.length > 0) {
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('ratingInput');
        const ratingError = document.getElementById('ratingError');
        const form = document.getElementById('bookReviewForm');
        
        let selectedRating = 0;

        // Add click event to each star
        stars.forEach((star, index) => {
          star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-value'));
            ratingInput.value = selectedRating;
            updateStars(selectedRating);
            
            // Hide error if rating is selected
            if (ratingError) {
              ratingError.style.display = 'none';
            }
          });

          // Add hover effect
          star.addEventListener('mouseenter', function() {
            const hoverValue = parseInt(this.getAttribute('data-value'));
            updateStars(hoverValue, true);
          });
        });

        // Reset to selected rating on mouse leave
        const starsContainer = document.querySelector('.stars');
        if (starsContainer) {
          starsContainer.addEventListener('mouseleave', function() {
            updateStars(selectedRating);
          });
        }

        // Form validation
        if (form) {
          form.addEventListener('submit', function(e) {
            const reviewText = document.getElementById('reviewText').value.trim();
            let isValid = true;

            // Validate rating
            if (selectedRating === 0) {
              e.preventDefault();
              if (ratingError) {
                ratingError.style.display = 'block';
              }
              isValid = false;
            }

            // Validate review text
            if (reviewText === '') {
              e.preventDefault();
              alert('Please write your review.');
              isValid = false;
            }

            if (!isValid) {
              return false;
            }
          });
        }

        /**
         * Update star display based on rating value
         */
        function updateStars(rating, isHover = false) {
          stars.forEach((star, index) => {
            const starValue = parseInt(star.getAttribute('data-value'));
            
            // Remove all classes first
            star.classList.remove('active', 'hover');
            
            if (starValue <= rating) {
              if (isHover) {
                star.classList.add('hover');
              } else {
                star.classList.add('active');
              }
            }
          });
        }
      }
    }
  };

})(Drupal, once);
