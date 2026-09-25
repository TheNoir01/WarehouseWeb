<?php

namespace App\Controllers;

use App\Services\ApiService;

class AuthController
{
    public function login(): void
    {
        if (isAuthenticated()) {
            redirect('dashboard');
        }
        include __DIR__ . '/../views/auth/login.php';
    }

    public function loginSubmit(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            redirect('login', 'Email dan password wajib diisi.', 'danger');
        }

        $api = new ApiService();
        $response = $api->post('auth/login', [
            'email' => $email,
            'password' => $password,
            'device_name' => 'Web PHP Client',
        ]);

        if (!empty($response['success']) && !empty($response['data']['token'])) {
            $_SESSION['token'] = $response['data']['token'];
            $_SESSION['user'] = $response['data']['user'];
            redirect('dashboard', 'Selamat datang kembali, ' . $response['data']['user']['name'] . '!');
        } else {
            $msg = $response['message'] ?? 'Login gagal. Periksa kredensial Anda.';
            redirect('login', $msg, 'danger');
        }
    }

    public function logout(): void
    {
        if (isAuthenticated()) {
            $api = new ApiService();
            $api->post('auth/logout');
        }
        unset($_SESSION['token'], $_SESSION['user']);
        redirect('login', 'Anda telah berhasil logout.');
    }
}
