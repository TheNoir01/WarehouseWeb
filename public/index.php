<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/helpers.php';
require_once __DIR__ . '/../app/services/ApiService.php';
require_once __DIR__ . '/../app/services/SimpleXlsxWriter.php';

// Autoload controllers
spl_autoload_register(function ($class) {
    $prefix = 'App\\Controllers\\';
    $baseDir = __DIR__ . '/../app/controllers/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ItemController;
use App\Controllers\GoodsReceiptController;
use App\Controllers\StockIssueController;
use App\Controllers\ReturnController;
use App\Controllers\RemnantController;
use App\Controllers\MasterController;
use App\Controllers\ReportController;
use App\Controllers\UserController;

$rawRoute = $_GET['r'] ?? 'dashboard';

// If $rawRoute contains query params (e.g. 'receipts/show&id=7' from encoded URL)
if (strpos($rawRoute, '&') !== false || strpos($rawRoute, '?') !== false) {
    $delimiter = (strpos($rawRoute, '?') !== false) ? '?' : '&';
    $parts = explode($delimiter, $rawRoute, 2);
    $route = $parts[0];
    parse_str($parts[1] ?? '', $extraParams);
    foreach ($extraParams as $k => $v) {
        if (!isset($_GET[$k])) {
            $_GET[$k] = $v;
        }
    }
} else {
    $route = $rawRoute;
}

// Public routes
if ($route === 'login' || $route === 'auth/login') {
    (new AuthController())->login();
    exit;
}
if ($route === 'auth/login-submit') {
    (new AuthController())->loginSubmit();
    exit;
}

// Authentication Guard
if (!isAuthenticated()) {
    redirect('login', 'Silakan login terlebih dahulu untuk mengakses sistem.', 'warning');
}

// Protected routes
switch ($route) {
    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'auth/logout':
        (new AuthController())->logout();
        break;

    // Items
    case 'items':
        (new ItemController())->index();
        break;
    case 'items/create':
        (new ItemController())->create();
        break;
    case 'items/store':
        (new ItemController())->store();
        break;
    case 'items/show':
        (new ItemController())->show();
        break;
    case 'items/check-duplicate-ajax':
        (new ItemController())->checkDuplicateAjax();
        break;
    case 'items/ajax-store':
        (new ItemController())->ajaxStore();
        break;
    case 'items/search-ajax':
        (new ItemController())->searchAjax();
        break;

    // Goods Receipts
    case 'receipts':
        (new GoodsReceiptController())->index();
        break;
    case 'receipts/create':
        (new GoodsReceiptController())->create();
        break;
    case 'receipts/store':
        (new GoodsReceiptController())->store();
        break;
    case 'receipts/show':
        (new GoodsReceiptController())->show();
        break;
    case 'receipts/export-excel':
        (new GoodsReceiptController())->exportExcel();
        break;

    // Stock Issues
    case 'issues':
        (new StockIssueController())->index();
        break;
    case 'issues/create':
        (new StockIssueController())->create();
        break;
    case 'issues/store':
        (new StockIssueController())->store();
        break;
    case 'issues/show':
        (new StockIssueController())->show();
        break;
    case 'issues/ajax-get':
        (new StockIssueController())->ajaxGet();
        break;

    // Returns
    case 'returns':
        (new ReturnController())->index();
        break;
    case 'returns/create':
        (new ReturnController())->create();
        break;
    case 'returns/store':
        (new ReturnController())->store();
        break;
    case 'returns/show':
        (new ReturnController())->show();
        break;

    // Remnants
    case 'remnants':
        (new RemnantController())->index();
        break;
    case 'remnants/show':
        (new RemnantController())->show();
        break;
    case 'remnants/update':
        (new RemnantController())->update();
        break;

    // Master Data
    case 'masters/categories':
        (new MasterController())->categories();
        break;
    case 'masters/categories-store':
        (new MasterController())->categoriesStore();
        break;
    case 'masters/categories-update':
        (new MasterController())->categoriesUpdate();
        break;
    case 'masters/categories-destroy':
        (new MasterController())->categoriesDestroy();
        break;
    case 'masters/types-store':
        (new MasterController())->typesStore();
        break;
    case 'masters/units':
        (new MasterController())->units();
        break;
    case 'masters/units-store':
        (new MasterController())->unitsStore();
        break;
    case 'masters/units-update':
        (new MasterController())->unitsUpdate();
        break;
    case 'masters/units-destroy':
        (new MasterController())->unitsDestroy();
        break;
    case 'masters/suppliers':
        (new MasterController())->suppliers();
        break;
    case 'masters/suppliers-store':
        (new MasterController())->suppliersStore();
        break;
    case 'masters/suppliers-update':
        (new MasterController())->suppliersUpdate();
        break;
    case 'masters/suppliers-destroy':
        (new MasterController())->suppliersDestroy();
        break;
    case 'masters/locations':
        (new MasterController())->locations();
        break;
    case 'masters/locations-store':
        (new MasterController())->locationsStore();
        break;
    case 'masters/locations-update':
        (new MasterController())->locationsUpdate();
        break;
    case 'masters/locations-destroy':
        (new MasterController())->locationsDestroy();
        break;
    case 'masters/companies':
        (new MasterController())->companies();
        break;
    case 'masters/companies-store':
        (new MasterController())->companiesStore();
        break;
    case 'masters/companies-update':
        (new MasterController())->companiesUpdate();
        break;
    case 'masters/companies-destroy':
        (new MasterController())->companiesDestroy();
        break;

    // Reports
    case 'reports/stock':
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat laporan stok per PT.', 'danger');
        }
        (new ReportController())->stock();
        break;
    case 'reports/stock-export-excel':
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh laporan stok per PT.', 'danger');
        }
        (new ReportController())->stockExportExcel();
        break;
    case 'reports/movements':
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat mutasi stok.', 'danger');
        }
        (new ReportController())->movements();
        break;
    case 'reports/movements-export-excel':
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh mutasi stok.', 'danger');
        }
        (new ReportController())->movementsExportExcel();
        break;

    // Users
    case 'users':
        if (!canManageUsers()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengelola pengguna & hak akses.', 'danger');
        }
        (new UserController())->index();
        break;
    case 'users/store':
        if (!canManageUsers()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengelola pengguna & hak akses.', 'danger');
        }
        (new UserController())->store();
        break;

    default:
        http_response_code(404);
        echo '<h1>404 - Halaman Tidak Ditemukan</h1><p><a href="' . url('dashboard') . '">Kembali ke Dashboard</a></p>';
        break;
}
