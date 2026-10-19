/**
 * @file
 * Interactive mouse-responsive gradient mesh, spotlight, and 3D card tilt behaviors.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.nexusInteractiveMesh = {
    attach: function (context) {
      const elements = once('nexus-mesh-init', 'body', context);
      if (!elements.length) {
        return;
      }

      // Check for reduced motion preference.
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (prefersReducedMotion) {
        return;
      }

      const spotlight = document.querySelector('.cursor-spotlight');
      const blobCyan = document.querySelector('.blob-cyan');
      const blobViolet = document.querySelector('.blob-violet');
      const blobIndigo = document.querySelector('.blob-indigo');
      const blobEmerald = document.querySelector('.blob-emerald');

      // Mouse position state with linear interpolation (lerp)
      let mouseX = window.innerWidth / 2;
      let mouseY = window.innerHeight / 2;
      let currentX = mouseX;
      let currentY = mouseY;

      // Track mouse movement smoothly
      window.addEventListener('pointermove', function (e) {
        mouseX = e.clientX;
        mouseY = e.clientY;

        // Update spotlight position directly for instantaneous responsiveness
        if (spotlight) {
          spotlight.style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0)`;
        }
      }, { passive: true });

      // Interactive subtle parallax drift for gradient blobs
      let isVisible = true;
      document.addEventListener('visibilitychange', function () {
        isVisible = !document.hidden;
      });

      function renderFrame() {
        if (isVisible) {
          // Lerp calculation for buttery smooth organic motion
          currentX += (mouseX - currentX) * 0.05;
          currentY += (mouseY - currentY) * 0.05;

          const normX = (currentX / window.innerWidth) - 0.5; // -0.5 to 0.5
          const normY = (currentY / window.innerHeight) - 0.5;

          // Parallax factors for individual blobs
          if (blobCyan) {
            blobCyan.style.transform = `translate3d(${normX * 60}px, ${normY * 60}px, 0)`;
          }
          if (blobViolet) {
            blobViolet.style.transform = `translate3d(${-normX * 70}px, ${-normY * 70}px, 0)`;
          }
          if (blobIndigo) {
            blobIndigo.style.transform = `translate3d(${normX * 45}px, ${-normY * 50}px, 0)`;
          }
          if (blobEmerald) {
            blobEmerald.style.transform = `translate3d(${-normX * 55}px, ${normY * 45}px, 0)`;
          }
        }

        requestAnimationFrame(renderFrame);
      }

      requestAnimationFrame(renderFrame);

      // Card Mouse Spotlight & 3D Tilt on Hover
      const cards = document.querySelectorAll(
        '.nexus-card, .feature-card, .stat-card, .recent-student-card, .dept-card, .interactive-card, .detail-card'
      );

      cards.forEach(card => {
        card.addEventListener('pointermove', function (e) {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;

          // Set custom CSS properties on individual card for spotlight border
          card.style.setProperty('--mouse-x', `${x}px`);
          card.style.setProperty('--mouse-y', `${y}px`);

          // Calculate 3D tilt angles (subtle & professional: max 6 degrees)
          const centerX = rect.width / 2;
          const centerY = rect.height / 2;
          const rotateX = ((y - centerY) / centerY) * -5;
          const rotateY = ((x - centerX) / centerX) * 5;

          card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;
        }, { passive: true });

        card.addEventListener('pointerleave', function () {
          card.style.transform = '';
          card.style.removeProperty('--mouse-x');
          card.style.removeProperty('--mouse-y');
        });
      });
    }
  };

})(Drupal, once);

