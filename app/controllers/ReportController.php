<?php

namespace App\Controllers;

use App\Services\ApiService;

class ReportController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function stock(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat laporan stok per PT.', 'danger');
        }

        $perPageParam = $_GET['per_page'] ?? '25';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $params = [
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
        ];

        if ($perPageParam === 'all' || (int)$perPageParam === -1) {
            $params['all'] = 1;
        } else {
            $perPage = in_array((int)$perPageParam, [10, 25, 50, 100]) ? (int)$perPageParam : 25;
            $params['per_page'] = $perPage;
            $params['page'] = $page;
        }

        $res = $this->api->get('stock', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $balances = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            header('Content-Type: application/json; charset=utf-8');
            ob_start();
            if (empty($balances)) {
                ?>
                <tr id="emptyDbRow"><td colspan="8" class="text-center text-muted" style="padding: 2.5rem;">
                  <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin: 0 auto 0.5rem; color: #94a3b8;"></i>
                  Tidak ada data stok yang cocok dengan kata kunci pencarian atau filter yang dipilih.
                </td></tr>
                <?php
            } else {
                foreach ($balances as $b) {
                    $item = $b['item'] ?? [];
                    $stock = (float) ($b['qty'] ?? 0);
                    $min = (float) ($item['category']['minimum_stock'] ?? $item['category_minimum_stock'] ?? $item['minimum_stock'] ?? 0);
                    $stockStatus = $b['stock_status'] ?? ($stock <= 0 ? 'HABIS' : (($min > 0 && $stock <= $min) ? 'MENIPIS' : 'TERSEDIA'));
                    ?>
                    <tr>
                      <td><span class="badge badge-primary"><?= htmlspecialchars($b['company']['code'] ?? 'N/A') ?></span></td>
                      <td>
                        <span style="font-family: monospace; font-weight: 600; color: #1e40af;">
                          <?= htmlspecialchars($item['item_code'] ?? '-') ?>
                        </span>
                      </td>
                      <td>
                        <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?? '' ?>" class="fw-bold" style="color: #1e40af; text-decoration: none;">
                          <?= htmlspecialchars($item['name'] ?? '-') ?>
                        </a>
                        <?php if (!empty($item['specification'])): ?>
                          <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($item['specification']) ?></div>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($item['category']['name'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($item['unit']['code'] ?? '-') ?></td>
                      <td class="fw-bold" style="font-size: 1rem;">
                        <?= formatQty($stock, $item['unit']['code'] ?? '') ?>
                      </td>
                      <td><?= renderBadge($stockStatus) ?></td>
                      <td class="text-muted" style="font-size: 0.85rem;"><?= formatDateTime($b['last_movement_at'] ?? $b['updated_at'] ?? null) ?></td>
                    </tr>
                    <?php
                }
            }
            $rowsHtml = ob_get_clean();

            $cur = (int) ($meta['current_page'] ?? 1);
            $last = (int) ($meta['last_page'] ?? 1);
            $total = (int) ($meta['total'] ?? count($balances));
            $perPageVal = $perPageParam;
            $perPageNum = (int) ($meta['per_page'] ?? ($perPageVal !== 'all' ? (int)$perPageVal : $total));
            $from = ($total > 0 && $perPageVal !== 'all') ? (($cur - 1) * $perPageNum + 1) : ($total > 0 ? 1 : 0);
            $to = ($perPageVal !== 'all') ? min($total, $cur * $perPageNum) : $total;

            ob_start();
            if ($perPageVal === 'all') {
                echo 'Menampilkan seluruh <strong>' . number_format($total, 0, ',', '.') . '</strong> data stok barang.';
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

        include __DIR__ . '/../views/reports/stock.php';
    }

    public function movements(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat mutasi stok.', 'danger');
        }

        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'movement_type' => $_GET['movement_type'] ?? '',
        ];

        $res = $this->api->get('stock/movements', array_filter($params));
        $movements = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/reports/movements.php';
    }

    public function stockExportExcel(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh laporan stok per PT.', 'danger');
        }

        $params = [
            'all' => 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
        ];

        $res = $this->api->get('stock', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $balances = $res['data'] ?? [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        $currentYm = date('Y-m');
        $grouped = [
            $currentYm => [
                'KJG' => [],
                'LNP' => [],
            ]
        ];

        foreach ($balances as $b) {
            $rawDate = (string) ($b['updated_at'] ?? $b['created_at'] ?? date('Y-m-d'));
            $cCode = strtoupper($b['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $b['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$currentYm][$cCode])) {
                $grouped[$currentYm][$cCode] = [];
            }

            $item = $b['item'] ?? [];
            $stock = (float) ($b['qty'] ?? 0);
            $min = (float) ($item['category']['minimum_stock'] ?? $item['category_minimum_stock'] ?? 0);
            if ($min <= 0 && (float)($item['minimum_stock'] ?? 0) > 0) {
                $min = (float) $item['minimum_stock'];
            }
            $status = ($stock <= 0) ? 'HABIS' : (($min > 0 && $stock <= $min) ? 'MENIPIS' : 'TERSEDIA');

            $cleanName = trim((string)($item['name'] ?? '-'));
            $cleanName = trim(preg_replace('/\s*[-–]\s*Spesifikasi.*/i', '', $cleanName));

            $supplier = trim((string)($item['suppliers_summary'] ?? ''));
            if ($supplier === '-' || $supplier === '') {
                $supplier = '';
                $spec = (string)($item['specification'] ?? '');
                $desc = (string)($item['description'] ?? '');
                if (preg_match('/Supplier\s*:\s*([^|\n]+)/i', $spec, $m)) {
                    $supplier = trim($m[1]);
                } elseif (preg_match('/Supplier\s*:\s*([^|\n]+)/i', $desc, $m)) {
                    $supplier = trim($m[1]);
                }
            }

            if (preg_match('/\s*[-–(]\s*Supplier\s*:\s*([^)]+)\)?/i', $cleanName, $m)) {
                if (empty($supplier)) {
                    $supplier = trim($m[1]);
                }
                $cleanName = trim(preg_replace('/\s*[-–(]\s*Supplier\s*:\s*[^)]+\)?/i', '', $cleanName));
            }

            $price = (float)($item['purchase_price'] ?? 0);
            $priceVal = ($price > 0) ? $price : '';

            $poNumber = trim((string)($item['po_number'] ?? ''));

            // Process goods receipts for this item in the current month
            $itemReceipts = $b['goods_receipts'] ?? $item['goods_receipts'] ?? [];
            $unit = strtoupper(trim((string)($item['unit']['code'] ?? $item['unit']['name'] ?? 'PCS')));
            if ($unit === '' || $unit === '-') {
                $unit = 'PCS';
            }

            $monthlyReceipts = array_filter($itemReceipts, function ($r) use ($currentYm) {
                if (empty($r['received_date'])) return false;
                return str_starts_with($r['received_date'], $currentYm);
            });

            $receiptsByDay = [];
            $receiptSuppliers = [];
            $receiptPos = [];

            foreach ($monthlyReceipts as $r) {
                $dayNum = (int) date('j', strtotime($r['received_date']));
                if (!isset($receiptsByDay[$dayNum])) {
                    $receiptsByDay[$dayNum] = [
                        'day' => $dayNum,
                        'qty' => 0,
                        'count' => 0,
                    ];
                }
                $receiptsByDay[$dayNum]['qty'] += (float) ($r['qty'] ?? 0);
                $receiptsByDay[$dayNum]['count']++;

                if (!empty($r['supplier_name']) && !in_array($r['supplier_name'], $receiptSuppliers, true)) {
                    $receiptSuppliers[] = $r['supplier_name'];
                }
                if (!empty($r['po_number']) && !in_array($r['po_number'], $receiptPos, true)) {
                    $receiptPos[] = $r['po_number'];
                }
            }
            ksort($receiptsByDay);

            if (empty($supplier) && !empty($receiptSuppliers)) {
                $supplier = implode(', ', $receiptSuppliers);
            }
            if (empty($poNumber) && !empty($receiptPos)) {
                $poNumber = implode(', ', $receiptPos);
            }

            $tanggalMasuk = '';
            $keterangan = '-';

            if (count($receiptsByDay) > 1) {
                // Multi-date goods receipts in the same month:
                // Tanggal format: tanggalnya saja (e.g. "5, 20")
                $tanggalMasuk = implode(', ', array_keys($receiptsByDay));

                // Keterangan: rincian barang masuk per tanggal
                $parts = [];
                foreach ($receiptsByDay as $d => $info) {
                    $qFormatted = rtrim(rtrim(number_format($info['qty'], 2, '.', ''), '0'), '.');
                    $parts[] = "tgl {$d} ({$qFormatted} {$unit})";
                }
                $keterangan = 'Masuk ' . implode(', ', $parts);
            } elseif (count($receiptsByDay) === 1) {
                // Exactly 1 receipt date in this month: format tanggalnya saja
                $dayNum = key($receiptsByDay);
                $tanggalMasuk = (int) $dayNum;
                $keterangan = '-';
            } else {
                // No new goods receipt in this month, use initial/updated date day
                $dayNum = (int) date('j', strtotime($rawDate));
                $tanggalMasuk = ($dayNum > 0) ? (int) $dayNum : '-';
                $keterangan = '-';
            }

            $grouped[$currentYm][$cCode][] = [
                'company' => $cCode,
                'item_code' => $item['item_code'] ?? '-',
                'category' => $item['category']['name'] ?? '-',
                'item_name' => $cleanName,
                'unit' => $item['unit']['code'] ?? $item['unit']['name'] ?? '-',
                'supplier' => $supplier,
                'price' => $priceVal,
                'po_number' => $poNumber,
                'qty' => $stock,
                'status' => $status,
                'tanggal_masuk' => $tanggalMasuk,
                'keterangan' => $keterangan,
            ];
        }

        $headers = [
            'No',
            'ID / Kode Barang',
            'Kategori',
            'Nama Barang',
            'Satuan',
            'suppler',
            'harga',
            'no po',
            'Total Stok',
            'Tanggal Masuk',
            'Keterangan',
        ];

        $colWidths = [6, 18, 18, 45, 10, 22, 16, 20, 14, 16, 35];

        $writer = new \App\Services\SimpleXlsxWriter();

        foreach ($grouped as $ym => $companiesData) {
            $parts = explode('-', $ym);
            $year = $parts[0] ?? date('Y');
            $mNum = isset($parts[1]) ? (int) $parts[1] : (int) date('m');
            $mLabel = ($monthNames[$mNum] ?? 'Bln') . ' ' . $year;

            foreach ($companiesData as $cCode => $rowsData) {
                // Natural ascending sort by item_code (e.g. KJG1, KJG2, ... / LNP1, LNP2, ...)
                usort($rowsData, function ($a, $b) {
                    return strnatcasecmp($a['item_code'] ?? '', $b['item_code'] ?? '');
                });

                $sheetTitle = "{$cCode} - {$mLabel}";
                $sheetRows = [];
                $no = 1;

                if (empty($rowsData)) {
                    $sheetRows[] = [
                        ['v' => 1, 'align' => 'center'],
                        '-',
                        '-',
                        'Tidak ada data saldo stok pada periode ini',
                        ['v' => '-', 'align' => 'center'],
                        '',
                        '',
                        '',
                        ['v' => 0, 'status' => 'HABIS'],
                        ['v' => '-', 'align' => 'center'],
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            ['v' => $no++, 'align' => 'center'],
                            $row['item_code'],
                            $row['category'],
                            $row['item_name'],
                            ['v' => $row['unit'], 'align' => 'center'],
                            $row['supplier'],
                            $row['price'],
                            $row['po_number'],
                            [
                                'v' => $row['qty'],
                                'status' => $row['status'],
                            ],
                            ['v' => $row['tanggal_masuk'], 'align' => 'center'],
                            ['v' => $row['keterangan']],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Saldo_Stok_' . date('Ymd_His') . '.xlsx';
        $writer->download($filename);
    }

    public function movementsExportExcel(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh mutasi stok.', 'danger');
        }

        $params = [
            'all' => 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'movement_type' => $_GET['movement_type'] ?? '',
        ];

        $res = $this->api->get('stock/movements', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $movements = $res['data'] ?? [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        $grouped = [];
        $monthsFound = [];
        foreach ($movements as $m) {
            $rawDate = (string) ($m['created_at'] ?? date('Y-m-d'));
            $ym = substr($rawDate, 0, 7);
            if ($ym) {
                $monthsFound[$ym] = true;
            }
        }
        if (empty($monthsFound)) {
            $monthsFound[date('Y-m')] = true;
        }

        ksort($monthsFound);

        foreach (array_keys($monthsFound) as $ym) {
            foreach ($allCompanies as $cCode => $cName) {
                $grouped[$ym][$cCode] = [];
            }
        }

        foreach ($movements as $m) {
            $rawDate = (string) ($m['created_at'] ?? date('Y-m-d'));
            $date = substr($rawDate, 0, 10);
            $ym = substr($date, 0, 7);
            $cCode = strtoupper($m['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $m['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$ym][$cCode])) {
                $grouped[$ym][$cCode] = [];
            }

            $item = $m['item'] ?? [];
            $grouped[$ym][$cCode][] = [
                'time' => substr($rawDate, 0, 19),
                'company' => $cCode,
                'type' => $m['movement_type'] ?? '-',
                'ref' => $m['reference_number'] ?? '-',
                'item_code' => $item['item_code'] ?? '-',
                'item_name' => $item['name'] ?? '-',
                'qty_change' => (float) ($m['qty'] ?? 0),
                'balance_after' => (float) ($m['balance_after'] ?? 0),
                'unit' => $item['unit']['code'] ?? $item['unit']['name'] ?? '-',
                'operator' => $m['user']['name'] ?? '-',
                'notes' => $m['notes'] ?? '-',
            ];
        }

        $headers = [
            'No',
            'Waktu Transaksi',
            'PT Pemilik',
            'Jenis Mutasi',
            'No. Referensi',
            'Kode Barang',
            'Nama Barang',
            'Perubahan Qty',
            'Saldo Akhir',
            'Satuan',
            'Operator',
            'Catatan',
        ];

        $colWidths = [6, 20, 14, 16, 22, 16, 32, 16, 14, 12, 18, 25];

        $writer = new \App\Services\SimpleXlsxWriter();

        foreach ($grouped as $ym => $companiesData) {
            $parts = explode('-', $ym);
            $year = $parts[0] ?? date('Y');
            $mNum = isset($parts[1]) ? (int) $parts[1] : (int) date('m');
            $mLabel = ($monthNames[$mNum] ?? 'Bln') . ' ' . $year;

            foreach ($companiesData as $cCode => $rowsData) {
                $sheetTitle = "{$cCode} - {$mLabel}";
                $sheetRows = [];
                $no = 1;

                if (empty($rowsData)) {
                    $sheetRows[] = [
                        1,
                        '-',
                        $cCode,
                        '-',
                        '-',
                        '-',
                        'Tidak ada mutasi stok pada periode ini',
                        0,
                        0,
                        '-',
                        '-',
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            $no++,
                            $row['time'],
                            $row['company'],
                            $row['type'],
                            $row['ref'],
                            $row['item_code'],
                            $row['item_name'],
                            $row['qty_change'],
                            $row['balance_after'],
                            $row['unit'],
                            $row['operator'],
                            $row['notes'],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Mutasi_Stok_' . date('Ymd_His') . '.xlsx';
        $writer->download($filename);
    }
}
