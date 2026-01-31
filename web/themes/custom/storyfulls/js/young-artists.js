/**
 * @file
 * Young Artists 3D Coverflow Carousel - Vanilla JavaScript
 * 
 * Handles carousel navigation and 3D positioning for artwork galleries.
 */

(function () {
  'use strict';

  // Carousel class to handle 3D coverflow behavior
  class CoverflowCarousel {
    constructor(containerId) {
      this.container = document.getElementById(containerId);
      if (!this.container) {
        console.warn(`Carousel container "${containerId}" not found.`);
        return;
      }

      this.wrapper = this.container.querySelector('.coverflow-wrapper');
      this.cards = Array.from(this.wrapper.querySelectorAll('.coverflow-card'));
      this.currentIndex = 0;
      this.totalCards = this.cards.length;

      // Initialize carousel
      this.init();
    }

    init() {
      if (this.totalCards === 0) {
        console.warn('No cards found in carousel.');
        return;
      }

      // Set initial positions
      this.updatePositions();

      // Add keyboard navigation
      this.addKeyboardNavigation();

      // Add touch/swipe support for mobile
      this.addSwipeSupport();
    }

    /**
     * Update card positions based on current index
     */
    updatePositions() {
      this.cards.forEach((card, index) => {
        // Calculate position relative to current center card
        const position = index - this.currentIndex;
        
        // Set data attribute for CSS positioning
        card.setAttribute('data-position', position);
        
        // Add click handler to navigate to clicked card
        card.onclick = () => {
          if (position !== 0) {
            this.goToSlide(index);
          }
        };
      });
    }

    /**
     * Navigate to next card
     */
    next() {
      this.currentIndex = (this.currentIndex + 1) % this.totalCards;
      this.updatePositions();
    }

    /**
     * Navigate to previous card
     */
    prev() {
      this.currentIndex = (this.currentIndex - 1 + this.totalCards) % this.totalCards;
      this.updatePositions();
    }

    /**
     * Go to specific slide
     */
    goToSlide(index) {
      if (index >= 0 && index < this.totalCards) {
        this.currentIndex = index;
        this.updatePositions();
      }
    }

    /**
     * Add keyboard navigation support
     */
    addKeyboardNavigation() {
      document.addEventListener('keydown', (e) => {
        // Only respond if carousel is in viewport
        if (!this.isInViewport()) return;

        if (e.key === 'ArrowLeft') {
          e.preventDefault();
          this.prev();
        } else if (e.key === 'ArrowRight') {
          e.preventDefault();
          this.next();
        }
      });
    }

    /**
     * Add swipe support for touch devices
     */
    addSwipeSupport() {
      let touchStartX = 0;
      let touchEndX = 0;
      const minSwipeDistance = 50; // Minimum distance for swipe

      this.container.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      this.container.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        this.handleSwipe(touchStartX, touchEndX, minSwipeDistance);
      }, { passive: true });
    }

    /**
     * Handle swipe gesture
     */
    handleSwipe(startX, endX, minDistance) {
      const diff = startX - endX;
      
      if (Math.abs(diff) > minDistance) {
        if (diff > 0) {
          // Swiped left - go to next
          this.next();
        } else {
          // Swiped right - go to previous
          this.prev();
        }
      }
    }

    /**
     * Check if carousel is in viewport (for keyboard nav)
     */
    isInViewport() {
      const rect = this.container.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    }
  }

  /**
   * Initialize carousels when DOM is ready
   */
  function initCarousels() {
    // Initialize Illustrations carousel
    const illustrationsCarousel = new CoverflowCarousel('illustrations-carousel');
    
    // Initialize Art & Craft carousel
    const artCraftCarousel = new CoverflowCarousel('artcraft-carousel');

    // Attach navigation button handlers
    setupNavigationButtons(illustrationsCarousel, 'illustrations-carousel');
    setupNavigationButtons(artCraftCarousel, 'artcraft-carousel');
  }

  /**
   * Setup navigation button event handlers
   */
  function setupNavigationButtons(carouselInstance, carouselId) {
    const prevBtn = document.querySelector(`[data-carousel="${carouselId}"].carousel-prev`);
    const nextBtn = document.querySelector(`[data-carousel="${carouselId}"].carousel-next`);

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        carouselInstance.prev();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        carouselInstance.next();
      });
    }
  }

  /**
   * Auto-play functionality (optional)
   * Uncomment to enable auto-rotation
   */
  /*
  function enableAutoPlay(carouselInstance, intervalMs = 5000) {
    setInterval(() => {
      carouselInstance.next();
    }, intervalMs);
  }
  */

  // Initialize when DOM is fully loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousels);
  } else {
    // DOM already loaded
    initCarousels();
  }

})();
