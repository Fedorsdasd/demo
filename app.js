папка js ( общая папка uchis-rf )

/* ============================================================
   Портал "Учусь.РФ" — JavaScript
   ============================================================ */

// ── Слайдер ──────────────────────────────────────────────────
function initSlider(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const track = container.querySelector('.slider-track');
  const slides = container.querySelectorAll('.slide');
  const dots   = container.querySelectorAll('.slider-dot');
  let current  = 0;
  let timer;

  function goTo(idx) {
    current = (idx + slides.length) % slides.length;
    track.style.transform = `translateX(-${current * 100}%)`;
    dots.forEach((d, i) => d.classList.toggle('active', i === current));
  }

  function next() { goTo(current + 1); }
  function prev() { goTo(current - 1); }

  function startAuto() {
    clearInterval(timer);
    timer = setInterval(next, 3000);
  }

  container.querySelector('.next')?.addEventListener('click', () => { next(); startAuto(); });
  container.querySelector('.prev')?.addEventListener('click', () => { prev(); startAuto(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); startAuto(); }));

  goTo(0);
  startAuto();
}

// ── Валидация формы регистрации ───────────────────────────────
function initRegisterValidation() {
  const form = document.getElementById('registerForm');
  if (!form) return;

  const rules = {
    login: {
      pattern: /^[a-zA-Z0-9]{6,}$/,
      msg: 'Минимум 6 символов, только латинские буквы и цифры'
    },
    password: {
      pattern: /^.{8,}$/,
      msg: 'Минимум 8 символов'
    },
    full_name: {
      pattern: /^[а-яА-ЯёЁa-zA-Z\s\-]{3,}$/,
      msg: 'Введите полное ФИО'
    },
    phone: {
      pattern: /^[\+\d][\d\s\-\(\)]{6,}$/,
      msg: 'Введите корректный номер телефона'
    },
    email: {
      pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      msg: 'Введите корректный e-mail'
    }
  };

  Object.entries(rules).forEach(([name, rule]) => {
    const input = form.querySelector(`[name="${name}"]`);
    const err   = form.querySelector(`#err_${name}`);
    if (!input) return;

    input.addEventListener('input', () => {
      const valid = rule.pattern.test(input.value.trim());
      input.classList.toggle('is-invalid', !valid);
      input.classList.toggle('is-valid', valid);
      if (err) err.textContent = valid ? '' : rule.msg;
    });
  });

  form.addEventListener('submit', (e) => {
    let ok = true;
    Object.entries(rules).forEach(([name, rule]) => {
      const input = form.querySelector(`[name="${name}"]`);
      if (!input) return;
      const valid = rule.pattern.test(input.value.trim());
      input.classList.toggle('is-invalid', !valid);
      input.classList.toggle('is-valid', valid);
      const err = form.querySelector(`#err_${name}`);
      if (err) err.textContent = valid ? '' : rule.msg;
      if (!valid) ok = false;
    });
    if (!ok) e.preventDefault();
  });
}

// ── Модальное окно ────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id)?.classList.add('active');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('active');
}

// ── Тост-уведомления ──────────────────────────────────────────
function showToast(msg, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `alert alert-${type}`;
  toast.style.cssText = 'position:fixed;top:80px;right:24px;z-index:9999;min-width:280px;max-width:380px;';
  toast.innerHTML = `<span>${msg}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ── Фильтры таблицы (admin) ───────────────────────────────────
function initAdminFilters() {
  const filterStatus = document.getElementById('filterStatus');
  const filterSearch = document.getElementById('filterSearch');
  const rows = document.querySelectorAll('table tbody tr');
  if (!filterStatus && !filterSearch) return;

  function applyFilters() {
    const status = filterStatus ? filterStatus.value : '';
    const search = filterSearch ? filterSearch.value.toLowerCase() : '';
    rows.forEach(row => {
      const rowStatus = row.dataset.status || '';
      const text      = row.textContent.toLowerCase();
      const matchStatus = !status || rowStatus === status;
      const matchSearch = !search || text.includes(search);
      row.style.display = matchStatus && matchSearch ? '' : 'none';
    });
  }

  filterStatus?.addEventListener('change', applyFilters);
  filterSearch?.addEventListener('input', applyFilters);
}

// ── Сортировка таблицы ────────────────────────────────────────
function initTableSort() {
  document.querySelectorAll('th[data-sort]').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
      const table = th.closest('table');
      const tbody = table.querySelector('tbody');
      const col   = Array.from(th.parentElement.children).indexOf(th);
      const asc   = th.dataset.dir !== 'asc';
      th.dataset.dir = asc ? 'asc' : 'desc';
      const rows  = Array.from(tbody.querySelectorAll('tr'));
      rows.sort((a, b) => {
        const va = a.cells[col]?.textContent.trim() || '';
        const vb = b.cells[col]?.textContent.trim() || '';
        return asc ? va.localeCompare(vb, 'ru') : vb.localeCompare(va, 'ru');
      });
      rows.forEach(r => tbody.appendChild(r));
    });
  });
}

// ── Подтверждение смены статуса ───────────────────────────────
function confirmStatus(form) {
  const select = form.querySelector('select');
  const labels = { new: 'Новая', in_progress: 'Идёт обучение', completed: 'Обучение завершено' };
  if (confirm(`Изменить статус на «${labels[select.value]}»?`)) form.submit();
}

// ── Инициализация ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initSlider('mainSlider');
  initRegisterValidation();
  initAdminFilters();
  initTableSort();

  // Закрытие модалок по оверлею
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) overlay.classList.remove('active');
    });
  });

  // Fade-up анимация при появлении
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.fade-up').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
  });
});
