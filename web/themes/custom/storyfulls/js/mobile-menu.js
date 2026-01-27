/**
 * Mobile Menu Toggle
 */
(function () {
  'use strict';

  // Wait for DOM to be ready
  function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const body = document.body;

    if (!menuToggle || !mainNav) {
      console.log('Mobile menu elements not found');
      return;
    }

    console.log('Mobile menu initialized');

    // Toggle menu when clicking hamburger button
    menuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const isActive = this.classList.toggle('active');
      mainNav.classList.toggle('active');
      body.classList.toggle('menu-open');
      
      console.log('Menu toggled:', isActive ? 'open' : 'closed');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.main-nav') && !e.target.closest('.mobile-menu-toggle')) {
        menuToggle.classList.remove('active');
        mainNav.classList.remove('active');
        body.classList.remove('menu-open');
      }
    });

    // Close menu when clicking a menu link
    const menuLinks = mainNav.querySelectorAll('.main-navigation a');
    menuLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        menuToggle.classList.remove('active');
        mainNav.classList.remove('active');
        body.classList.remove('menu-open');
      });
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileMenu);
  } else {
    initMobileMenu();
  }

  // Also initialize on Drupal behaviors (for AJAX compatibility)
  if (typeof Drupal !== 'undefined') {
    Drupal.behaviors.mobileMenu = {
      attach: function (context, settings) {
        if (context === document) {
          initMobileMenu();
        }
      }
    };
  }

})();
