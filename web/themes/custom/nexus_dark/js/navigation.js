/**
 * @file
 * Navigation behaviors, mobile menu drawer, and scroll effects.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.nexusNavigation = {
    attach: function (context) {
      const header = once('nexus-nav-init', '.nexus-header', context)[0];
      if (!header) {
        return;
      }

      // Scroll event for sticky glass header
      const handleScroll = function () {
        if (window.scrollY > 20) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      };

      window.addEventListener('scroll', handleScroll, { passive: true });
      handleScroll();

      // Mobile Drawer Toggle
      const mobileToggle = document.querySelector('.nexus-mobile-toggle');
      const mobileDrawer = document.querySelector('.nexus-mobile-drawer');

      if (mobileToggle && mobileDrawer) {
        mobileToggle.addEventListener('click', function () {
          const isOpen = mobileToggle.classList.toggle('is-open');
          mobileDrawer.classList.toggle('is-open', isOpen);
          mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close drawer on link click
        mobileDrawer.querySelectorAll('a').forEach(function (link) {
          link.addEventListener('click', function () {
            mobileToggle.classList.remove('is-open');
            mobileDrawer.classList.remove('is-open');
            mobileToggle.setAttribute('aria-expanded', 'false');
          });
        });
      }

      // Auto-highlight active nav link based on window.location.pathname
      const currentPath = window.location.pathname;
      const allNavLinks = document.querySelectorAll('.nexus-nav-link, .mobile-nav-link');
      allNavLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && (href === currentPath || (href !== '/' && currentPath.startsWith(href)))) {
          link.classList.add('is-active');
        }
      });
    }
  };

})(Drupal, once);

