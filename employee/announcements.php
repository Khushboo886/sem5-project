<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id    = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'] ?? null;

// helper: snippet
function snippet($text, $len = 140) {
    $t = strip_tags($text);
    if (mb_strlen($t) <= $len) return $t;
    return mb_substr($t, 0, $len) . '...';
}

// mark all announcement notifications for this user as read
if (isset($_GET['mark']) && $_GET['mark'] === 'all') {
    $u = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND type = 'announcement'");
    $u->execute([$user_id]);
    header('Location: announcements.php');
    exit;
}

// If id is present, show single announcement view
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($viewId && $company_id) {
    // fetch announcement and ensure it belongs to user's company
    $stmt = $pdo->prepare("
        SELECT id, title, content, priority, start_date, end_date, created_at, created_by, company_id
        FROM announcements
        WHERE id = ? AND company_id = ? LIMIT 1
    ");
    $stmt->execute([$viewId, $company_id]);
    $ann = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ann) {
        // not found or unauthorized
        header('Location: announcements.php');
        exit;
    }

    // mark notifications for this announcement as read for this user
    $m = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND type = 'announcement' AND reference_id = ?");
    $m->execute([$user_id, $viewId]);

    // Render single announcement view below (HTML section)
    ?>
    <div class="cc-app" style="display:flex; min-height:100vh; background:#f9fafc;">
        <?php include '../includes/emp_sidebar.php'; ?>

        <main class="cc-main" style="flex:1; padding:30px;">
            <h2 style="margin-bottom:18px; font-weight:600;">Employee Dashboard &gt; Announcements</h2>

            <div style="background:white; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,0.06); max-width:900px;">
                <div style="display:flex; justify-content:space-between; gap:18px; align-items:flex-start;">
                    <div style="flex:1;">
                        <h1 style="margin:0 0 8px 0; font-size:22px; color:#111;"><?= htmlspecialchars($ann['title']) ?></h1>

                        <div style="color:#666; font-size:13px; margin-bottom:14px;">
                            <?= date('d M Y, h:i A', strtotime($ann['created_at'])) ?>
                            <?php if (!empty($ann['start_date']) || !empty($ann['end_date'])): ?>
                                &nbsp; | &nbsp;
                                <?= !empty($ann['start_date']) ? htmlspecialchars($ann['start_date']) : '' ?>
                                <?= (!empty($ann['start_date']) && !empty($ann['end_date'])) ? " → " : "" ?>
                                <?= !empty($ann['end_date']) ? htmlspecialchars($ann['end_date']) : '' ?>
                            <?php endif; ?>
                        </div>

                        <div style="color:#333; line-height:1.6; font-size:15px;">
                            <?= nl2br(htmlspecialchars($ann['content'])) ?>
                        </div>
                    </div>

                    <div style="min-width:140px; text-align:right;">
                        <div style="background:<?= $ann['priority'] === 'high' ? '#dc3545' : ($ann['priority'] === 'medium' ? '#ffc107' : '#198754') ?>; color:#fff; padding:8px 10px; border-radius:8px; display:inline-block; font-weight:600;">
                            <?= ucfirst($ann['priority'] ?? 'medium') ?>
                        </div>

                        <div style="margin-top:20px;">
                            <a href="announcements.php" style="text-decoration:none; color:#0d6efd; font-weight:600;">← Back to Announcements</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php
    include '../includes/footer.php';
    exit;
}

// ---------- LIST VIEW (default) ----------
$showAll = isset($_GET['all']) && $_GET['all'] == '1';

