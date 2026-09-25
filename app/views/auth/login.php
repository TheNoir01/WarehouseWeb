<?php
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistem Gudang</title>
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      padding: 1rem;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      border-radius: 12px;
      padding: 2.25rem;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
    }
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .login-logo {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      color: var(--primary);
    }
    .login-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: #0f172a;
    }
    .login-subtitle {
      font-size: 0.85rem;
      color: #64748b;
    }
    .demo-accounts {
      margin-top: 1.5rem;
      padding: 1rem;
      background: #f8fafc;
      border-radius: 8px;
      border: 1px dashed #cbd5e1;
      font-size: 0.8rem;
    }
    .demo-btn {
      cursor: pointer;
      display: inline-block;
      margin: 2px;
      padding: 3px 8px;
      background: #e2e8f0;
      border-radius: 4px;
      font-weight: 600;
      color: #1e293b;
    }
    .demo-btn:hover {
      background: #cbd5e1;
    }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <div class="login-logo"><i class="bi bi-box-seam-fill"></i></div>
    <h1 class="login-title">Sistem Gudang</h1>
    <p class="login-subtitle">1 Gudang Fisik</p>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
      <span><?= htmlspecialchars($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <form action="<?= url('auth/login-submit') ?>" method="POST">
    <div class="form-group">
      <label for="email"><i class="bi bi-person"></i> Email atau Username</label>
      <input type="text" id="email" name="email" class="form-control" required placeholder="kepala@warehouse.test" autofocus>
    </div>

    <div class="form-group">
      <label for="password"><i class="bi bi-lock"></i> Password</label>
      <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; margin-top: 0.5rem;">
      <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
    </button>
  </form>

  <div class="demo-accounts">
    <div class="fw-bold mb-1" style="color: #475569;"><i class="bi bi-key-fill"></i> Akun Uji Coba Cepat (Password: password):</div>
    <div>
      <span class="demo-btn" onclick="fillDemo('kepala@warehouse.test')">Kepala Gudang</span>
      <span class="demo-btn" onclick="fillDemo('karyawan@warehouse.test')">Karyawan</span>
      <span class="demo-btn" onclick="fillDemo('admin@warehouse.test')">Admin</span>
    </div>
  </div>
</div>

<script>
function fillDemo(email) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = 'password';
}
</script>

</body>
</html>
