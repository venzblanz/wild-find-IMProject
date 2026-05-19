<?php
require_once 'connect.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['userID'];
$stmt = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$title = "Admin Dashboard";

// --- Stats ---
$lostCount     = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='LOST'")->fetch_assoc()['c'] ?? 0;
$foundCount    = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='FOUND'")->fetch_assoc()['c'] ?? 0;
$reunitedCount = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='REUNITED'")->fetch_assoc()['c'] ?? 0;
$userCount     = $connection->query("SELECT COUNT(*) AS c FROM users WHERE is_admin = 0")->fetch_assoc()['c'] ?? 0;
$pendingCount  = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='PENDING'")->fetch_assoc()['c'] ?? 0;

$lostWeek     = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='LOST'  AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;
$foundWeek    = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='FOUND' AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;
$reunitedWeek = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='REUNITED' AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;

// --- Recent posts ---
$recentLostResult = $connection->query("
    SELECT p.post_id, p.itemName, p.dateReported, p.currentStatus, l.locationName
    FROM posts p
    LEFT JOIN location l ON p.location_id = l.location_id
    WHERE p.type = 'LOST'
    ORDER BY p.dateReported DESC
    LIMIT 3
");
$recentLost = $recentLostResult ? $recentLostResult->fetch_all(MYSQLI_ASSOC) : [];

$recentFoundResult = $connection->query("
    SELECT p.post_id, p.itemName, p.dateReported, p.currentStatus, l.locationName
    FROM posts p
    LEFT JOIN location l ON p.location_id = l.location_id
    WHERE p.type = 'FOUND'
    ORDER BY p.dateReported DESC
    LIMIT 3
");
$recentFound = $recentFoundResult ? $recentFoundResult->fetch_all(MYSQLI_ASSOC) : [];

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 3600)  return floor($diff / 60)   . ' min ago';
    if ($diff < 86400) return floor($diff / 3600)  . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}
?>

<?php include 'header_footer/main-header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
:root {
    --cr:    #7B0E1E;
    --cr-dk: #5A0A15;
    --cr-lt: #C8193A;
    --cr-bg: #F9E8EA;
    --gn:    #146B3A;
    --gn-bg: #E8F5EE;
    --gd:    #B8860B;
    --gd-bg: #FEF4E3;
    --bl:    #1A5CA4;
    --bl-bg: #EAF1FB;
    --pu:    #6D3FA0;
    --pu-bg: #F3EDFB;
    --r:     14px;
    --sh:    0 2px 12px rgba(0,0,0,.08);
}

.db-wrap {
    flex: 1; padding: 28px;
    display: flex; flex-direction: column; gap: 22px;
    background: #F4F5F7;
    min-height: calc(100vh - 70px);
    box-sizing: border-box;
}

