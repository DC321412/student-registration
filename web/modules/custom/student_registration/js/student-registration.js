/**
 * @file
 * JavaScript behaviors for Student Registration module.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.studentRegistration = {
    attach: function (context, settings) {
      // Image live preview handler.
      const fileInputs = once('student-photo-preview', '.student-photo-upload input[type="file"]', context);
      fileInputs.forEach(function (input) {
        input.addEventListener('change', function (e) {
          const file = e.target.files && e.target.files[0];
          const previewContainer = document.getElementById('student-photo-preview');
          if (file && previewContainer) {
            // Verify file is an image.
            if (!file.type.startsWith('image/')) {
              previewContainer.innerHTML = '<span class="preview-placeholder text-danger" style="color: #dc2626;">Please select a valid image file (PNG, JPG, WEBP).</span>';
              return;
            }

            // Check file size limit (2MB).
            if (file.size > 2 * 1024 * 1024) {
              previewContainer.innerHTML = '<span class="preview-placeholder text-danger" style="color: #dc2626;">File is too large! Maximum allowed is 2 MB.</span>';
              return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
              previewContainer.innerHTML = '<img src="' + event.target.result + '" alt="Photo preview" />';
            };
            reader.readAsDataURL(file);
          }
        });
      });

      // Phone number formatting helper.
      const phoneInputs = once('student-phone-format', 'input[type="tel"]', context);
      phoneInputs.forEach(function (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
          let value = e.target.value.replace(/\D/g, '');
          if (value.length > 10) {
            value = value.substring(0, 15);
          }
          if (value.length === 10) {
            e.target.value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
          }
        });
      });

      // Quick visual validation for email.
      const emailInputs = once('student-email-feedback', 'input[type="email"]', context);
      emailInputs.forEach(function (emailInput) {
        emailInput.addEventListener('blur', function (e) {
          const val = e.target.value.trim();
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (val.length > 0 && emailRegex.test(val)) {
            e.target.style.borderColor = '#16a34a';
          }
          else if (val.length > 0) {
            e.target.style.borderColor = '#dc2626';
          }
          else {
            e.target.style.borderColor = '';
          }
        });
      });
    }
  };
})(Drupal, once);

