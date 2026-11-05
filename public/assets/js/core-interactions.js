/**
 * e-Mabalacat Core Client Interactions & JavaScript Form Validations
 * Pure behavior: Real-time inline field validation, interactive Leaflet maps,
 * live camera/photo upload previews, native <dialog> modals, and toast notifications.
 * NO HTML template rendering.
 */
(() => {
  'use strict';

  // -------------------------------------------------------------------------
  // 1. Toast Notifications Dismiss and Fade Triggers
  // -------------------------------------------------------------------------
  window.showToast = function(message, type = 'info', duration = 4000) {
    let stack = document.getElementById('toasts');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toasts';
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<span>${message}</span><button type="button" aria-label="Dismiss">&times;</button>`;

    const closeBtn = toast.querySelector('button');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 250);
      });
    }

    stack.appendChild(toast);

    if (duration > 0) {
      setTimeout(() => {
        if (toast.isConnected) {
          toast.style.opacity = '0';
          setTimeout(() => toast.remove(), 250);
        }
      }, duration);
    }
  };

  // -------------------------------------------------------------------------
  // 2. JavaScript Client-Side Form Validations (Live Feedback & Pre-Submit)
  // -------------------------------------------------------------------------
  function setFieldError(input, message) {
    if (!input) return;
    input.classList.add('is-invalid');
    input.style.borderColor = 'var(--red)';

    let errorEl = input.parentElement.querySelector('.error-text');
    if (!errorEl) {
      errorEl = document.createElement('p');
      errorEl.className = 'error-text';
      input.parentElement.appendChild(errorEl);
    }
    errorEl.textContent = message;
    errorEl.classList.add('show');
  }

  function clearFieldError(input) {
    if (!input) return;
    input.classList.remove('is-invalid');
    input.style.borderColor = '';

    const errorEl = input.parentElement.querySelector('.error-text');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.remove('show');
    }
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email).trim());
  }

  function initFormValidations() {
    // A. Registration Form Validation
    const regForm = document.querySelector('form[action*="register.php"]');
    if (regForm) {
      regForm.setAttribute('novalidate', 'true');

      // Live blur/input triggers
      const nameInput = regForm.querySelector('#regName');
      const emailInput = regForm.querySelector('#regEmail');
      const passInput = regForm.querySelector('#regPassword');
      const confirmInput = regForm.querySelector('#regConfirmPassword');
      const otpInput = regForm.querySelector('#regEnteredOtp');

      if (nameInput) {
        nameInput.addEventListener('input', () => {
          if (nameInput.value.trim().length >= 2) clearFieldError(nameInput);
        });
      }

      if (emailInput) {
        emailInput.addEventListener('input', () => {
          if (validateEmail(emailInput.value)) clearFieldError(emailInput);
        });
      }

      if (passInput) {
        passInput.addEventListener('input', () => {
          if (passInput.value.length >= 6) clearFieldError(passInput);
          if (confirmInput && confirmInput.value && confirmInput.value === passInput.value) {
            clearFieldError(confirmInput);
          }
        });
      }

      if (confirmInput) {
        confirmInput.addEventListener('input', () => {
          if (passInput && confirmInput.value === passInput.value) {
            clearFieldError(confirmInput);
          }
        });
      }

      if (otpInput) {
        otpInput.addEventListener('input', () => {
          if (otpInput.value.trim().length === 4) clearFieldError(otpInput);
        });
      }

      // Pre-submit validation
      regForm.addEventListener('submit', (e) => {
        let hasError = false;

        if (nameInput && nameInput.value.trim().length < 2) {
          setFieldError(nameInput, 'Please enter your full official name (at least 2 characters).');
          hasError = true;
        }

        if (emailInput && !validateEmail(emailInput.value)) {
          setFieldError(emailInput, 'Please provide a valid email address (e.g. name@example.ph).');
          hasError = true;
        }

        if (passInput && passInput.value.length < 6) {
          setFieldError(passInput, 'Password must contain at least 6 characters.');
          hasError = true;
        }

        if (confirmInput && passInput && confirmInput.value !== passInput.value) {
          setFieldError(confirmInput, 'Passwords do not match. Please re-enter.');
          hasError = true;
        }

        if (otpInput && otpInput.value.trim().length !== 4) {
          setFieldError(otpInput, 'Please enter the 4-digit verification code.');
          hasError = true;
        }

        const consentCheck = regForm.querySelector('input[name="consent"]');
        if (consentCheck && !consentCheck.checked) {
          window.showToast('Please check the data privacy consent box to proceed.', 'error');
          hasError = true;
        }

        if (hasError) {
          e.preventDefault();
          const firstErr = regForm.querySelector('.is-invalid');
          if (firstErr) firstErr.focus();
        }
      });
    }

    // B. Login Form Validation (Resident password & Staff login)
    document.querySelectorAll('form[action*="login.php"]').forEach(form => {
      form.setAttribute('novalidate', 'true');

      form.addEventListener('submit', (e) => {
        let hasError = false;
        const emailInput = form.querySelector('input[name="email"]');
        const passInput = form.querySelector('input[name="password"]');
        const otpInput = form.querySelector('input[name="otp"]');

        if (emailInput && !validateEmail(emailInput.value)) {
          setFieldError(emailInput, 'Please enter a valid email address.');
          hasError = true;
        } else if (emailInput) {
          clearFieldError(emailInput);
        }

        if (passInput && !passInput.value.trim()) {
          setFieldError(passInput, 'Password is required to sign in.');
          hasError = true;
        } else if (passInput) {
          clearFieldError(passInput);
        }

        if (otpInput && otpInput.value.trim().length !== 4) {
          setFieldError(otpInput, 'Please enter the 4-digit verification code.');
          hasError = true;
        } else if (otpInput) {
          clearFieldError(otpInput);
        }

        if (hasError) {
          e.preventDefault();
          const firstErr = form.querySelector('.is-invalid');
          if (firstErr) firstErr.focus();
        }
      });
    });

    // C. Hazard Report Form Validation
    const reportForm = document.getElementById('reportWizardForm');
    if (reportForm) {
      reportForm.setAttribute('novalidate', 'true');

      const descInput = reportForm.querySelector('#reportDesc');
      const addrInput = reportForm.querySelector('#reportAddress');

      if (descInput) {
        descInput.addEventListener('input', () => {
          if (descInput.value.trim().length >= 10) clearFieldError(descInput);
        });
      }

      if (addrInput) {
        addrInput.addEventListener('input', () => {
          if (addrInput.value.trim().length >= 3) clearFieldError(addrInput);
        });
      }

      reportForm.addEventListener('submit', (e) => {
        let hasError = false;

        const selectedCat = reportForm.querySelector('input[name="category"]:checked');
        if (!selectedCat) {
          window.showToast('Please select a hazard category.', 'error');
          hasError = true;
        }

        if (addrInput && addrInput.value.trim().length < 3) {
          setFieldError(addrInput, 'Please provide a street or nearby landmark for field responders.');
          hasError = true;
        }

        if (descInput && descInput.value.trim().length < 10) {
          setFieldError(descInput, 'Please describe the hazard in more detail (at least 10 characters).');
          hasError = true;
        }

        if (hasError) {
          e.preventDefault();
          const firstErr = reportForm.querySelector('.is-invalid');
          if (firstErr) firstErr.focus();
        }
      });
    }

    // D. Dispatch Work Order Form Validation
    const dispatchForm = document.querySelector('form[action*="dispatch.php"]');
    if (dispatchForm) {
      dispatchForm.setAttribute('novalidate', 'true');
      dispatchForm.addEventListener('submit', (e) => {
        const teamSelect = dispatchForm.querySelector('#teamSelect');
        if (teamSelect && !teamSelect.value) {
          setFieldError(teamSelect, 'Please select a response team before dispatching.');
          e.preventDefault();
          teamSelect.focus();
        }
      });
    }
  }

  // -------------------------------------------------------------------------
  // 3. Native <dialog> Modal Open/Close Controls
  // -------------------------------------------------------------------------
  const modal = document.getElementById('modal');
  if (modal) {
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
      btn.addEventListener('click', () => {
        const title = btn.getAttribute('data-modal-title') || 'Information';
        const body = btn.getAttribute('data-modal-body') || '';
        const titleEl = document.getElementById('modalTitle');
        const bodyEl = document.getElementById('modalBody');
        if (titleEl) titleEl.textContent = title;
        if (bodyEl) bodyEl.innerHTML = body;
        modal.showModal();
      });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
      btn.addEventListener('click', () => modal.close());
    });

    modal.addEventListener('click', (e) => {
      const rect = modal.getBoundingClientRect();
      if (
        e.clientX < rect.left ||
        e.clientX > rect.right ||
        e.clientY < rect.top ||
        e.clientY > rect.bottom
      ) {
        modal.close();
      }
    });
  }

  // -------------------------------------------------------------------------
  // 4. Live Camera / File Upload Thumbnail Preview
  // -------------------------------------------------------------------------
  document.querySelectorAll('input[type="file"][data-photo-preview]').forEach(input => {
    input.addEventListener('change', () => {
      const targetId = input.getAttribute('data-photo-preview');
      const container = document.getElementById(targetId);
      if (!container) return;

      const img = container.querySelector('img');
      const file = input.files && input.files[0];

      if (file && img) {
        const reader = new FileReader();
        reader.onload = (e) => {
          img.src = e.target.result;
          container.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else if (container) {
        container.style.display = 'none';
      }
    });
  });

  // -------------------------------------------------------------------------
  // 5. Instant Client-Side Table & Directory Filters
  // -------------------------------------------------------------------------
  const brgySearch = document.getElementById('brgySearch');
  if (brgySearch) {
    brgySearch.addEventListener('input', () => {
      const q = brgySearch.value.trim().toLowerCase();
      const cards = document.querySelectorAll('#brgyDirectory .brgy-card');
      let visible = 0;

      cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        if (!q || name.includes(q)) {
          card.style.display = '';
          visible++;
        } else {
          card.style.display = 'none';
        }
      });

      const countEl = document.getElementById('brgyCount');
      if (countEl) countEl.textContent = `${visible} barangay${visible === 1 ? '' : 's'}`;
    });
  }

  // -------------------------------------------------------------------------
  // 6. Leaflet Maps Initialization
  // -------------------------------------------------------------------------
  function initMaps() {
    if (typeof L === 'undefined') return;

    // A. Preview Map (Hero section)
    const heroPreview = document.getElementById('heroPreviewMap');
    if (heroPreview) {
      const lat = parseFloat(heroPreview.getAttribute('data-lat') || '15.22');
      const lng = parseFloat(heroPreview.getAttribute('data-lng') || '120.575');
      const zoom = parseInt(heroPreview.getAttribute('data-zoom') || '12', 10);

      const map = L.map(heroPreview, {
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        touchZoom: false,
        scrollWheelZoom: false,
        doubleClickZoom: false
      }).setView([lat, lng], zoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
      }).addTo(map);

      L.circleMarker([lat, lng], {
        radius: 8,
        fillColor: '#f5d10d',
        color: '#063151',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.9
      }).addTo(map);
    }

    // B. Interactive Service Map
    const serviceMapEl = document.getElementById('serviceMap');
    if (serviceMapEl) {
      const lat = parseFloat(serviceMapEl.getAttribute('data-lat') || '15.215');
      const lng = parseFloat(serviceMapEl.getAttribute('data-lng') || '120.575');
      const zoom = parseInt(serviceMapEl.getAttribute('data-zoom') || '13', 10);

      const map = L.map(serviceMapEl).setView([lat, lng], zoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      let rawPins = [];
      try {
        rawPins = JSON.parse(serviceMapEl.getAttribute('data-pins') || '[]');
      } catch (err) {
        rawPins = [];
      }

      const markers = {};
      rawPins.forEach(pin => {
        const color = pin.priority === 'emergency' ? '#b42318' : (pin.priority === 'high' ? '#d9b600' : '#1766a1');
        const marker = L.circleMarker([pin.lat, pin.lng], {
          radius: 7,
          fillColor: color,
          color: '#ffffff',
          weight: 2,
          opacity: 1,
          fillOpacity: 0.85
        }).addTo(map);

        marker.bindPopup(`
          <div style="font-family:var(--sans);font-size:13px;line-height:1.4;">
            <strong style="color:#063151;">${pin.categoryName}</strong><br>
            <span style="color:#5b6d7a;">${pin.barangay} &bull; ${pin.code}</span><br>
            <span style="font-size:11px;font-weight:600;display:inline-block;margin-top:4px;">${pin.slaShort}</span>
          </div>
        `);

        markers[pin.id] = marker;
      });

      // Side-list item tap navigates to pin
      document.querySelectorAll('#mapIncidentsList .mini-ticket').forEach(item => {
        item.addEventListener('click', () => {
          const id = item.getAttribute('data-pin-id');
          if (markers[id]) {
            const m = markers[id];
            map.flyTo(m.getLatLng(), 15, { duration: 0.8 });
            m.openPopup();
          }
        });
      });
    }

    // C. Report GPS Coordinate Picker Map
    const pickerMapEl = document.getElementById('pickerMap');
    if (pickerMapEl) {
      const initialLat = parseFloat(pickerMapEl.getAttribute('data-lat') || '15.215');
      const initialLng = parseFloat(pickerMapEl.getAttribute('data-lng') || '120.575');
      const initialZoom = parseInt(pickerMapEl.getAttribute('data-zoom') || '14', 10);

      const map = L.map(pickerMapEl).setView([initialLat, initialLng], initialZoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      const pickerMarker = L.marker([initialLat, initialLng], {
        draggable: true
      }).addTo(map);

      function updateCoords(lat, lng) {
        const inputLat = document.getElementById('inputLat');
        const inputLng = document.getElementById('inputLng');
        const displayLat = document.getElementById('displayLat');
        const displayLng = document.getElementById('displayLng');

        if (inputLat) inputLat.value = lat.toFixed(6);
        if (inputLng) inputLng.value = lng.toFixed(6);
        if (displayLat) displayLat.textContent = lat.toFixed(6);
        if (displayLng) displayLng.textContent = lng.toFixed(6);
      }

      pickerMarker.on('dragend', (e) => {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
      });

      map.on('click', (e) => {
        pickerMarker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
      });

      const btnGps = document.getElementById('btnUseGps');
      if (btnGps && navigator.geolocation) {
        btnGps.addEventListener('click', () => {
          btnGps.disabled = true;
          btnGps.textContent = 'Locating GPS...';

          navigator.geolocation.getCurrentPosition(
            (pos) => {
              const userLat = pos.coords.latitude;
              const userLng = pos.coords.longitude;
              map.flyTo([userLat, userLng], 16);
              pickerMarker.setLatLng([userLat, userLng]);
              updateCoords(userLat, userLng);
              btnGps.disabled = false;
              btnGps.innerHTML = 'GPS Located ✓';
              setTimeout(() => {
                btnGps.innerHTML = 'Use my device location';
              }, 3000);
            },
            (err) => {
              btnGps.disabled = false;
              btnGps.innerHTML = 'GPS Unavailable';
              setTimeout(() => {
                btnGps.innerHTML = 'Use my device location';
              }, 3000);
            },
            { timeout: 10000, enableHighAccuracy: true }
          );
        });
      }
    }
  }

  function init() {
    initFormValidations();
    initMaps();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
