<?php

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = rtrim(dirname($scriptName), '/\\');
    
    // Clean base for root directory
    if ($base === '/' || $base === '\\') {
        $base = '';
    }

    if (!$path) {
        return ($base ? $base . '/' : '/');
    }

    // Parse route and query string if path contains ? or &
    $query = '';
    if (strpos($path, '?') !== false) {
        [$route, $query] = explode('?', $path, 2);
    } elseif (strpos($path, '&') !== false) {
        [$route, $query] = explode('&', $path, 2);
    } else {
        $route = $path;
    }

    $url = ($base ? $base . '/' : '/') . 'index.php?r=' . $route;
    if ($query !== '') {
        $url .= '&' . $query;
    }

    return $url;
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = rtrim(dirname($scriptName), '/\\');
    if ($base === '/' || $base === '\\') {
        $base = '';
    }
    
    $fullPath = dirname(__DIR__, 2) . '/public/assets/' . $path;
    $version = file_exists($fullPath) ? '?v=' . filemtime($fullPath) : '';
    
    return ($base ? $base . '/' : '/') . 'assets/' . $path . $version;
}

function redirect(string $route, ?string $message = null, string $type = 'success'): void
{
    if ($message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
    header('Location: ' . url($route));
    exit;
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function apiToken(): ?string
{
    return $_SESSION['token'] ?? null;
}

function isAuthenticated(): bool
{
    return !empty($_SESSION['token']) && !empty($_SESSION['user']);
}

function isMaintenance(): bool
{
    return (currentUser()['role'] ?? '') === 'maintenance';
}

function isKepalaGudang(): bool
{
    return (currentUser()['role'] ?? '') === 'kepala_gudang';
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'admin';
}

function isKaryawan(): bool
{
    return (currentUser()['role'] ?? '') === 'karyawan';
}

function isPurchasing(): bool
{
    return (currentUser()['role'] ?? '') === 'purchasing';
}

function canManagePurchasing(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['purchasing', 'maintenance']);
}

function canEditPrice(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['purchasing', 'maintenance']);
}

function canEditItem(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'kepala_gudang', 'karyawan']);
}

function canManageOperational(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'kepala_gudang', 'karyawan']);
}

function canViewQrCode(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'admin', 'kepala_gudang', 'karyawan']);
}

function formatRupiah($amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function canManageMaster(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'kepala_gudang', 'karyawan']);
}

function canViewReports(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'admin', 'kepala_gudang']);
}

function canManageUsers(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['maintenance', 'admin', 'kepala_gudang']);
}

function formatQty($qty, ?string $unit = ''): string
{
    $formatted = number_format((float) $qty, 2, ',', '.');
    // Remove trailing ,00 for neatness
    if (str_ends_with($formatted, ',00')) {
        $formatted = substr($formatted, 0, -3);
    }
    return $formatted . ($unit ? ' ' . $unit : '');
}

function formatDate(?string $date): string
{
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime(?string $dateTime): string
{
    if (!$dateTime) return '-';
    return date('d/m/Y H:i', strtotime($dateTime));
}

function renderBadge(string $status): string
{
    $statusUpper = strtoupper($status);
    $colorClass = match ($statusUpper) {
        'HABIS' => 'badge-danger',
        'MENIPIS' => 'badge-warning',
        'TERSEDIA' => 'badge-success',
        'COMPLETED', 'CLOSED', 'FULLY_RETURNED' => 'badge-success',
        'OPEN', 'PARTIALLY_RETURNED' => 'badge-primary',
        'CANCELLED' => 'badge-danger',
        default => 'badge-secondary',
    };

    return "<span class=\"badge {$colorClass}\">{$statusUpper}</span>";
}

function icon(string $name, string $extraClass = '', ?string $style = null): string
{
    $styleAttr = $style ? " style=\"{$style}\"" : '';
    return "<i class=\"bi bi-{$name} {$extraClass}\"{$styleAttr}></i>";
}

function renderCompanyBadge(?string $code): string
{
    $codeUpper = strtoupper(trim((string) $code));
    if ($codeUpper === 'LNP') {
        return '<span class="badge badge-company-lnp" style="background-color: #0284c7; color: #ffffff; border: 1px solid #0369a1; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.03em;">LNP</span>';
    } elseif ($codeUpper === 'KJG') {
        return '<span class="badge badge-company-kjg" style="background-color: #8B0000; color: #ffffff; border: 1px solid #6b001b; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.03em;">KJG</span>';
    } elseif (!empty($codeUpper) && $codeUpper !== '-') {
        return '<span class="badge badge-secondary" style="font-weight: 700; font-size: 0.75rem;">' . htmlspecialchars($codeUpper) . '</span>';
    }
    return '<span class="text-muted" style="font-size: 0.75rem;">-</span>';
}

