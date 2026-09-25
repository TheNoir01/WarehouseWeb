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
    return ($base ? $base . '/' : '/') . 'assets/' . $path;
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

function canManageMaster(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['admin', 'kepala_gudang', 'karyawan']);
}

function canViewReports(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['admin', 'kepala_gudang']);
}

function canManageUsers(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['admin', 'kepala_gudang']);
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
