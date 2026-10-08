document.addEventListener('DOMContentLoaded', function () {
  // Sidebar Offcanvas Drawer (Menu Pintasan via Tombol Garis Tiga)
  function getSidebar() { return document.getElementById('sidebarMenu'); }
  function getBackdrop() { return document.getElementById('sidebarBackdrop'); }

  if (!window.openSidebar) {
    window.openSidebar = function() {
      const s = getSidebar();
      const b = getBackdrop();
      if (s) s.classList.add('show');
      if (b) b.classList.add('show');
      document.body.style.overflow = 'hidden';
    };
  }

  if (!window.closeSidebar) {
    window.closeSidebar = function() {
      const s = getSidebar();
      const b = getBackdrop();
      if (s) s.classList.remove('show');
      if (b) b.classList.remove('show');
      document.body.style.overflow = '';
    };
  }

  let lastToggleTime = 0;
  window.toggleSidebar = function() {
    const now = Date.now();
    if (now - lastToggleTime < 250) return; // Prevent double-trigger from inline onclick + event listener
    lastToggleTime = now;

    const s = getSidebar();
    if (s && s.classList.contains('show')) {
      window.closeSidebar();
    } else {
      window.openSidebar();
    }
  };

  const btnToggle = document.getElementById('btnToggleSidebar');
  if (btnToggle) {
    btnToggle.onclick = function(e) {
      if (e) e.stopPropagation();
      window.toggleSidebar();
    };
  }

  const brandShortcut = document.querySelector('.brand-shortcut');
  if (brandShortcut) {
    brandShortcut.onclick = function(e) {
      if (e) e.stopPropagation();
      window.toggleSidebar();
    };
  }

  const btnClose = document.getElementById('btnCloseSidebar');
  if (btnClose) {
    btnClose.onclick = function(e) {
      if (e) e.stopPropagation();
      window.closeSidebar();
    };
  }

  const sidebarBackdrop = getBackdrop();
  if (sidebarBackdrop) {
    sidebarBackdrop.onclick = function(e) {
      if (e) e.stopPropagation();
      window.closeSidebar();
    };
  }

  // Close when pressing Escape key
  document.addEventListener('keydown', function(e) {
    const sidebar = getSidebar();
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
      window.closeSidebar();
    }
  });

  // Zoom Dropdown Control
  const zoomSelect = document.getElementById('zoomSelect');

  let currentZoom = parseInt(localStorage.getItem('wh_zoom') || '100', 10);
  if (isNaN(currentZoom) || currentZoom < 60 || currentZoom > 160) {
    currentZoom = 100;
  }

  function applyZoom(zoom) {
    currentZoom = Math.min(150, Math.max(70, zoom));
    document.documentElement.style.zoom = currentZoom + '%';
    localStorage.setItem('wh_zoom', currentZoom);

    if (zoomSelect) {
      let matched = false;
      for (let i = 0; i < zoomSelect.options.length; i++) {
        if (parseInt(zoomSelect.options[i].value, 10) === currentZoom) {
          zoomSelect.selectedIndex = i;
          matched = true;
          break;
        }
      }
      if (!matched) {
        const opt = new Option(currentZoom + '%', currentZoom, true, true);
        zoomSelect.add(opt);
      }
    }
  }

  applyZoom(currentZoom);

  if (zoomSelect) {
    zoomSelect.addEventListener('change', function () {
      applyZoom(parseInt(this.value, 10));
    });
  }

  // Auto-dismiss alert-warning & alert-success (alert-sukses) setelah durasi 10 detik
  const autoHideAlerts = document.querySelectorAll('.alert-warning, .alert-success, .alert-sukses');
  autoHideAlerts.forEach(alert => {
    // Jangan auto-dismiss alert di dalam kotak pengecekan duplikat barang
    if (alert.closest('#duplicate_warning_box')) return;

    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease';
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-6px)';
      setTimeout(() => alert.remove(), 500);
    }, 10000); // 10 detik
  });

  // Modal helpers
  window.openModal = function(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
  };

  window.closeModal = function(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
  };

  // Close modal when clicking backdrop
  document.querySelectorAll('.modal-backdrop').forEach(modal => {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('show');
      }
    });
  });

  // Duplicate Item Warning Check
  const itemNameInput = document.getElementById('item_name_input');
  const duplicateWarningBox = document.getElementById('duplicate_warning_box');
  let duplicateTimer = null;

  if (itemNameInput && duplicateWarningBox) {
    itemNameInput.addEventListener('input', function() {
      clearTimeout(duplicateTimer);
      const val = this.value.trim();
      if (val.length < 3) {
        duplicateWarningBox.innerHTML = '';
        duplicateWarningBox.style.display = 'none';
        return;
      }

      duplicateTimer = setTimeout(() => {
        fetch(`index.php?r=items/check-duplicate-ajax&name=${encodeURIComponent(val)}`)
          .then(res => res.json())
          .then(data => {
            if (data && data.length > 0) {
              let html = `<div class="alert alert-warning" style="display:block; font-size:0.85rem;">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Peringatan Barang Mirip Terdeteksi:</strong>
                <p class="mb-1 text-muted">Barang dengan nama serupa sudah ada di sistem. Pastikan tidak terjadi duplikasi tidak disengaja:</p>
                <ul style="padding-left: 1.25rem; margin-top: 0.35rem;">`;
              data.forEach(item => {
                html += `<li><strong>[${item.company_code}]</strong> ${item.item_code} - ${item.name} (Stok: ${item.total_stock} ${item.unit || ''})</li>`;
              });
              html += `</ul></div>`;
              duplicateWarningBox.innerHTML = html;
              duplicateWarningBox.style.display = 'block';
            } else {
              duplicateWarningBox.innerHTML = '';
              duplicateWarningBox.style.display = 'none';
            }
          })
          .catch(() => {});
      }, 400);
    });
  }
});
