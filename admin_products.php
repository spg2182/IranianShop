<?php
if (!defined('BASE_PATH')) {
    include('./includes/init.php');
}
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// include کردن فایل توابع که شامل تابع require_admin است
include_once('./includes/functions.php');

// بررسی دسترسی مدیر (هم ورود کاربر و هم سطح دسترسی را بررسی می‌کند)
// بررسی دسترسی مدیر
require_admin();

$title = "مدیریت کالا";
include('./includes/header.php');

// اتصال به پایگاه داده با PDO
try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("اتصال به پایگاه داده ناموفق: " . $e->getMessage());
}

// توابع کمکی
function buildFilterQuery($search, $min_price, $max_price, $in_stock) {
    $whereClauses = [];
    $params = [];
    
    if ($search !== '') {
        $whereClauses[] = "(product_code LIKE :search OR name LIKE :search OR details LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($min_price !== null) {
        $whereClauses[] = "price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price !== null) {
        $whereClauses[] = "price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if ($in_stock !== null) {
        if ($in_stock == '1') {
            $whereClauses[] = "qty > 0";
        } elseif ($in_stock == '2') {
            $whereClauses[] = "qty = 0";
        }
    }
    
    $whereSQL = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);
    return [$whereSQL, $params];
}

function buildUrl($overrides = []) {
    $qs = array_merge($_GET, $overrides);
    unset($qs['csrf_token']); // حذف توکن از URL
    return '?' . http_build_query($qs);
}

// --- پارامترهای صفحه‌بندی، جستجو و فیلتر ---
$valid_limits = [10, 20, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $valid_limits) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// جستجو/فیلتر از کوئری استرینگ
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$in_stock = isset($_GET['in_stock']) ? $_GET['in_stock'] : null;

// ساخت بخش WHERE با پارامترهای امن
list($whereSQL, $params) = buildFilterQuery($search, $min_price, $max_price, $in_stock);

// خروجی CSV (Export)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    ob_clean(); // پاک کردن هرگونه خروجی قبلی
    
    // ساخت query با فیلترها (بدون LIMIT)
    $exportStmt = $conn->prepare("SELECT id, product_code, name, qty, price, image, details FROM products $whereSQL ORDER BY id DESC");
    foreach ($params as $k => $v) {
        $exportStmt->bindValue($k, $v);
    }
    $exportStmt->execute();
    
    // تنظیم هدرهای HTTP
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_export_' . date('Ymd_His') . '.csv');
    
    // باز کردن فایل برای نوشتن
    $out = fopen('php://output', 'w');
    // افزودن BOM برای اطمینان از شناسایی صحیح UTF-8 در Excel
    fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    // نوشتن سرستون‌ها
    fputcsv($out, ['شناسه', 'کد کالا', 'نام محصول', 'موجودی', 'قیمت', 'تصویر', 'توضیحات']);
    // نوشتن داده‌ها
    while ($r = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $r['id'],
            $r['product_code'],
            $r['name'],
            $r['qty'],
            $r['price'],
            $r['image'],
            $r['details']
        ]);
    }
    fclose($out);
    exit;
}

// محاسبه تعداد کل با فیلترها
$countStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM products $whereSQL");
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$totalItems = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
$totalPages = max(1, (int)ceil($totalItems / $limit));