// fetch announcements for company
$announcements = [];
if ($company_id) {
    if ($showAll) {
        $q = "SELECT id, title, content, priority, start_date, end_date, created_at
              FROM announcements
              WHERE company_id = ?
              ORDER BY created_at DESC";
        $stmt = $pdo->prepare($q);
        $stmt->execute([$company_id]);
    } else {
        $q = "SELECT id, title, content, priority, start_date, end_date, created_at
              FROM announcements
              WHERE company_id = ?
              ORDER BY created_at DESC
              LIMIT 5";
        $stmt = $pdo->prepare($q);
        $stmt->execute([$company_id]);
    }
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// unread counts & user notif map
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'announcement' AND is_read = 0");
$countStmt->execute([$user_id]);
$unreadCount = (int)$countStmt->fetchColumn();

$notifStmt = $pdo->prepare("SELECT reference_id, is_read FROM notifications WHERE user_id = ? AND type = 'announcement'");
$notifStmt->execute([$user_id]);
$userNotifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
$notifByRef = [];
foreach ($userNotifs as $n) {
    $notifByRef[(int)$n['reference_id']] = (int)$n['is_read'];
}

// ---------- RENDER LIST ----------
?>
<div class="cc-app" style="display:flex; min-height:100vh; background:#f9fafc;">
    <?php include '../includes/emp_sidebar.php'; ?>

    <main class="cc-main" style="flex:1; padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h2 style="margin:0; font-weight:600; color:#222;">Employee Dashboard &gt; Announcements
                <?php if ($unreadCount > 0): ?>
                    <span style="display:inline-block; margin-left:12px; background:#dc3545; color:white; font-size:13px; padding:4px 8px; border-radius:999px;">
                        <?= $unreadCount ?>
                    </span>
                <?php endif; ?>
            </h2>

            <div>
                <?php if ($unreadCount > 0): ?>
                    <a href="announcements.php?mark=all" style="text-decoration:none; color:#0d6efd; font-weight:600;">Mark all as read</a>
                <?php endif; ?>
            </div>
        </div>

        <div style="background:white; border-radius:12px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <h3 style="margin-top:0; margin-bottom:14px;">Company Announcements</h3>

            <?php if (empty($announcements)): ?>
                <p style="color:#666;">No announcements available.</p>
            <?php else: ?>
                <ul style="list-style:none; padding:0; margin:0;">
                    <?php foreach ($announcements as $a): 
                        $isUnread = array_key_exists((int)$a['id'], $notifByRef) && $notifByRef[(int)$a['id']] == 0;
                        ?>
                        <li style="padding:12px; border-radius:8px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:flex-start; background:#fbfbfc;">
                            <div style="flex:1;">
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <div style="font-weight:600; font-size:15px; color:#111;">
                                        <?= htmlspecialchars($a['title']) ?>
                                    </div>
                                    <?php if ($isUnread): ?>
                                        <div title="Unread" style="width:10px; height:10px; background:#dc3545; border-radius:50%;"></div>
                                    <?php endif; ?>
                                </div>

                                <div style="color:#666; font-size:13px; margin-top:6px;">
                                    <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
                                    <?php if (!empty($a['start_date']) || !empty($a['end_date'])): ?>
                                        &nbsp; | &nbsp;
                                        <?= !empty($a['start_date']) ? htmlspecialchars($a['start_date']) : '' ?>
                                        <?= (!empty($a['start_date']) && !empty($a['end_date'])) ? " → " : "" ?>
                                        <?= !empty($a['end_date']) ? htmlspecialchars($a['end_date']) : '' ?>
                                    <?php endif; ?>
                                </div>

                                <div style="color:#333; margin-top:8px; font-size:14px; line-height:1.4;">
                                    <?= nl2br(htmlspecialchars(snippet($a['content'], 200))) ?>
                                </div>
                            </div>

                            <div style="margin-left:14px; text-align:right; min-width:120px;">
                                <div style="margin-bottom:8px;">
                                    <a href="announcements.php?id=<?= urlencode($a['id']) ?>"
                                       style="color:#0d6efd; text-decoration:none; font-weight:600;">View</a>
                                </div>
                                <div style="background:<?= $a['priority'] === 'high' ? '#dc3545' : ($a['priority'] === 'medium' ? '#ffc107' : '#198754') ?>; color:#fff; padding:6px 8px; border-radius:6px; font-size:12px;">
                                    <?= ucfirst($a['priority'] ?? 'medium') ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div style="text-align:right; margin-top:10px;">
                    <?php if (!$showAll): ?>
                        <a href="?all=1" style="color:#0d6efd; text-decoration:none; font-weight:600;">View All →</a>
                    <?php else: ?>
                        <a href="announcements.php" style="color:#0d6efd; text-decoration:none; font-weight:600;">Show Recent →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
