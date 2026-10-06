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
        $perPageParam = $_GET['per_page'] ?? '25';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $params = [
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'stock_status' => $_GET['stock_status'] ?? '',
        ];

        if ($perPageParam === 'all' || (int)$perPageParam === -1) {
            $params['all'] = 1;
        } else {
            $perPage = in_array((int)$perPageParam, [10, 25, 50, 100]) ? (int)$perPageParam : 25;
            $params['per_page'] = $perPage;
            $params['page'] = $page;
        }

        $res = $this->api->get('items', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $items = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];
        $categories = $this->api->get('categories')['data'] ?? [];
        $units = $this->api->get('units')['data'] ?? [];

        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            header('Content-Type: application/json; charset=utf-8');
            ob_start();
            if (empty($items)) {
                ?>
                <tr id="emptyDbRow"><td colspan="7" class="text-center text-muted" style="padding: 2.5rem;">
                  <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin: 0 auto 0.5rem; color: #94a3b8;"></i>
                  Tidak ada barang yang cocok dengan kata kunci pencarian atau filter yang dipilih.
                </td></tr>
                <?php
            } else {
                foreach ($items as $item) {
                    $itemJson = htmlspecialchars(json_encode([
                        'id' => $item['id'],
                        'item_code' => $item['item_code'],
                        'name' => $item['name'],
                        'company_code' => $item['company']['code'] ?? 'N/A',
                        'total_stock' => $item['total_stock'],
                        'unit' => $item['unit']['code'] ?? '',
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'category_id' => $item['category_id'] ?? ($item['category']['id'] ?? ''),
                        'unit_id' => $item['unit_id'] ?? ($item['unit']['id'] ?? ''),
                        'minimum_stock' => $item['minimum_stock'] ?? 0,
                        'po_number' => $item['po_number'] ?? '',
                        'specification' => $item['specification'] ?? '',
                        'description' => $item['description'] ?? '',
                    ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr class="item-row">
                      <td>
                        <span style="font-family: monospace; font-weight: 600; color: #1e40af;">
                          <?= htmlspecialchars($item['item_code']) ?>
                        </span>
                      </td>
                      <td>
                        <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="fw-bold">
                          <?= htmlspecialchars($item['name']) ?>
                        </a>
                      </td>
                      <td style="font-family: monospace;">
                        <?php if ((float)($item['purchase_price'] ?? 0) > 0): ?>
                          <span class="fw-bold" style="color: #0284c7; font-size: 0.88rem;">
                            <?= formatRupiah($item['purchase_price']) ?>
                          </span>
                        <?php else: ?>
                          <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">-</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?= htmlspecialchars($item['category']['name'] ?? '-') ?>
                      </td>
                      <td class="fw-bold" style="font-size: 1rem;">
                        <?= formatQty($item['total_stock'], $item['unit']['code'] ?? '') ?>
                      </td>
                      <td><?= renderBadge($item['stock_status']) ?></td>
                      <td class="text-center" style="white-space: nowrap;">
                        <div class="action-buttons">
                          <?php if (isPurchasing()): ?>
                            <button type="button" class="btn-action-price btn-input-price" onclick="openPurchasingModal(<?= $itemJson ?>)" title="Input / Edit No. PO & Harga">
                              <i class="bi bi-tag me-1"></i>No. PO & Harga
                            </button>
                          <?php endif; ?>
                          <?php if (canEditItem()): ?>
                            <button type="button" class="btn-action-edit btn-edit-item" data-item="<?= $itemJson ?>" onclick="openEditItemModal(this)" title="Edit Isi Barang">
                              <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                          <?php endif; ?>
                          <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="btn-action-view" title="Lihat Detail Barang">
                            <i class="bi bi-eye me-1"></i>Detail
                          </a>
                        </div>
                      </td>
                    </tr>
                    <?php
                }
            }
            $rowsHtml = ob_get_clean();

            $cur = (int) ($meta['current_page'] ?? 1);
            $last = (int) ($meta['last_page'] ?? 1);
            $total = (int) ($meta['total'] ?? count($items));
            $perPageVal = $perPageParam;
            $perPageNum = (int) ($meta['per_page'] ?? ($perPageVal !== 'all' ? (int)$perPageVal : $total));
            $from = ($total > 0 && $perPageVal !== 'all') ? (($cur - 1) * $perPageNum + 1) : ($total > 0 ? 1 : 0);
            $to = ($perPageVal !== 'all') ? min($total, $cur * $perPageNum) : $total;

            ob_start();
            if ($perPageVal === 'all') {
                echo 'Menampilkan seluruh <strong>' . number_format($total, 0, ',', '.') . '</strong> data barang.';
            } else {
                echo 'Menampilkan baris <strong>' . number_format($from, 0, ',', '.') . '</strong> - <strong>' . number_format($to, 0, ',', '.') . '</strong> dari total <strong>' . number_format($total, 0, ',', '.') . '</strong> data barang (Halaman <strong>' . $cur . '</strong> dari <strong>' . $last . '</strong>)';
            }
            $summaryHtml = ob_get_clean();

            ob_start();
            if ($last > 1 && $perPageVal !== 'all') {
                ?>
                <div class="pagination" style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                  <?php if ($cur > 1): ?>
                    <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="1" style="padding: 4px 8px;" title="Halaman Pertama">&laquo;</button>
                    <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $cur - 1 ?>" style="padding: 4px 8px;" title="Sebelumnya">&lsaquo;</button>
                  <?php endif; ?>

                  <?php
                    $startPage = max(1, $cur - 2);
                    $endPage = min($last, $cur + 2);
                    if ($startPage > 1) {
                      echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="1" style="padding: 4px 10px;">1</button>';
                      if ($startPage > 2) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
                    }
                    for ($p = $startPage; $p <= $endPage; $p++) {
                      $activeStyle = ($p == $cur) ? 'background-color: var(--primary); color: #fff; border-color: var(--primary); font-weight: bold;' : '';
                      echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="' . $p . '" style="padding: 4px 10px; ' . $activeStyle . '">' . $p . '</button>';
                    }
                    if ($endPage < $last) {
                      if ($endPage < $last - 1) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
                      echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="' . $last . '" style="padding: 4px 10px;">' . $last . '</button>';
                    }
                  ?>

                  <?php if ($cur < $last): ?>
                    <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $cur + 1 ?>" style="padding: 4px 8px;" title="Selanjutnya">&rsaquo;</button>
                    <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $last ?>" style="padding: 4px 8px;" title="Halaman Terakhir">&raquo;</button>
                  <?php endif; ?>
                </div>
                <?php
            }
            $paginationHtml = ob_get_clean();

            echo json_encode([
                'success' => true,
                'rows_html' => $rowsHtml,
                'summary_html' => $summaryHtml,
                'pagination_html' => $paginationHtml,
                'total' => $total,
                'current_page' => $cur,
                'last_page' => $last,
            ]);
            exit;
        }

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
        $categories = $this->api->get('categories')['data'] ?? [];
        $units = $this->api->get('units')['data'] ?? [];

        include __DIR__ . '/../views/items/show.php';
    }

    public function updatePurchasing(): void
    {
        if (!isPurchasing()) {
            redirect('items', 'Akses ditolak: Hanya role Purchasing yang berhak menginput atau mengubah data PO dan harga barang.', 'danger');
        }

        $id = (int) ($_POST['item_id'] ?? 0);
        if (!$id) {
            redirect('items');
        }

        $purchasePrice = isset($_POST['purchase_price']) 
            ? (float) str_replace(',', '.', trim((string) $_POST['purchase_price'])) 
            : 0.0;
        $poNumber = trim($_POST['po_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $payload = [
            'purchase_price' => max(0, $purchasePrice),
            'po_number' => $poNumber ?: null,
            'notes' => $notes ?: null,
        ];

        $res = $this->api->put("items/{$id}/purchasing", $payload);

        $returnUrl = !empty($_POST['return_to']) && $_POST['return_to'] === 'show' 
            ? "items/show&id={$id}" 
            : 'items';

        if (!empty($res['success'])) {
            redirect($returnUrl, 'Data purchasing (No. PO & Harga) barang [' . ($res['data']['item_code'] ?? '') . '] berhasil diperbarui tanpa menggeser antrean FIFO.');
        } else {
            $msg = $res['message'] ?? 'Gagal memperbarui data purchasing barang.';
            redirect($returnUrl, $msg, 'danger');
        }
    }

    public function update(): void
    {
        if (!canEditItem()) {
            redirect('items', 'Akses ditolak: Anda tidak memiliki izin mengedit data barang.', 'danger');
        }

        $id = (int) ($_POST['item_id'] ?? 0);
        if (!$id) {
            redirect('items');
        }

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'unit_id' => !empty($_POST['unit_id']) ? (int)$_POST['unit_id'] : null,
            'minimum_stock' => (float) str_replace(',', '.', (string) ($_POST['minimum_stock'] ?? 0)),
            'specification' => trim($_POST['specification'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
        ];

        $res = $this->api->put("items/{$id}", $payload);

        $returnUrl = !empty($_POST['return_to']) && $_POST['return_to'] === 'show' 
            ? "items/show&id={$id}" 
            : 'items';

        if (!empty($res['success'])) {
            redirect($returnUrl, 'Data barang [' . ($res['data']['item_code'] ?? '') . '] berhasil diperbarui!');
        } else {
            $msg = $res['message'] ?? 'Gagal memperbarui data barang.';
            redirect($returnUrl, $msg, 'danger');
        }
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
