/**
 * Edit Profile Page JavaScript
 */

(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.editProfile = {
    attach: function (context, settings) {
      
      // Avatar selection handling
      once('avatar-selection', '.avatar-option', context).forEach(function(option) {
        option.addEventListener('click', function() {
          // Remove selected class from all options
          document.querySelectorAll('.avatar-option').forEach(function(opt) {
            opt.classList.remove('selected');
          });
          
          // Add selected class to clicked option
          this.classList.add('selected');
          
          // Check the radio button
          const radio = this.querySelector('input[type="radio"]');
          if (radio) {
            radio.checked = true;
          }
        });
      });
      
      // Profile picture preview (legacy - keeping for backward compatibility)
      once('profile-picture-upload', '#profilePictureInput', context).forEach(function(input) {
        input.addEventListener('change', function(e) {
          const file = e.target.files[0];
          
          if (file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
              alert('Please upload a valid image file (JPG, PNG, or GIF)');
              input.value = '';
              return;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
              alert('File size must be less than 5MB');
              input.value = '';
              return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(event) {
              const display = document.getElementById('profilePictureDisplay');
              
              // Remove existing content
              display.innerHTML = '';
              
              // Create new image
              const img = document.createElement('img');
              img.src = event.target.result;
              img.alt = 'Profile Picture Preview';
              img.className = 'current-profile-pic';
              
              display.appendChild(img);
            };
            
            reader.readAsDataURL(file);
          }
        });
      });
      
      // Auto-calculate age from date of birth
      once('date-of-birth-calc', '#dateOfBirth', context).forEach(function(dateInput) {
        dateInput.addEventListener('change', function(e) {
          const dob = new Date(e.target.value);
          const today = new Date();
          let age = today.getFullYear() - dob.getFullYear();
          const monthDiff = today.getMonth() - dob.getMonth();
          
          if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
          }
          
          if (age > 0 && age < 120) {
            document.getElementById('age').value = age;
          }
        });
      });
      
      // Form submission with loading state
      once('edit-profile-form', '#editProfileForm', context).forEach(function(form) {
        form.addEventListener('submit', function(e) {
          const submitBtn = form.querySelector('.btn-save');
          
          // Validate avatar selection
          const avatarSelected = form.querySelector('input[name="avatar"]:checked');
          if (!avatarSelected) {
            e.preventDefault();
            alert('Please select an avatar');
            return false;
          }
          
          // Add loading state
          form.classList.add('form-loading');
          submitBtn.disabled = true;
          
          // Validate required fields
          const firstName = document.getElementById('firstName').value.trim();
          const lastName = document.getElementById('lastName').value.trim();
          
          if (!firstName || !lastName) {
            e.preventDefault();
            alert('Please fill in all required fields');
            form.classList.remove('form-loading');
            submitBtn.disabled = false;
            return false;
          }
        });
      });
      
      // Character count for textareas
      once('textarea-counter', '.form-textarea', context).forEach(function(textarea) {
        const maxLength = textarea.getAttribute('maxlength');
        
        if (maxLength) {
          const counter = document.createElement('div');
          counter.className = 'char-counter';
          counter.style.cssText = 'text-align: right; font-size: 12px; color: #666; margin-top: 0.25rem;';
          
          const updateCounter = function() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = remaining + ' characters remaining';
            
            if (remaining < 20) {
              counter.style.color = '#E74C3C';
            } else {
              counter.style.color = '#666';
            }
          };
          
          textarea.parentNode.insertBefore(counter, textarea.nextSibling);
          textarea.addEventListener('input', updateCounter);
          updateCounter();
        }
      });
      
      // Smooth scroll to form errors
      const messages = document.querySelector('.messages--error');
      if (messages) {
        messages.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      
    }
  };

})(jQuery, Drupal, once);
