document.addEventListener('DOMContentLoaded', function () {
  // Sidebar Offcanvas Drawer (Menu Pintasan via Tombol Garis Tiga)
  const sidebar = document.getElementById('sidebarMenu');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  const btnToggle = document.getElementById('btnToggleSidebar');
  const btnClose = document.getElementById('btnCloseSidebar');

  window.openSidebar = function() {
    if (sidebar) sidebar.classList.add('show');
    if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
  };

  window.closeSidebar = function() {
    if (sidebar) sidebar.classList.remove('show');
    if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
    document.body.style.overflow = '';
  };

  window.toggleSidebar = function() {
    if (sidebar && sidebar.classList.contains('show')) {
      window.closeSidebar();
    } else {
      window.openSidebar();
    }
  };

  if (btnToggle) {
    btnToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      window.toggleSidebar();
    });
  }

  if (btnClose) {
    btnClose.addEventListener('click', function(e) {
      e.stopPropagation();
      window.closeSidebar();
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function() {
      window.closeSidebar();
    });
  }

  // Close when pressing Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
      window.closeSidebar();
    }
  });

  // Zoom In / Zoom Out Controls
  const btnZoomIn = document.getElementById('btnZoomIn');
  const btnZoomOut = document.getElementById('btnZoomOut');
  const zoomDisplay = document.getElementById('zoomLevelDisplay');

  let currentZoom = parseInt(localStorage.getItem('wh_zoom') || '100', 10);
  if (isNaN(currentZoom) || currentZoom < 60 || currentZoom > 160) {
    currentZoom = 100;
  }

  function applyZoom(zoom) {
    currentZoom = Math.min(150, Math.max(70, zoom));
    document.documentElement.style.zoom = currentZoom + '%';
    localStorage.setItem('wh_zoom', currentZoom);
    if (zoomDisplay) {
      zoomDisplay.textContent = currentZoom + '%';
      zoomDisplay.title = currentZoom === 100 ? 'Zoom Normal (100%)' : 'Klik untuk Reset ke 100%';
    }
  }

  applyZoom(currentZoom);

  if (btnZoomIn) {
    btnZoomIn.addEventListener('click', function(e) {
      e.preventDefault();
      applyZoom(currentZoom + 10);
    });
  }

  if (btnZoomOut) {
    btnZoomOut.addEventListener('click', function(e) {
      e.preventDefault();
      applyZoom(currentZoom - 10);
    });
  }

  if (zoomDisplay) {
    zoomDisplay.addEventListener('click', function(e) {
      e.preventDefault();
      applyZoom(100);
    });
  }

  // Auto-dismiss alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 300);
    }, 5000);
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
