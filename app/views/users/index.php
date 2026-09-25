<?php
$pageTitle = 'Manajemen Pengguna & Hak Akses';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah Pengguna -->
  <div class="card">
    <div class="card-header">
      <span>+ Tambah Pengguna Baru</span>
    </div>
    <div class="card-body">
      <form action="<?= url('users/store') ?>" method="POST">
        <div class="form-group">
          <label for="user_name">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="name" id="user_name" class="form-control" required placeholder="Contoh: Rahmat Hidayat">
        </div>
        <div class="form-group">
          <label for="user_email">Alamat Email <span class="text-danger">*</span></label>
          <input type="email" name="email" id="user_email" class="form-control" required placeholder="rahmat@warehouse.test">
        </div>
        <div class="form-group">
          <label for="user_username">Username</label>
          <input type="text" name="username" id="user_username" class="form-control" placeholder="rahmat">
        </div>
        <div class="form-group">
          <label for="user_password">Password Awal <span class="text-danger">*</span></label>
          <input type="password" name="password" id="user_password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
        </div>
        <div class="form-group">
          <label for="user_role_id">Hak Akses (Role) <span class="text-danger">*</span></label>
          <select name="role_id" id="user_role_id" class="form-select" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label'] ?? $r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="user_company_id">Afiliasi PT (Opsional jika multi-PT)</label>
          <select name="company_id" id="user_company_id" class="form-select">
            <option value="">Multi-Company / Semua PT</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'] . ' - ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Daftarkan Pengguna</button>
      </form>
    </div>
  </div>

  <!-- Daftar Pengguna -->
  <div class="card">
    <div class="card-header">
      <span>Daftar Pengguna Sistem</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Nama & Email</th>
            <th>Role / Hak Akses</th>
            <th>PT Terkait</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['email']) ?></div>
              </td>
              <td>
                <span class="badge badge-primary"><?= htmlspecialchars($u['role']['label'] ?? $u['role']['name'] ?? '-') ?></span>
              </td>
              <td>
                <?= !empty($u['company']) ? htmlspecialchars($u['company']['code']) : '<span class="text-muted">Semua PT</span>' ?>
              </td>
              <td>
                <span class="badge <?= ($u['is_active'] ?? true) ? 'badge-success' : 'badge-danger' ?>">
                  <?= ($u['is_active'] ?? true) ? 'AKTIF' : 'NONAKTIF' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