/* ── Hero card ── */
.hero-card {
    background: #fff; border-radius: var(--r); box-shadow: var(--sh);
    display: flex; align-items: stretch; overflow: hidden;
}
.hero-illus {
    width: 196px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; padding: 16px;
}
.hero-illus svg { width: 100%; }
.hero-text {
    flex: 1; padding: 28px 20px;
    display: flex; flex-direction: column; justify-content: center;
}
.hero-text h1 { font-size: 30px; font-weight: 800; line-height: 1.15; color: #1A1A2E; margin-bottom: 8px; }
.hero-text p  { font-size: 14px; color: #6B7280; line-height: 1.6; margin: 0; }
.hero-div { width: 1px; background: #E5E7EB; align-self: stretch; margin: 14px 0; flex-shrink: 0; }
.hero-actions { display: flex; align-items: center; gap: 14px; padding: 24px 28px; }

.act-card {
    background: var(--cr-bg); border-radius: 12px; padding: 20px 18px;
    width: 178px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.act-card.blue   { background: var(--bl-bg); }
.act-card.purple { background: var(--pu-bg); }

.act-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; }
.act-icon.red    { background: var(--cr); }
.act-icon.blue   { background: var(--bl); }
.act-icon.purple { background: var(--pu); }
.act-icon i { color: #fff; font-size: 20px; }

.act-card h3 { font-size: 13px; font-weight: 700; margin: 0; }
.act-card h3.red    { color: var(--cr); }
.act-card h3.blue   { color: var(--bl); }
.act-card h3.purple { color: var(--pu); }
.act-card p { font-size: 11px; color: #6B7280; line-height: 1.5; margin: 0; }

.btn-act {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 14px; border-radius: 8px; font-size: 12px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity .18s;
}
.btn-act:hover { opacity: .85; }
.btn-act.red    { background: var(--cr); color: #fff; }
.btn-act.blue   { background: var(--bl); color: #fff; }
.btn-act.purple { background: var(--pu); color: #fff; }

/* ── Stats grid ── */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.stat-card {
    background: #fff; border-radius: var(--r); box-shadow: var(--sh);
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
}
.stat-ico { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-ico.red    { background: #FDECEA; } .stat-ico.red    i { color: var(--cr); }
.stat-ico.green  { background: #E6F4EC; } .stat-ico.green  i { color: var(--gn); }
.stat-ico.gold   { background: var(--gd-bg); } .stat-ico.gold i { color: var(--gd); }
.stat-ico.blue   { background: var(--bl-bg); } .stat-ico.blue i { color: var(--bl); }
.stat-ico.purple { background: var(--pu-bg); } .stat-ico.purple i { color: var(--pu); }

.stat-info .lbl  { font-size: 12px; color: #6B7280; font-weight: 500; }
.stat-info .num  { font-size: 26px; font-weight: 800; line-height: 1.15; color: #1A1A2E; }
.stat-info .trnd { font-size: 12px; color: #16a34a; font-weight: 500; }
.stat-info .val  { font-size: 12px; color: var(--bl); font-weight: 600; }
.stat-info .val a { color: var(--bl); text-decoration: none; }
.stat-info .val a:hover { text-decoration: underline; }

/* ── Recent items ── */
.recent-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.rc-card { background: #fff; border-radius: var(--r); box-shadow: var(--sh); padding: 20px; }
.rc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.rc-head h3 { font-size: 15px; font-weight: 700; margin: 0; }
.rc-head a  { font-size: 13px; color: var(--bl); font-weight: 600; text-decoration: none; }
.rc-head a:hover { text-decoration: underline; }

.item-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #F0F0F0; }
.item-row:last-child { border-bottom: none; }
.item-ico {
    width: 44px; height: 44px; border-radius: 10px; background: #F4F5F7; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.item-info { flex: 1; }
.item-info .nm  { font-size: 14px; font-weight: 600; color: #1A1A2E; }
.item-info .loc { font-size: 12px; color: #6B7280; }
.item-time { font-size: 11px; color: #9CA3AF; flex-shrink: 0; }

.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.badge.lost  { background: #FDECEA; color: var(--cr); }
.badge.found { background: #E6F4EC; color: var(--gn); }
.empty-state { text-align: center; padding: 24px 0; color: #9CA3AF; font-size: 13px; }

/* ── Admin badge on hero ── */
.admin-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--cr); color: #fff;
    font-size: 10px; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase;
    padding: 3px 10px; border-radius: 20px;
    margin-bottom: 10px;
}
.admin-chip i { font-size: 9px; }
</style>

<div class="layout">
    <?php require_once 'admin-side-menu.php'; ?>

    <div class="main-content">
        <div class="db-wrap">

            <!-- ── HERO CARD ── -->
            <div class="hero-card">

                <!-- Illustration -->
                <div class="hero-illus">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="200" height="160" fill="#F0E8F5" rx="10"/>
                        <!-- desk surface -->
                        <rect x="20" y="90" width="160" height="8" fill="#C5A8D8" rx="3"/>
                        <!-- monitor -->
                        <rect x="60" y="40" width="80" height="52" fill="#fff" rx="6" stroke="#9B6BB5" stroke-width="2"/>
                        <rect x="64" y="44" width="72" height="40" fill="#EAF1FB" rx="4"/>
                        <!-- screen lines -->
                        <rect x="68" y="50" width="40" height="3" fill="#1A5CA4" rx="2" opacity=".6"/>
                        <rect x="68" y="57" width="56" height="2" fill="#9CA3AF" rx="2" opacity=".5"/>
                        <rect x="68" y="63" width="48" height="2" fill="#9CA3AF" rx="2" opacity=".5"/>
                        <rect x="68" y="69" width="30" height="2" fill="#9CA3AF" rx="2" opacity=".4"/>
                        <!-- monitor stand -->
                        <rect x="95" y="92" width="10" height="10" fill="#9B6BB5" rx="2"/>
                        <rect x="86" y="100" width="28" height="4" fill="#9B6BB5" rx="2"/>
                        <!-- keyboard -->
                        <rect x="55" y="106" width="90" height="14" fill="#D8C6E8" rx="4"/>
                        <rect x="60" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="69" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="78" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="87" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="96" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="105" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="114" y="109" width="6" height="3" fill="#9B6BB5" rx="1" opacity=".5"/>
                        <rect x="68" y="114" width="64" height="3" fill="#9B6BB5" rx="1" opacity=".35"/>
                        <!-- mouse -->
                        <rect x="152" y="106" width="14" height="18" fill="#D8C6E8" rx="7"/>
                        <line x1="159" y1="106" x2="159" y2="115" stroke="#9B6BB5" stroke-width="1.5" opacity=".5"/>
                        <!-- shield/admin badge -->
                        <path d="M28 52 L28 65 Q28 72 35 75 Q42 72 42 65 L42 52 L35 49 Z" fill="#7B0E1E" opacity=".9"/>
                        <path d="M32 62 L34 64 L39 58" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- floor -->
                        <rect x="0" y="128" width="200" height="32" fill="#D8C6E8"/>
                    </svg>
                </div>

                <!-- Welcome text -->
                <div class="hero-text">
                    <div class="admin-chip"><i class="fas fa-shield-alt"></i> Admin Panel</div>
                    <h1>WELCOME,<br><?= strtoupper(htmlspecialchars($user['full_name'] ?? 'ADMIN')) ?>!</h1>
                    <p>Manage users, review reports,<br>and keep the platform running smoothly.</p>
                </div>

                <div class="hero-div"></div>

                <!-- Admin action cards -->
                <div class="hero-actions">
                    <div class="act-card blue">
                        <div class="act-icon blue"><i class="fas fa-users"></i></div>
                        <h3 class="blue">Manage Users</h3>
                        <p>View, edit, or remove registered accounts.</p>
                        <a href="manage-users.php" class="btn-act blue">
                            <i class="fas fa-arrow-right"></i> Manage Users
                        </a>
                    </div>
                    <div class="act-card purple">
                        <div class="act-icon purple"><i class="fas fa-clipboard-list"></i></div>
                        <h3 class="purple">All Reports</h3>
                        <p>Review every lost and found report on the platform.</p>
                        <a href="all-reports.php" class="btn-act purple">
                            <i class="fas fa-arrow-right"></i> View Reports
                        </a>
                    </div>
                </div>

            </div><!-- /hero-card -->

            <!-- ── STATS ── -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-ico red"><i class="fas fa-question-circle fa-lg"></i></div>
                    <div class="stat-info">
                        <div class="lbl">Lost Items</div>
                        <div class="num"><?= $lostCount ?></div>
                        <div class="trnd">+<?= $lostWeek ?> this week ↗</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico green"><i class="fas fa-shopping-bag fa-lg"></i></div>
                    <div class="stat-info">
                        <div class="lbl">Found Items</div>
                        <div class="num"><?= $foundCount ?></div>
                        <div class="trnd">+<?= $foundWeek ?> this week ↗</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico gold"><i class="fas fa-handshake fa-lg"></i></div>
                    <div class="stat-info">
                        <div class="lbl">Reunited Items</div>
                        <div class="num"><?= $reunitedCount ?></div>
                        <div class="trnd">+<?= $reunitedWeek ?> this week ↗</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ico blue"><i class="fas fa-users fa-lg"></i></div>
                    <div class="stat-info">
                        <div class="lbl">Registered Users</div>
                        <div class="num"><?= $userCount ?></div>
                        <div class="val"><a href="manage-users.php">View all users &rarr;</a></div>
                    </div>
                </div>
            </div>

            <!-- ── RECENT ITEMS ── -->
            <div class="recent-grid">

                <div class="rc-card">
                    <div class="rc-head">
                        <h3>Recent Lost Items</h3>
                        <a href="all-reports.php?type=lost">View All &rsaquo;</a>
                    </div>
                    <?php if (empty($recentLost)): ?>
                        <div class="empty-state">No lost items reported yet.</div>
                    <?php else: ?>
                        <?php foreach ($recentLost as $item): ?>
                        <div class="item-row">
                            <div class="item-ico">🔍</div>
                            <div class="item-info">
                                <div class="nm"><?= htmlspecialchars($item['itemName'] ?? '—') ?></div>
                                <div class="loc"><?= htmlspecialchars($item['locationName'] ?? 'Unknown location') ?></div>
                            </div>
                            <span class="item-time"><?= timeAgo($item['dateReported']) ?></span>
                            <span class="badge lost">Lost</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="rc-card">
                    <div class="rc-head">
                        <h3>Recent Found Items</h3>
                        <a href="all-reports.php?type=found">View All &rsaquo;</a>
                    </div>
                    <?php if (empty($recentFound)): ?>
                        <div class="empty-state">No found items reported yet.</div>
                    <?php else: ?>
                        <?php foreach ($recentFound as $item): ?>
                        <div class="item-row">
                            <div class="item-ico">📦</div>
                            <div class="item-info">
                                <div class="nm"><?= htmlspecialchars($item['itemName'] ?? '—') ?></div>
                                <div class="loc"><?= htmlspecialchars($item['locationName'] ?? 'Unknown location') ?></div>
                            </div>
                            <span class="item-time"><?= timeAgo($item['dateReported']) ?></span>
                            <span class="badge found">Found</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div><!-- /recent-grid -->

        </div><!-- /db-wrap -->
    </div><!-- /main-content -->
</div><!-- /layout -->

<?php include 'header_footer/footer.php'; ?>