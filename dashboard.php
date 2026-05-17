<?php
require_once 'connect.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$title = "Dashboard";

$lostCount     = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='LOST'")->fetch_assoc()['c'] ?? 0;
$foundCount    = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='FOUND'")->fetch_assoc()['c'] ?? 0;
$reunitedCount = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='REUNITED'")->fetch_assoc()['c'] ?? 0;
$activeCount   = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='PENDING'")->fetch_assoc()['c'] ?? 0;

$lostWeek     = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='LOST'  AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;
$foundWeek    = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE type='FOUND' AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;
$reunitedWeek = $connection->query("SELECT COUNT(*) AS c FROM posts WHERE currentStatus='REUNITED' AND dateReported >= NOW() - INTERVAL 7 DAY")->fetch_assoc()['c'] ?? 0;

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
    if ($diff < 3600)  return floor($diff/60)   . ' min ago';
    if ($diff < 86400) return floor($diff/3600)  . ' hours ago';
    return floor($diff/86400) . ' days ago';
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
/* tabs */
.db-tabs { display: flex; }
.db-tab {
    padding: 9px 24px; font-size: 14px; font-weight: 600;
    border: none; cursor: pointer; border-radius: 10px 10px 0 0;
    transition: background .18s, color .18s;
}
.db-tab.active { background: var(--cr); color: #fff; }
.db-tab:not(.active) { background: #fff; color: #6B7280; }
.db-tab:not(.active):hover { background: var(--cr-bg); color: var(--cr); }

/* hero */
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
.act-card.green { background: var(--gn-bg); }
.act-icon { width: 48px; height: 48px; border-radius: 13px; display: flex; align-items: center; justify-content: center; }
.act-icon.red   { background: var(--cr); } .act-icon.green { background: var(--gn); }
.act-icon i { color: #fff; font-size: 20px; }
.act-card h3 { font-size: 13px; font-weight: 700; margin: 0; }
.act-card h3.red { color: var(--cr); } .act-card h3.green { color: var(--gn); }
.act-card p { font-size: 11px; color: #6B7280; line-height: 1.5; margin: 0; }
.btn-act {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 14px; border-radius: 8px; font-size: 12px; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none; transition: opacity .18s;
}
.btn-act:hover { opacity: .85; }
.btn-act.red   { background: var(--cr); color: #fff; }
.btn-act.green { background: var(--gn); color: #fff; }

/* stats */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.stat-card {
    background: #fff; border-radius: var(--r); box-shadow: var(--sh);
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
}
.stat-ico { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-ico.red   { background: #FDECEA; } .stat-ico.red   i { color: var(--cr); }
.stat-ico.green { background: #E6F4EC; } .stat-ico.green i { color: var(--gn); }
.stat-ico.gold  { background: var(--gd-bg); } .stat-ico.gold i { color: var(--gd); }
.stat-ico.blue  { background: var(--bl-bg); } .stat-ico.blue i { color: var(--bl); }
.stat-info .lbl  { font-size: 12px; color: #6B7280; font-weight: 500; }
.stat-info .num  { font-size: 26px; font-weight: 800; line-height: 1.15; color: #1A1A2E; }
.stat-info .trnd { font-size: 12px; color: #16a34a; font-weight: 500; }
.stat-info .val  { font-size: 12px; color: var(--bl); font-weight: 600; }
.stat-info .val a { color: var(--bl); text-decoration: none; }
.stat-info .val a:hover { text-decoration: underline; }

/* recent */
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

/* footer */
.db-footer {
    margin-top: auto;
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px solid #E5E7EB; padding-top: 14px;
    font-size: 12px; color: #9CA3AF;
}
.db-footer a { color: #9CA3AF; text-decoration: none; }
.db-footer a:hover { color: #374151; }
.db-footer-links { display: flex; gap: 18px; }
</style>

<div class="layout">
    <?php include 'side-menu.php'; ?>

    <div class="main-content">

        <div class="db-wrap">
            <!-- HERO CARD -->
            <div class="hero-card">
                <div class="hero-illus">
                    <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="200" height="160" fill="#FDE8EA" rx="10"/>
                        <rect x="18" y="58" width="26" height="72" fill="#E8B4BB" rx="4"/>
                        <rect x="48" y="42" width="22" height="88" fill="#D99099" rx="4"/>
                        <rect x="78" y="68" width="20" height="62" fill="#E8B4BB" rx="4"/>
                        <rect x="148" y="54" width="25" height="76" fill="#D99099" rx="4"/>
                        <rect x="175" y="68" width="18" height="62" fill="#E8B4BB" rx="4"/>
                        <rect x="66" y="116" width="66" height="7" fill="#8B4513" rx="3"/>
                        <rect x="76" y="123" width="4" height="15" fill="#6B3410" rx="2"/>
                        <rect x="118" y="123" width="4" height="15" fill="#6B3410" rx="2"/>
                        <rect x="48" y="78" width="4" height="50" fill="#9B6B5A" rx="2"/>
                        <path d="M48 78 Q42 70 52 66" stroke="#9B6B5A" stroke-width="3" fill="none"/>
                        <circle cx="42" cy="66" r="5" fill="#FBBF24" opacity=".8"/>
                        <rect x="148" y="78" width="4" height="50" fill="#9B6B5A" rx="2"/>
                        <path d="M152 78 Q158 70 148 66" stroke="#9B6B5A" stroke-width="3" fill="none"/>
                        <circle cx="158" cy="66" r="5" fill="#FBBF24" opacity=".8"/>
                        <ellipse cx="30" cy="88" rx="13" ry="20" fill="#C8A0A8" opacity=".55"/>
                        <ellipse cx="170" cy="86" rx="12" ry="18" fill="#C8A0A8" opacity=".55"/>
                        <rect x="0" y="128" width="200" height="32" fill="#E8B4BB"/>
                    </svg>
                </div>

                <div class="hero-text">
                    <h1>WELCOME,<br><?= strtoupper(htmlspecialchars($user['full_name'] ?? 'USER')) ?>!</h1>
                    <p>Report or search for lost<br>and found items in your area.</p>
                </div>

                <div class="hero-div"></div>

                <div class="hero-actions">
                    <div class="act-card">
                        <div class="act-icon red"><i class="fas fa-shopping-bag"></i></div>
                        <h3 class="red">Report a Lost Item</h3>
                        <p>Let others know about your lost item.</p>
                        <a href="report_lost.php" class="btn-act red">Report Lost Item</a>
                    </div>
                    <div class="act-card green">
                        <div class="act-icon green"><i class="fas fa-box"></i></div>
                        <h3 class="green">Report a Found Item</h3>
                        <p>Help someone by reporting a found item.</p>
                        <a href="report_found.php" class="btn-act green">Report Found Item</a>
                    </div>
                </div>
            </div>

            <!-- STATS -->
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
                    <div class="stat-ico blue"><i class="fas fa-eye fa-lg"></i></div>
                    <div class="stat-info">
                        <div class="lbl">Active Reports</div>
                        <div class="num"><?= $activeCount ?></div>
                        <div class="val"><a href="lost_items.php">View all reports &rarr;</a></div>
                    </div>
                </div>
            </div>

            <!-- RECENT ITEMS -->
            <div class="recent-grid">

                <div class="rc-card">
                    <div class="rc-head">
                        <h3>Recent Lost Items</h3>
                        <a href="lost_items.php">View All &rsaquo;</a>
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
                        <a href="found_items.php">View All &rsaquo;</a>
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
            </div>
        </div><!-- /db-wrap -->
    </div><!-- /main-content -->
</div><!-- /layout -->

<script>
document.querySelectorAll('.db-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.db-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
</script>

<?php include 'header_footer/footer.php'; ?>
