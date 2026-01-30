/**
 * Fact Section Interactive Functionality
 */
(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.factSection = {
    attach: function (context, settings) {
      $('.fact-section', context).once('fact-section').each(function () {
        const $section = $(this);
        const correctAnswerIndex = parseInt($section.data('correct-answer'), 10);
        const $options = $section.find('.fact-option');
        const $submitBtn = $section.find('.fact-submit-btn');
        const $result = $section.find('.fact-result');
        const $resultMessage = $section.find('.fact-result-message');
        
        let selectedIndex = null;
        let hasSubmitted = false;

        // Handle option click
        $options.on('click', function () {
          if (hasSubmitted) return; // Don't allow changing after submission
          
          // Remove selected class from all options
          $options.removeClass('selected');
          
          // Add selected class to clicked option
          $(this).addClass('selected');
          
          // Store selected index
          selectedIndex = parseInt($(this).data('option-index'), 10);
        });

        // Handle submit button click
        $submitBtn.on('click', function () {
          if (selectedIndex === null) {
            alert('Please select an option first!');
            return;
          }

          if (hasSubmitted) return; // Prevent multiple submissions
          
          hasSubmitted = true;
          $submitBtn.prop('disabled', true);

          // Check if answer is correct
          const isCorrect = selectedIndex === correctAnswerIndex;

          // Show visual feedback on options
          $options.each(function () {
            const optionIndex = parseInt($(this).data('option-index'), 10);
            
            if (optionIndex === correctAnswerIndex) {
              $(this).addClass('correct');
            } else if (optionIndex === selectedIndex && !isCorrect) {
              $(this).addClass('incorrect');
            }
          });

          // Show result message
          if (isCorrect) {
            $resultMessage.text('Correct! Well done!').addClass('correct').removeClass('incorrect');
          } else {
            $resultMessage.text('Oops! Try again next time.').addClass('incorrect').removeClass('correct');
          }
          
          $result.fadeIn();
        });
      });
    }
  };

})(jQuery, Drupal);
