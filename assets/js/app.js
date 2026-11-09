(() => {
  const root = document.documentElement;
  const themeToggle = document.querySelector('[data-theme-toggle]');
  const themePreference = window.matchMedia('(prefers-color-scheme: dark)');

  const setTheme = (theme) => {
    root.dataset.theme = theme;
    if (themeToggle) {
      const dark = theme === 'dark';
      themeToggle.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
      themeToggle.setAttribute('aria-pressed', String(dark));
      themeToggle.innerHTML = dark
        ? '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>'
        : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z"/></svg>';
    }
  };

  let savedTheme = null;
  try {
    savedTheme = window.localStorage.getItem('perpustakaan-theme');
  } catch (error) {
    savedTheme = null;
  }
  setTheme(savedTheme === 'light' || savedTheme === 'dark' ? savedTheme : (themePreference.matches ? 'dark' : 'light'));
  themeToggle?.addEventListener('click', () => {
    const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    setTheme(theme);
    try {
      window.localStorage.setItem('perpustakaan-theme', theme);
    } catch (error) {
      themeToggle.setAttribute('title', 'Pilihan mode tidak dapat disimpan di peramban ini.');
    }
  });

  const shell = document.querySelector('.app-shell');
  const toggle = document.querySelector('[data-menu-toggle]');
  const sidebar = document.querySelector('#app-sidebar');

  const closeMenu = () => {
    if (!shell || !toggle) return;
    shell.classList.remove('menu-open');
    toggle.setAttribute('aria-expanded', 'false');
    if (sidebar && window.matchMedia('(max-width: 768px)').matches) {
      sidebar.inert = true;
      sidebar.setAttribute('aria-hidden', 'true');
    }
  };

  if (shell && toggle && sidebar) {
    const mobile = window.matchMedia('(max-width: 768px)');
    const syncSidebar = () => {
      const hidden = mobile.matches && !shell.classList.contains('menu-open');
      sidebar.inert = hidden;
      sidebar.setAttribute('aria-hidden', String(hidden));
    };
    syncSidebar();
    toggle.addEventListener('click', () => {
      const isOpen = shell.classList.toggle('menu-open');
      toggle.setAttribute('aria-expanded', String(isOpen));
      syncSidebar();
    });
    document.querySelector('[data-menu-close]')?.addEventListener('click', closeMenu);
    sidebar.addEventListener('click', (event) => {
      if (event.target instanceof HTMLAnchorElement) closeMenu();
    });
    mobile.addEventListener('change', syncSidebar);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
  }

  document.querySelectorAll('[data-count]').forEach((element) => {
    const target = Number(element.dataset.count);
    if (!Number.isFinite(target)) return;
    const start = performance.now();
    const duration = 600;
    const update = (now) => {
      const progress = Math.min(1, (now - start) / duration);
      element.textContent = Math.round(target * progress).toLocaleString('id-ID');
      if (progress < 1) window.requestAnimationFrame(update);
    };
    window.requestAnimationFrame(update);
  });

  document.querySelectorAll('[data-bar-width]').forEach((bar) => {
    const width = Number(bar.dataset.barWidth);
    if (Number.isFinite(width)) bar.style.width = `${Math.max(0, Math.min(100, width))}%`;
  });
  document.querySelectorAll('[data-quota]').forEach((bar) => {
    const width = Number(bar.dataset.quota);
    if (Number.isFinite(width)) bar.style.width = `${Math.max(0, Math.min(100, width))}%`;
  });

  document.querySelectorAll('[data-view-select]').forEach((button) => {
    button.addEventListener('click', () => {
      const selectedView = button.dataset.viewSelect;
      document.querySelectorAll('[data-view-select]').forEach((choice) => {
        const selected = choice === button;
        choice.classList.toggle('selected', selected);
        choice.setAttribute('aria-pressed', String(selected));
      });
      document.querySelectorAll('[data-collection-view]').forEach((view) => {
        view.hidden = view.dataset.collectionView !== selectedView;
      });
    });
  });

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.parentElement?.querySelector('input');
      if (!(input instanceof HTMLInputElement)) return;
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      button.textContent = visible ? 'Sembunyikan' : 'Tampilkan';
      button.setAttribute('aria-pressed', String(visible));
      button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  const loginForm = document.querySelector('[data-login-form]');
  if (loginForm) {
    const password = loginForm.querySelector('#password');
    const capsLockWarning = loginForm.querySelector('[data-caps-lock-warning]');
    if (password instanceof HTMLInputElement && capsLockWarning instanceof HTMLElement) {
      const updateCapsLockWarning = (event) => {
        capsLockWarning.hidden = !event.getModifierState('CapsLock');
      };
      password.addEventListener('keydown', updateCapsLockWarning);
      password.addEventListener('keyup', updateCapsLockWarning);
      password.addEventListener('blur', () => {
        capsLockWarning.hidden = true;
      });
    }

    if (loginForm.dataset.loginError === 'true' && password instanceof HTMLInputElement) {
      password.focus();
    }
  }

  const loanForm = document.querySelector('[data-loan-form]');
  if (loanForm) {
    const member = loanForm.querySelector('[name="id_anggota"]');
    const book = loanForm.querySelector('[name="id_buku"]');
    const date = loanForm.querySelector('[name="tgl_pinjam"]');
    const duration = loanForm.querySelector('[name="lama"]');
    const summary = {
      member: document.querySelector('[data-summary-member]'),
      book: document.querySelector('[data-summary-book]'),
      stock: document.querySelector('[data-summary-stock]'),
      due: document.querySelector('[data-summary-due]'),
    };
    const updateSummary = () => {
      if (member instanceof HTMLSelectElement && summary.member) {
        summary.member.textContent = member.value ? member.selectedOptions[0].textContent : 'Belum dipilih';
      }
      if (book instanceof HTMLSelectElement && summary.book && summary.stock) {
        const option = book.selectedOptions[0];
        const match = option?.textContent?.match(/\(stok (\d+)\)$/);
        summary.book.textContent = book.value ? option.textContent.replace(/\s*\(stok \d+\)$/, '') : 'Belum dipilih';
        summary.stock.textContent = match ? `${match[1]} eksemplar` : '—';
      }
      if (date instanceof HTMLInputElement && duration instanceof HTMLSelectElement && summary.due) {
        const dueDate = new Date(`${date.value}T12:00:00`);
        dueDate.setDate(dueDate.getDate() + Number(duration.value || 0));
        summary.due.textContent = date.value && Number.isFinite(dueDate.getTime())
          ? new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(dueDate)
          : '—';
      }
    };
    [member, book, date, duration].forEach((field) => field?.addEventListener('change', updateSummary));
    updateSummary();
  }

  const dialog = document.querySelector('[data-confirm-dialog]');
  const confirmMessage = dialog?.querySelector('[data-confirm-message]');
  const confirmTitle = dialog?.querySelector('#confirm-title');
  const confirmAction = dialog?.querySelector('[data-confirm-accept]');
  let pendingForm = null;
  let pendingSubmitter = null;
  let confirmedForm = null;

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (confirmedForm === form) {
        confirmedForm = null;
        disableSubmit(form, event.submitter);
        return;
      }
      event.preventDefault();
      if (!dialog || !confirmMessage || !confirmTitle || !confirmAction) return;
      pendingForm = form;
      pendingSubmitter = event.submitter;
      confirmTitle.textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
      confirmMessage.textContent = form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan?';
      confirmAction.textContent = form.dataset.confirmAction || 'Lanjutkan';
      dialog.showModal();
    });
  });

  dialog?.querySelector('[data-confirm-cancel]')?.addEventListener('click', () => {
    pendingForm = null;
    pendingSubmitter = null;
    dialog.close();
  });

  confirmAction?.addEventListener('click', () => {
    if (!pendingForm) return;
    const form = pendingForm;
    const submitter = pendingSubmitter;
    pendingForm = null;
    pendingSubmitter = null;
    confirmedForm = form;
    dialog.close();
    form.requestSubmit(submitter || undefined);
  });

  dialog?.addEventListener('cancel', () => {
    pendingForm = null;
    pendingSubmitter = null;
  });

  document.querySelectorAll('form:not([data-confirm])').forEach((form) => {
    form.addEventListener('submit', (event) => disableSubmit(form, event.submitter));
  });

  function disableSubmit(form, submitter) {
    const button = submitter instanceof HTMLButtonElement
      ? submitter
      : form.querySelector('button[type="submit"], button:not([type])');
    if (!(button instanceof HTMLButtonElement)) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    if (button.hasAttribute('data-login-submit')) {
      const label = button.querySelector('[data-login-submit-label]');
      const spinner = button.querySelector('[data-login-spinner]');
      const arrow = button.querySelector('[data-login-arrow]');
      if (label) label.textContent = 'Memproses...';
      if (spinner instanceof HTMLElement) spinner.hidden = false;
      if (arrow instanceof SVGElement) arrow.hidden = true;
      return;
    }
    button.dataset.originalText = button.textContent || '';
    button.textContent = 'Menyimpan...';
  }

  document.querySelectorAll('.alert.ok').forEach((alert) => {
    window.setTimeout(() => {
      alert.classList.add('is-dismissing');
      window.setTimeout(() => alert.remove(), 180);
    }, 5000);
  });

  document.querySelector('[data-print]')?.addEventListener('click', () => window.print());
})();
