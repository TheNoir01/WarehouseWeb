document.addEventListener('DOMContentLoaded', function () {
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
