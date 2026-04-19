// ============================================================
// Reuchne Tooth Fairy Clinic — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ---- NAVBAR SCROLL ----
  const navbar = document.getElementById('mainNav');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  // ---- MOBILE NAV TOGGLE ----
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.classList.toggle('active');
    });
  }

  // ---- CAROUSEL ----
  const carousel = document.querySelector('.carousel-wrapper');
  if (carousel) {
    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots   = carousel.querySelectorAll('.carousel-dot');
    const prevBtn = carousel.querySelector('.carousel-btn.prev');
    const nextBtn = carousel.querySelector('.carousel-btn.next');
    let current = 0, timer;

    function goTo(n) {
      slides[current].classList.remove('active');
      if (dots[current]) dots[current].classList.remove('active');
      current = (n + slides.length) % slides.length;
      slides[current].classList.add('active');
      if (dots[current]) dots[current].classList.add('active');
    }

    function autoPlay() {
      timer = setInterval(() => goTo(current + 1), 5000);
    }

    function resetAuto() {
      clearInterval(timer);
      autoPlay();
    }

    if (slides.length > 0) {
      slides[0].classList.add('active');
      if (dots[0]) dots[0].classList.add('active');
      autoPlay();
    }

    dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); resetAuto(); }));
    if (prevBtn) prevBtn.addEventListener('click', () => { goTo(current - 1); resetAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { goTo(current + 1); resetAuto(); });
  }

  // ---- SIMPLE AOS (Animate On Scroll) ----
  const aosElements = document.querySelectorAll('[data-aos]');
  if (aosElements.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const delay = entry.target.dataset.aosDelay || 0;
          setTimeout(() => entry.target.classList.add('aos-animate'), delay);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    aosElements.forEach(el => observer.observe(el));
  }

  // ---- DOCTOR PHOTO GALLERY ----
  const galleryMain = document.querySelector('.gallery-main img');
  const thumbs = document.querySelectorAll('.gallery-thumb');
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      if (galleryMain) {
        galleryMain.style.opacity = '0';
        setTimeout(() => {
          galleryMain.src = thumb.querySelector('img').src;
          galleryMain.style.opacity = '1';
        }, 200);
      }
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });
  if (galleryMain) galleryMain.style.transition = 'opacity 0.2s ease';
  if (thumbs[0]) thumbs[0].classList.add('active');

  // ---- MODAL ----
  document.querySelectorAll('[data-modal]').forEach(btn => {
    const modalId = btn.dataset.modal;
    const modal = document.getElementById(modalId);
    if (modal) {
      btn.addEventListener('click', e => {
        e.preventDefault();
        modal.classList.add('open');
      });
    }
  });
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-backdrop')?.classList.remove('open');
    });
  });

  // ---- ALERT AUTO-DISMISS ----
  document.querySelectorAll('.alert[data-dismiss]').forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.4s';
      setTimeout(() => alert.remove(), 400);
    }, parseInt(alert.dataset.dismiss) || 4000);
  });

  // ---- DROPDOWN (also touch) ----
  document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
    trigger.addEventListener('click', e => {
      e.stopPropagation();
      const menu = trigger.nextElementSibling;
      const isOpen = menu.style.opacity === '1';
      document.querySelectorAll('.dropdown-menu').forEach(m => {
        m.style.opacity = '0'; m.style.visibility = 'hidden'; m.style.transform = 'translateY(-8px)';
      });
      if (!isOpen) {
        menu.style.opacity = '1'; menu.style.visibility = 'visible'; menu.style.transform = 'translateY(0)';
      }
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach(m => {
      m.style.opacity = '0'; m.style.visibility = 'hidden'; m.style.transform = 'translateY(-8px)';
    });
  });

  // ---- SIDEBAR TOGGLE (mobile) ----
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  // ---- FLASH MESSAGE ----
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(() => {
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-16px)';
      flash.style.transition = 'all 0.4s';
      setTimeout(() => flash.remove(), 400);
    }, 4000);
  }

  // ---- CONFIRM DELETE ----
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  // ---- BOOKING CALENDAR ----
  initCalendar();

  // ---- TIME SLOTS ----
  document.querySelectorAll('.time-slot:not(.taken)').forEach(slot => {
    slot.addEventListener('click', () => {
      document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
      slot.classList.add('selected');
      const input = document.getElementById('appointment_time');
      if (input) input.value = slot.dataset.time;
    });
  });

  // ---- PRINT ----
  document.querySelectorAll('[data-print]').forEach(btn => {
    btn.addEventListener('click', () => window.print());
  });

});

