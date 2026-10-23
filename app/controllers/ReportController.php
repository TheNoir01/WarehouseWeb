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

        $perPageParam = $_GET['per_page'] ?? '25';
        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'movement_type' => $_GET['movement_type'] ?? '',
        ];

        if ($perPageParam === 'all') {
            $params['all'] = 1;
        } else {
            $perPage = (int) $perPageParam;
            if ($perPage <= 0) $perPage = 25;
            $params['per_page'] = $perPage;
        }

        $res = $this->api->get('stock/movements', array_filter($params, fn($v) => $v !== '' && $v !== null));
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

            $itemReceipts = $b['goods_receipts'] ?? $item['goods_receipts'] ?? [];
            $itemIssues = $b['goods_issues'] ?? $item['goods_issues'] ?? [];
            $priceHistories = $b['price_histories'] ?? $item['price_histories'] ?? [];
            $unit = strtoupper(trim((string)($item['unit']['code'] ?? $item['unit']['name'] ?? 'Pcs')));
            if ($unit === '' || $unit === '-') {
                $unit = 'Pcs';
            }

            // 1. Barang Masuk di bulan ini
            $monthlyReceipts = array_filter($itemReceipts, function ($r) use ($currentYm) {
                if (empty($r['received_date'])) return false;
                return str_starts_with($r['received_date'], $currentYm);
            });

            $qtyMasuk = 0;
            $receiptDays = [];
            $receiptSuppliers = [];
            $receiptPos = [];

            foreach ($monthlyReceipts as $r) {
                $qtyMasuk += (float) ($r['qty'] ?? 0);
                $dayNum = (int) date('j', strtotime($r['received_date']));
                if (!in_array($dayNum, $receiptDays, true)) {
                    $receiptDays[] = $dayNum;
                }
                if (!empty($r['supplier_name']) && !in_array($r['supplier_name'], $receiptSuppliers, true)) {
                    $receiptSuppliers[] = $r['supplier_name'];
                }
                if (!empty($r['po_number'])) {
                    $cleanPo = trim((string) $r['po_number']);
                    if ($cleanPo !== '' && !in_array($cleanPo, $receiptPos, true)) {
                        $receiptPos[] = $cleanPo;
                    }
                }
            }
            sort($receiptDays);
            $tglMasuk = !empty($receiptDays) ? implode(', ', $receiptDays) : '';

            // 2. Barang Keluar di bulan ini
            $monthlyIssues = array_filter($itemIssues, function ($iss) use ($currentYm) {
                if (empty($iss['issued_date'])) return false;
                return str_starts_with($iss['issued_date'], $currentYm);
            });

            $qtyKeluar = 0;
            $issueDays = [];
            foreach ($monthlyIssues as $iss) {
                $qtyKeluar += (float) ($iss['qty'] ?? 0);
                $dayNum = (int) date('j', strtotime($iss['issued_date']));
                if (!in_array($dayNum, $issueDays, true)) {
                    $issueDays[] = $dayNum;
                }
            }
            sort($issueDays);
            $tglKeluar = !empty($issueDays) ? implode(', ', $issueDays) : '';

            // 3. Stok Akhir dan Stok Awal
            $stokAkhir = $stock;
            $stokAwal = $stokAkhir - $qtyMasuk + $qtyKeluar;
            if ($stokAwal < 0) {
                $stokAwal = 0;
            }

            // 4. Harga Baru dan Harga Lama
            $currentPrice = (float) ($item['purchase_price'] ?? 0);
            if ($currentPrice <= 0 && !empty($itemReceipts)) {
                foreach (array_reverse($itemReceipts) as $rcp) {
                    if (!empty($rcp['unit_price']) && (float) $rcp['unit_price'] > 0) {
                        $currentPrice = (float) $rcp['unit_price'];
                        break;
                    }
                }
            }

            $hargaLamaVal = 0;
            $hargaBaruVal = 0;

            // Periksa riwayat perubahan harga
            $hasPriceChange = false;
            if (!empty($priceHistories)) {
                foreach ($priceHistories as $ph) {
                    $oldP = (float) ($ph['old_price'] ?? 0);
                    $newP = (float) ($ph['new_price'] ?? $ph['price'] ?? 0);
                    if ($oldP > 0 && $newP > 0 && $oldP != $newP) {
                        $hargaLamaVal = $oldP;
                        $hargaBaruVal = $newP;
                        $hasPriceChange = true;
                        break;
                    }
                }
            }

            // Jika tidak ada perubahan harga:
            // Maka harga yang ada masuk ke kolom HARGA LAMA (tidak di kolom HARGA BARU)
            if (!$hasPriceChange) {
                if ($currentPrice > 0) {
                    $hargaLamaVal = $currentPrice;
                } elseif (!empty($priceHistories)) {
                    $firstP = (float) ($priceHistories[0]['new_price'] ?? $priceHistories[0]['price'] ?? $priceHistories[0]['old_price'] ?? 0);
                    if ($firstP > 0) {
                        $hargaLamaVal = $firstP;
                    }
                }
                $hargaBaruVal = 0;
            }

            $hargaLama = ($hargaLamaVal > 0) ? 'Rp. ' . number_format($hargaLamaVal, 0, ',', '.') : '';
            $hargaBaru = ($hargaBaruVal > 0) ? 'Rp. ' . number_format($hargaBaruVal, 0, ',', '.') : '';

            // 5. Supplier
            if (empty($supplier) && !empty($receiptSuppliers)) {
                $supplier = implode(', ', $receiptSuppliers);
            }
            if (empty($supplier) && !empty($itemReceipts)) {
                foreach ($itemReceipts as $r) {
                    if (!empty($r['supplier_name']) && !in_array($r['supplier_name'], $receiptSuppliers, true)) {
                        $receiptSuppliers[] = $r['supplier_name'];
                    }
                }
                if (!empty($receiptSuppliers)) {
                    $supplier = implode(', ', $receiptSuppliers);
                }
            }

            // 6. NO PO
            $poNumber = '';
            if (!empty($receiptPos)) {
                $poNumber = implode(', ', $receiptPos);
            } elseif (!empty($item['po_number'])) {
                $poNumber = trim((string) $item['po_number']);
            }

            // 7. Kondisi Stock ('HABIS' / 'KURANG' / 'CUKUP')
            if ($stokAkhir <= 0) {
                $kondisiStock = 'HABIS';
                $statusMarker = 'habis';
            } elseif ($min > 0 && $stokAkhir <= $min) {
                $kondisiStock = 'KURANG';
                $statusMarker = 'kurang';
            } else {
                $kondisiStock = 'CUKUP';
                $statusMarker = 'cukup';
            }

            $grouped[$currentYm][$cCode][] = [
                'company' => $cCode,
                'item_code' => $item['item_code'] ?? '-',
                'category' => $item['category']['name'] ?? '-',
                'item_name' => $cleanName,
                'stok_awal' => $stokAwal,
                'supplier' => $supplier,
                'tgl_masuk' => $tglMasuk,
                'qty_masuk' => $qtyMasuk > 0 ? $qtyMasuk : '',
                'unit' => $unit,
                'po_number' => $poNumber,
                'harga_lama' => $hargaLama,
                'harga_baru' => $hargaBaru,
                'tgl_keluar' => $tglKeluar,
                'qty_keluar' => $qtyKeluar > 0 ? $qtyKeluar : '',
                'stok_akhir' => $stokAkhir,
                'kondisi_stock' => $kondisiStock,
                'status_marker' => $statusMarker,
                'keterangan' => $item['description'] ?? '',
            ];
        }

        $headers = [
            'NO',
            'KODE',
            'KATEGORI',
            'NAMA BARANG',
            'STOK AWAL',
            'SUPPLIER',
            'TANGGAL BARANG MASUK',
            'BARANG MASUK',
            'SATUAN',
            'NO PO',
            'HARGA LAMA',
            'HARGA BARU',
            'TANGGAL BARANG KELUAR',
            'BARANG KELUAR',
            'SATUAN',
            'STOK AKHIR',
            'KONDISI STOCK',
            'KETERANGAN',
        ];

        $colWidths = [6, 14, 16, 48, 12, 24, 24, 16, 10.5, 24, 16, 16, 24.5, 16, 10.5, 14, 16, 28];

        $monthNamesFull = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        $writer = new \App\Services\SimpleXlsxWriter();

        foreach ($grouped as $ym => $companiesData) {
            $parts = explode('-', $ym);
            $year = $parts[0] ?? date('Y');
            $mNum = isset($parts[1]) ? (int) $parts[1] : (int) date('m');
            $mLabel = ($monthNames[$mNum] ?? 'Bln') . ' ' . $year;
            $fullMonth = $monthNamesFull[$mNum] ?? strtoupper(date('F'));

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
                        ['v' => 0, 'align' => 'center'],
                        '-',
                        '',
                        '',
                        ['v' => '-', 'align' => 'center'],
                        '',
                        '',
                        '',
                        '',
                        '',
                        ['v' => '-', 'align' => 'center'],
                        ['v' => 0, 'align' => 'center'],
                        ['v' => 'HABIS', 'align' => 'center', 'status' => 'habis'],
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            ['v' => $no++, 'align' => 'center'],
                            ['v' => $row['item_code'], 'align' => 'center'],
                            ['v' => $row['category'], 'align' => 'center'],
                            ['v' => $row['item_name'], 'align' => 'left'],
                            ['v' => $row['stok_awal'], 'align' => 'center'],
                            ['v' => $row['supplier'], 'align' => 'center'],
                            ['v' => $row['tgl_masuk'], 'align' => 'center'],
                            ['v' => $row['qty_masuk'], 'align' => 'center'],
                            ['v' => $row['unit'], 'align' => 'center'],
                            ['v' => $row['po_number'], 'align' => 'center'],
                            ['v' => $row['harga_lama'], 'align' => 'center'],
                            ['v' => $row['harga_baru'], 'align' => 'center'],
                            ['v' => $row['tgl_keluar'], 'align' => 'center'],
                            ['v' => $row['qty_keluar'], 'align' => 'center'],
                            ['v' => $row['unit'], 'align' => 'center'],
                            ['v' => $row['stok_akhir'], 'align' => 'center'],
                            [
                                'v' => $row['kondisi_stock'],
                                'align' => 'center',
                                'status' => $row['status_marker'] ?? ($row['kondisi_stock'] === 'CUKUP' ? 'cukup' : ($row['kondisi_stock'] === 'HABIS' ? 'habis' : 'kurang')),
                            ],
                            ['v' => $row['keterangan'], 'align' => 'left'],
                        ];
                    }
                }

                $options = [
                    'theme' => 'warehouse',
                    'banner1' => "LAPORAN STOCK BARANG BULAN {$fullMonth} {$year}",
                    'banner2' => "ITEM NAME {$cCode}",
                    'banner2_color' => ($cCode === 'LNP' ? 'blue' : 'red'),
                ];

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths, $options);
            }
        }

        $filename = 'Laporan_Saldo_stok_' . date('d-m-Y') . '.xlsx';
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
                'time' => formatDateTime($rawDate),
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

        $filename = 'Laporan_Mutasi_Stok_' . date('d-m-Y') . '.xlsx';
        $writer->download($filename);
    }
}
