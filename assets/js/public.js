(() => {
  const header = document.querySelector('[data-public-header]');
  const progressBar = document.querySelector('[data-reading-progress]');
  const menuButton = document.querySelector('[data-public-menu]');
  const menuPanel = document.querySelector('[data-public-menu-panel]');
  const hero = document.querySelector('.public-hero');
  const sections = Array.from(document.querySelectorAll('main section[id]'));
  const sectionLinks = document.querySelectorAll('[data-nav-section]');
  const smallViewport = window.matchMedia('(max-width: 899px)');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let scrollScheduled = false;

  const updateScrollState = () => {
    scrollScheduled = false;
    header?.classList.toggle('is-scrolled', window.scrollY > 8);
    if (progressBar) {
      const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = scrollableHeight > 0 ? window.scrollY / scrollableHeight : 0;
      progressBar.style.setProperty('--scroll-progress', String(Math.min(1, Math.max(0, progress))));
    }

    if (hero && !reducedMotion) {
      const bounds = hero.getBoundingClientRect();
      const progress = Math.min(1, Math.max(0, (window.innerHeight - bounds.top) / (window.innerHeight + bounds.height)));
      const distance = smallViewport.matches ? 16 : 32;
      hero.style.setProperty('--hero-parallax', `${(progress - 0.5) * distance}px`);
    }

    if (sections.length && sectionLinks.length) {
      const marker = window.innerHeight * 0.38;
      let activeId = sections[0].id;
      sections.forEach((section) => {
        if (section.getBoundingClientRect().top <= marker) activeId = section.id;
      });
      sectionLinks.forEach((link) => {
        const active = link.dataset.navSection === activeId;
        link.classList.toggle('is-active', active);
        if (active) link.setAttribute('aria-current', 'location');
        else link.removeAttribute('aria-current');
      });
    }
  };

  const scheduleScrollUpdate = () => {
    if (scrollScheduled) return;
    scrollScheduled = true;
    window.requestAnimationFrame(updateScrollState);
  };
  updateScrollState();
  window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
  window.addEventListener('resize', scheduleScrollUpdate);

  if (menuButton && menuPanel) {
    const menuItems = () => Array.from(menuPanel.querySelectorAll('a, button'))
      .filter((item) => item.getClientRects().length > 0 && !item.hasAttribute('disabled'));
    const closeMenu = (restoreFocus = false) => {
      menuPanel.classList.remove('is-open');
      menuPanel.setAttribute('aria-hidden', 'true');
      menuPanel.setAttribute('inert', '');
      menuButton.setAttribute('aria-expanded', 'false');
      menuButton.setAttribute('aria-label', 'Buka menu');
      document.body.classList.remove('public-menu-open');
      if (restoreFocus) menuButton.focus();
    };

    menuButton.addEventListener('click', () => {
      const isOpen = !menuPanel.classList.contains('is-open');
      if (!isOpen) {
        closeMenu(true);
        return;
      }
      menuPanel.classList.toggle('is-open', isOpen);
      menuPanel.setAttribute('aria-hidden', 'false');
      menuPanel.removeAttribute('inert');
      menuButton.setAttribute('aria-expanded', 'true');
      menuButton.setAttribute('aria-label', 'Tutup menu');
      document.body.classList.toggle('public-menu-open', smallViewport.matches);
      menuItems()[0]?.focus();
    });
    menuPanel.addEventListener('click', (event) => {
      if (event.target instanceof Element && event.target.closest('a, button')) closeMenu();
    });
    document.addEventListener('keydown', (event) => {
      if (!menuPanel.classList.contains('is-open')) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        closeMenu(true);
        return;
      }
      const items = menuItems();
      if (!items.length) return;
      const currentIndex = items.indexOf(document.activeElement);
      if (event.key === 'Tab' && smallViewport.matches) {
        if (event.shiftKey && currentIndex <= 0) {
          event.preventDefault();
          items[items.length - 1].focus();
        } else if (!event.shiftKey && currentIndex === items.length - 1) {
          event.preventDefault();
          items[0].focus();
        }
        return;
      }
      if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp' && event.key !== 'Home' && event.key !== 'End') return;
      let nextIndex = currentIndex;
      if (event.key === 'Home') nextIndex = 0;
      else if (event.key === 'End') nextIndex = items.length - 1;
      else if (event.key === 'ArrowDown') nextIndex = (currentIndex + 1) % items.length;
      else nextIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
      event.preventDefault();
      items[nextIndex].focus();
    });
    document.addEventListener('click', (event) => {
      if (!menuPanel.classList.contains('is-open')) return;
      if (event.target instanceof Node && !menuPanel.contains(event.target) && !menuButton.contains(event.target)) {
        const target = event.target instanceof Element ? event.target : null;
        const clickedFocusable = target?.closest('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        closeMenu(!clickedFocusable);
      }
    });
    smallViewport.addEventListener('change', () => closeMenu());
  }

  document.querySelectorAll('[data-public-count]').forEach((element) => {
    const target = Number(element.dataset.publicCount);
    if (!Number.isFinite(target)) return;
    if (reducedMotion) {
      element.textContent = Math.round(target).toLocaleString('id-ID');
      return;
    }

    const start = performance.now();
    const update = (now) => {
      const progress = Math.min(1, (now - start) / 600);
      element.textContent = Math.round(target * progress).toLocaleString('id-ID');
      if (progress < 1) window.requestAnimationFrame(update);
    };
    window.requestAnimationFrame(update);
  });

  const revealItems = document.querySelectorAll([
    '.public-reveal',
    '.public-catalog-intro .public-catalog-inner > *',
    '.public-catalog-filter',
    '.public-catalog-count',
    '.public-book-card',
    '.public-catalog-empty',
    '.public-catalog-page .pager',
    '.public-footer-inner > *'
  ].join(', '));
  if (!reducedMotion && 'IntersectionObserver' in window && revealItems.length) {
    document.documentElement.classList.add('public-js');
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        entry.target.classList.toggle('is-visible', entry.isIntersecting);
      });
    }, { threshold: 0.12 });
    revealItems.forEach((item) => {
      item.classList.add('public-reveal');
      revealObserver.observe(item);
    });
  }

})();