// ---- CALENDAR WIDGET ----
function initCalendar() {
  const calWrap = document.querySelector('.calendar-wrapper');
  if (!calWrap) return;

  const dateInput = document.getElementById('appointment_date');
  const monthLabel = calWrap.querySelector('#calMonth');
  const cellsContainer = calWrap.querySelector('.calendar-cells');
  if (!cellsContainer) return;

  const today = new Date();
  let viewYear = today.getFullYear();
  let viewMonth = today.getMonth();
  let selectedDate = null;

  const disabledDays = window.disabledDays || [];   // e.g. [0,6] for Sun/Sat
  const bookedDates  = window.bookedDates  || [];    // array of 'YYYY-MM-DD'

  function renderCalendar() {
    const firstDay = new Date(viewYear, viewMonth, 1).getDay();
    const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    if (monthLabel) monthLabel.textContent = `${monthNames[viewMonth]} ${viewYear}`;

    cellsContainer.innerHTML = '';

    // empty cells
    for (let i = 0; i < firstDay; i++) {
      const empty = document.createElement('div');
      empty.className = 'calendar-cell empty';
      cellsContainer.appendChild(empty);
    }

    for (let d = 1; d <= daysInMonth; d++) {
      const cell = document.createElement('div');
      cell.className = 'calendar-cell';
      cell.textContent = d;

      const dateStr = `${viewYear}-${String(viewMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const dateObj = new Date(viewYear, viewMonth, d);
      const dayOfWeek = dateObj.getDay();

      const isPast    = dateObj < new Date(today.getFullYear(), today.getMonth(), today.getDate());
      const isDisabled = disabledDays.includes(dayOfWeek);
      const isBooked  = bookedDates.includes(dateStr);

      if (isPast || isDisabled || isBooked) {
        cell.classList.add('disabled');
      } else {
        cell.addEventListener('click', () => {
          document.querySelectorAll('.calendar-cell.selected').forEach(c => c.classList.remove('selected'));
          cell.classList.add('selected');
          selectedDate = dateStr;
          if (dateInput) dateInput.value = dateStr;
          loadTimeSlots(dateStr);
        });
      }

      if (dateStr === `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`) {
        cell.classList.add('today');
      }
      if (dateStr === selectedDate) cell.classList.add('selected');

      cellsContainer.appendChild(cell);
    }
  }

  calWrap.querySelector('#calPrev')?.addEventListener('click', () => {
    viewMonth--; if (viewMonth < 0) { viewMonth = 11; viewYear--; }
    renderCalendar();
  });
  calWrap.querySelector('#calNext')?.addEventListener('click', () => {
    viewMonth++; if (viewMonth > 11) { viewMonth = 0; viewYear++; }
    renderCalendar();
  });

  renderCalendar();
}

function loadTimeSlots(date) {
  const container = document.getElementById('timeSlots');
  if (!container) return;
  const doctorId = document.getElementById('doctor_id')?.value;
  if (!doctorId) return;

  container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

  fetch(`/reuchne-clinic/api/timeslots.php?doctor_id=${doctorId}&date=${date}`)
    .then(r => r.json())
    .then(data => {
      container.innerHTML = '';
      if (!data.slots || data.slots.length === 0) {
        container.innerHTML = '<p style="color:var(--gray-500);font-size:0.9rem;">No available slots for this date.</p>';
        return;
      }
      data.slots.forEach(slot => {
        const div = document.createElement('div');
        div.className = `time-slot${slot.taken ? ' taken' : ''}`;
        div.textContent = slot.display;
        div.dataset.time = slot.value;
        if (!slot.taken) {
          div.addEventListener('click', () => {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            div.classList.add('selected');
            document.getElementById('appointment_time').value = slot.value;
          });
        }
        container.appendChild(div);
      });
    })
    .catch(() => {
      container.innerHTML = '<p style="color:#ef4444;">Failed to load time slots. Please try again.</p>';
    });
}

// Number formatting helpers
function formatPHP(amount) {
  return '₱' + parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}
