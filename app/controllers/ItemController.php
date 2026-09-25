<?php

namespace App\Controllers;

use App\Services\ApiService;

class ItemController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index(): void
    {
        $params = [
            'all' => 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'stock_status' => $_GET['stock_status'] ?? '',
        ];

        $res = $this->api->get('items', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $items = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];
        $categories = $this->api->get('categories')['data'] ?? [];

        include __DIR__ . '/../views/items/index.php';
    }

    public function create(): void
    {
        if (!canManageMaster()) {
            redirect('items', 'Anda tidak memiliki izin menambah master barang.', 'danger');
        }

        $companies = $this->api->get('companies')['data'] ?? [];
        $categories = $this->api->get('categories')['data'] ?? [];
        $types = $this->api->get('types')['data'] ?? [];
        $units = $this->api->get('units')['data'] ?? [];

        $presetCompanyCode = strtoupper(trim($_GET['company'] ?? ''));
        $presetName = trim($_GET['name'] ?? '');
        $from = trim($_GET['from'] ?? '');

        include __DIR__ . '/../views/items/create.php';
    }

    public function store(): void
    {
        if (!canManageMaster()) {
            redirect('items', 'Akses ditolak.', 'danger');
        }

        $payload = [
            'company_id' => $_POST['company_id'] ?? null,
            'name' => trim($_POST['name'] ?? ''),
            'category_id' => $_POST['category_id'] ?: null,
            'category_name' => trim($_POST['category_name'] ?? '') ?: null,
            'type_id' => $_POST['type_id'] ?: null,
            'type_name' => trim($_POST['type_name'] ?? '') ?: null,
            'unit_id' => $_POST['unit_id'] ?: null,
            'unit_code' => trim($_POST['unit_code'] ?? '') ?: null,
            'barcode' => trim($_POST['barcode'] ?? '') ?: null,
            'minimum_stock' => (float) str_replace(',', '.', (string) ($_POST['minimum_stock'] ?? 0)),
            'specification' => trim($_POST['specification'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'generate_qr' => !empty($_POST['generate_qr']),
        ];

        $res = $this->api->post('items', $payload);

        $returnTo = trim($_POST['return_to'] ?? '');
        $companyCode = strtoupper(trim($_POST['company_code'] ?? 'KJG'));

        if (!empty($res['success'])) {
            if ($returnTo === 'receipts') {
                $newItemId = $res['data']['id'] ?? '';
                redirect("receipts/create&company={$companyCode}&new_item_id={$newItemId}", 'Barang baru [' . ($res['data']['item_code'] ?? '') . ' - ' . ($res['data']['name'] ?? '') . '] berhasil didaftarkan dan langsung disiapkan di form penerimaan!');
            }
            redirect('items', 'Barang baru [' . $res['data']['item_code'] . '] berhasil didaftarkan.');
        } else {
            $msg = $res['message'] ?? 'Gagal mendaftarkan barang.';
            if ($returnTo === 'receipts') {
                redirect("items/create&company={$companyCode}&from=receipts", $msg, 'danger');
            }
            redirect('items/create', $msg, 'danger');
        }
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('items');
        }

        $res = $this->api->get("items/{$id}");
        if (empty($res['success'])) {
            redirect('items', 'Barang tidak ditemukan.', 'danger');
        }

        $item = $res['data'];
        include __DIR__ . '/../views/items/show.php';
    }

    public function checkDuplicateAjax(): void
    {
        header('Content-Type: application/json');
        $name = $_GET['name'] ?? '';
        $res = $this->api->get('items/check-duplicate', ['name' => $name]);
        echo json_encode($res['data'] ?? []);
        exit;
    }

    public function ajaxStore(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $res = $this->api->post('items', $input);
        echo json_encode($res);
        exit;
    }

    public function searchAjax(): void
    {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        $companyId = !empty($_GET['company_id']) ? (int) $_GET['company_id'] : null;

        if ($q === '') {
            echo json_encode(['success' => true, 'data' => [], 'total' => 0]);
            exit;
        }

        $params = [
            'search' => $q,
        ];
        if (!empty($_GET['merge_by_name'])) {
            $params['all'] = 1;
        } else {
            $params['per_page'] = 100;
        }
        if ($companyId) {
            $params['company_id'] = $companyId;
        }

        $res = $this->api->get('items', $params);
        $rawItems = $res['data'] ?? [];

        // If merge_by_name is requested (e.g. for outgoing issues where identical items from different PTs are merged)
        if (!empty($_GET['merge_by_name'])) {
            $merged = [];
            foreach ($rawItems as $it) {
                $normName = strtolower(trim($it['name'] ?? ''));
                $stock = (float) ($it['total_stock'] ?? 0);
                if (!isset($merged[$normName])) {
                    $merged[$normName] = [
                        'id' => $it['id'],
                        'item_code' => $it['item_code'] ?? '',
                        'name' => $it['name'] ?? '',
                        'specification' => $it['specification'] ?? '',
                        'category' => $it['category'] ?? null,
                        'unit' => $it['unit'] ?? null,
                        'total_stock' => $stock,
                        'stock_status' => ($stock <= 0) ? 'HABIS' : 'TERSEDIA',
                    ];
                } else {
                    $merged[$normName]['total_stock'] += $stock;
                    if ($merged[$normName]['total_stock'] > 0) {
                        $merged[$normName]['stock_status'] = 'TERSEDIA';
                    }
                    if (empty($merged[$normName]['specification']) && !empty($it['specification'])) {
                        $merged[$normName]['specification'] = $it['specification'];
                    }
                }
            }
            $rawItems = array_values($merged);
        }

        echo json_encode([
            'success' => true,
            'data' => $rawItems,
            'total' => count($rawItems),
        ]);
        exit;
    }
}
