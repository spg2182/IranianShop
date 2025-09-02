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

$title = "مدیریت کاربران";
include('./includes/header.php');

// بررسی وضعیت ورود کاربر
if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    echo "<script>location.replace('./not_logged_in.php');</script>";
    exit;
}
// اتصال به پایگاه داده با استفاده از PDO
try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("اتصال به پایگاه داده ناموفق:\n " . $e->getMessage());
}
// تعداد کاربران در هر صفحه
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
// جستجو
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
$params = [];
if ($search) {
    $where = "WHERE username LIKE :search OR email LIKE :search OR realname LIKE :search";
    $params[':search'] = "%$search%";
}
// دریافت کاربران با استفاده از Prepared Statements
$stmt = $conn->prepare("SELECT * FROM users $where ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// محاسبه تعداد صفحات
$sql_total = "SELECT COUNT(*) FROM users $where";
$stmt_total = $conn->prepare($sql_total);
foreach ($params as $key => $value) {
    $stmt_total->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt_total->execute();
$total_rows = $stmt_total->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<!-- بخش اصلی مدیریت کاربران -->
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people-fill me-2"></i> مدیریت کاربران</h2>
        <a href="admin_add_user.php" class="btn btn-success">
            <i class="bi bi-person-plus-fill me-1"></i> افزودن کاربر جدید
        </a>
    </div>
    
    <!-- کارت آمار -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $total_rows; ?></h4>
                            <p class="card-text">تعداد کل کاربران</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php 
                $active_users = 0;
                foreach ($users as $user) {
                    if ($user['is_active']) $active_users++;
                }
            ?>
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $active_users; ?></h4>
                            <p class="card-text">کاربران فعال</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php 
                $admin_users = 0;
                foreach ($users as $user) {
                    if ($user['user_type'] == 1) $admin_users++;
                }
            ?>
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo $admin_users; ?></h4>
                            <p class="card-text">مدیران سیستم</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-shield-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?php echo count($users); ?></h4>
                            <p class="card-text">کاربران این صفحه</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-table fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- کارت جستجو و فیلتر -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i> جستجو و فیلتر</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام کاربری، نام واقعی یا ایمیل..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> جستجو
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- کارت جدول کاربران -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i> لیست کاربران</h5>
            <div>
                <span class="badge bg-primary"><?php echo $total_rows; ?> کاربر</span>
                <span class="badge bg-secondary">صفحه <?php echo $page; ?> از <?php echo $total_pages; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">نام کاربری</th>
                            <th width="15%">نام واقعی</th>
                            <th width="20%">ایمیل</th>
                            <th width="10%">نوع کاربر</th>
                            <th width="10%">وضعیت</th>
                            <th width="15%">آخرین ورود</th>
                            <th width="10%">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo $start + $index + 1; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <?php echo substr(htmlspecialchars($user['realname']), 0, 1); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <small class="text-muted">کد: <?php echo $user['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['realname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['user_type'] == 1): ?>
                                        <span class="badge bg-warning text-dark">مدیر</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">عادی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">فعال</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">غیرفعال</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $last_login = !empty($user['last_login']) ? $user['last_login'] : $user['created_at'];
                                        echo date('Y/m/d H:i', strtotime($last_login));
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="admin_edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="toggle_status.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $user['is_active'] ? 'غیرفعال کردن' : 'فعال کردن'; ?>" onclick="return confirm('آیا مطمئن هستید؟')">
                                            <i class="bi bi-<?php echo $user['is_active'] ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                        </a>
<a href="javascript:void(0)" class="btn btn-sm btn-outline-danger" title="حذف" 
   onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['realname'], ENT_QUOTES) ?>')">
    <i class="bi bi-trash"></i>
</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                    هیچ کاربری یافت نشد.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <!-- صفحه‌بندی -->
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=1<?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    
                    <?php 
                        // نمایش حداکثر 5 صفحه در صفحه‌بندی
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                        }
                        
                        if ($end_page < $total_pages) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    ?>
                    
                    <li class="page-item <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item <?= ($page == $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $total_pages ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<style>
/* استایل‌های اختصاصی */
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--bs-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.card {
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin: 0 2px;
}

.pagination .page-link {
    border-radius: 0.375rem;
    margin: 0 2px;
    color: var(--bs-primary);
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

/* انیمیشن برای ظاهر شدن کارت‌ها */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease forwards;
}
</style>

<script>
$(document).ready(function() {
    // بهبود تجربه کاربری در جستجو
    $('input[name="search"]').on('keyup', function(e) {
        if (e.key === 'Enter') {
            $(this).closest('form').submit();
        }
    });
    
    // تأییدیه‌های حذف و تغییر وضعیت با استفاده از SweetAlert
    if (typeof Swal !== 'undefined') {
        $('a[onclick*="confirm"]').on('click', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            const message = $(this).attr('onclick').match(/confirm\('(.+?)'\)/)[1];
            
            Swal.fire({
                title: 'تأییدیه',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله',
                cancelButtonText: 'خیر'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    }
});
</script>

<?php
// بستن اتصال به پایگاه داده
$conn = null;
include('./includes/footer.php');
?>