// دریافت رکوردهای صفحه جاری با فیلترها
$dataStmt = $conn->prepare("SELECT id, product_code, name, qty, price, image, details FROM products $whereSQL ORDER BY id DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $dataStmt->bindValue($k, $v);
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">مدیریت محصولات</h2>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> اضافه کردن محصول
            </button>
            <a href="<?= buildUrl(['export'=>'csv']) ?>" class="btn btn-success ms-2">
                <i class="bi bi-file-earmark-excel"></i> خروجی اکسل (CSV)
            </a>
        </div>
        <form method="get" class="d-flex align-items-center" style="gap:8px;">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="min_price" value="<?= htmlspecialchars($min_price) ?>">
            <input type="hidden" name="max_price" value="<?= htmlspecialchars($max_price) ?>">
            <input type="hidden" name="in_stock" value="<?= $in_stock !== null ? htmlspecialchars($in_stock) : '' ?>">
            <label class="me-2">نمایش در هر صفحه:</label>
            <select name="limit" class="form-select" onchange="this.form.submit()" style="width:auto;">
                <?php foreach ($valid_limits as $l): ?>
                    <option value="<?= $l ?>" <?= $l === $limit ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <!-- فرم جستجو و فیلتر -->
    <form method="get" class="row g-3 mb-4 p-3 bg-light rounded">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="جستجو (کد، نام، توضیحات)" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control" placeholder="قیمت از (ریال)" value="<?= htmlspecialchars($min_price) ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control" placeholder="قیمت تا (ریال)" value="<?= htmlspecialchars($max_price) ?>">
        </div>
        <div class="col-md-2">
            <select name="in_stock" class="form-select">
                <option value="">همه</option>
                <option value="1" <?= $in_stock === '1' ? 'selected' : '' ?>>موجود</option>
                <option value="2" <?= $in_stock === '2' ? 'selected' : '' ?>>ناموجود/صفر</option>
            </select>
        </div>
        <div class="col-md-2 d-flex">
            <button type="submit" class="btn btn-primary me-2">
                <i class="bi bi-search"></i> اعمال فیلتر
            </button>
            <a href="admin_product.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> پاک کردن
            </a>
        </div>
    </form>
    
    <div class="container mt-2">
        <h6 class="text-center small">نمایش <strong><?= $totalItems ?></strong> محصول — صفحه <strong><?= $page ?></strong> از <strong><?= $totalPages ?></strong></h6>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ردیف</th>                
                    <th>شناسه</th>
                    <th>کد کالا</th>
                    <th>نام محصول</th>
                    <th>موجودی</th>
                    <th>قیمت (ریال)</th>
                    <th>تصویر</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rowNumberStart = $offset + 1;
                while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)):
                    $rowNumber = $rowNumberStart++;
                    $stockClass = $row['qty'] > 0 ? 'text-success' : 'text-danger';
                ?>
                    <tr>
                        <td><?= $rowNumber ?></td>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['product_code']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td class="<?= $stockClass ?> fw-bold"><?= htmlspecialchars($row['qty']) ?></td>
                        <td><?= number_format((int)$row['price'],0,'',',') ?> ریال</td>
                        <td>
                            <?php if (!empty($row['image'])): 
                                ?>
                                <img src="/assets/images/<?= htmlspecialchars($row['image']) ?>" 
                                     width="80" height="50" 
                                     alt="تصویر" 
                                     class="img-thumbnail cursor-pointer"
                                     data-bs-toggle="modal"
                                     data-bs-target="#imageModal"
                                     data-image="/assets/images/<?= htmlspecialchars($row['image']) ?>">
                            <?php else: ?>
                                <span class="text-muted">ندارد</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="action_admin_products.php?id=<?= urlencode($row['id']) ?>&action=DELETE&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('آیا از حذف این کالا مطمئن هستید؟')">
                                <i class="bi bi-trash"></i> حذف
                            </a>
                            <button class="btn btn-warning btn-sm btn-edit"
                                data-id="<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>"
                                data-product_code="<?= htmlspecialchars($row['product_code'], ENT_QUOTES) ?>"
                                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                                data-qty="<?= htmlspecialchars($row['qty'], ENT_QUOTES) ?>"
                                data-price="<?= htmlspecialchars($row['price'], ENT_QUOTES) ?>"
                                data-image="<?= htmlspecialchars($row['image'], ENT_QUOTES) ?>"
                                data-details="<?= htmlspecialchars($row['details'], ENT_QUOTES) ?>">
                                <i class="bi bi-pencil"></i> ویرایش
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <?php
        $buildPageUrl = function($p) {
            $qs = $_GET;
            $qs['page'] = $p;
            unset($qs['csrf_token']);
            return '?' . http_build_query($qs);
        };
        $prev = max(1, $page-1);
        $next = min($totalPages, $page+1);
        ?>
        <li class="page-item <?= $page<=1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $page<=1 ? '#' : $buildPageUrl($prev) ?>">قبلی</a>
        </li>
        <?php
        $start = max(1, $page-3);
        $end = min($totalPages, $page+3);
        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="'.$buildPageUrl(1).'">1</a></li>';
            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        for ($i=$start;$i<=$end;$i++) {
            echo '<li class="page-item '.($i===$page ? 'active' : '').'"><a class="page-link" href="'.$buildPageUrl($i).'">'.$i.'</a></li>';
        }
        if ($end < $totalPages) {
            if ($end < $totalPages-1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            echo '<li class="page-item"><a class="page-link" href="'.$buildPageUrl($totalPages).'">'.$totalPages.'</a></li>';
        }
        ?>
        <li class="page-item <?= $page>=$totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $page>=$totalPages ? '#' : $buildPageUrl($next) ?>">بعدی</a>
        </li>
      </ul>
    </nav>
</div>

<!-- Modals -->
<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addProductForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">اضافه کردن محصول</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
        </div>
        <div class="modal-body">
            <?php
                $resMax = $conn->query("SELECT MAX(id) AS max_id FROM products");
                $rmax = $resMax->fetch(PDO::FETCH_ASSOC);
                $new_id = $rmax['max_id'] ? $rmax['max_id'] + 1 : 1;
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="ADD">
            <div class="mb-3">
                <label class="form-label">شناسه</label>
                <input type="text" name="id" class="form-control" value="<?= htmlspecialchars($new_id) ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">کد کالا</label>
                <input type="text" name="product_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">نام</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">موجودی</label>
                <input type="number" name="qty" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">قیمت (ریال)</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">تصویر (png,jpg,jpeg)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png" required>
            </div>
            <div class="mb-3">
                <label class="form-label">توضیحات</label>
                <textarea name="details" class="form-control" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
          <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> اضافه کردن
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editProductForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">ویرایش محصول</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="EDIT">
            <input type="hidden" id="edit_id" name="id">
            <div class="mb-3">
                <label class="form-label">کد کالا</label>
                <input type="text" id="edit_product_code" name="product_code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">نام</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">موجودی</label>
                <input type="number" id="edit_qty" name="qty" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">قیمت (ریال)</label>
                <input type="number" id="edit_price" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">تصویر جدید (اختیاری)</label>
                <input type="file" id="edit_image" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                <input type="hidden" id="current_image" name="image_old">
            </div>
            <div class="mb-3">
                <label class="form-label">توضیحات</label>
                <textarea id="edit_details" name="details" class="form-control" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
          <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> ذخیره
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تصویر محصول</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loading" class="position-fixed top-50 start-50 translate-middle d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">در حال بارگذاری...</span>
    </div>
</div>

<?php
$conn = null;
include('./includes/footer.php');
?>