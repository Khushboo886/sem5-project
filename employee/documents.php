<?php
require_once '../includes/session.php';
requireEmployee(); // only employees can access
require_once '../includes/db.php';
include '../includes/header.php';

$user_id    = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'] ?? null;

// If no company in session, show empty list
$documents = [];

if ($company_id) {
    // Show public docs + docs uploaded by this user
    $stmt = $pdo->prepare("
        SELECT d.*, u.name AS uploader_name
        FROM documents d
        LEFT JOIN users u ON d.uploaded_by = u.id
        WHERE d.company_id = ?
          AND (d.access_level = 'public' OR d.uploaded_by = ?)
        ORDER BY d.created_at DESC
    ");
    $stmt->execute([$company_id, $user_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Layout container -->
<div class="cc-app" style="display:flex; min-height:100vh; background:#f9fafc;">
    <!-- Sidebar -->
    <?php include '../includes/emp_sidebar.php'; ?>

    <!-- Main content -->
    <main class="cc-main" style="flex:1; padding:30px;">

        <h2 style="margin-bottom:20px; font-weight:600; color:#222;">DOCUMENTS</h2>

        <div style="background:white; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.08); padding:18px; margin-bottom:18px;">
            <h3 style="margin-top:0; margin-bottom:12px;">Shared Documents List</h3>

            <?php if (empty($documents)): ?>
                <p style="color:#666; margin:8px 0;">No documents uploaded yet.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:720px; font-size:15px;">
                        <thead>
                            <tr style="background:linear-gradient(180deg,#0b1220,#17202a); color:#fff;">
                                <th style="padding:12px; text-align:left;">Document Name</th>
                                <th style="padding:12px; text-align:left; width:35%;">Description</th>
                                <th style="padding:12px; text-align:left;">Uploaded By</th>
                                <th style="padding:12px; text-align:left;">Uploaded On</th>
                                <th style="padding:12px; text-align:center; width:130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): 
                                // file path on disk
                                $filePath = '../uploads/documents/' . $doc['file_path'];
                            ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:12px;">
                                        <strong><?= htmlspecialchars($doc['title']) ?></strong>
                                        <div style="font-size:13px; color:#666; margin-top:6px;">
                                            <small><?= htmlspecialchars($doc['file_type'] ?: '') ?></small>
                                        </div>
                                    </td>

                                    <td style="padding:12px; color:#333;">
                                        <?= nl2br(htmlspecialchars(strlen($doc['description'] ?? '') > 200 ? substr($doc['description'],0,200) . '...' : ($doc['description'] ?? '-'))); ?>
                                    </td>

                                    <td style="padding:12px;"><?= htmlspecialchars($doc['uploader_name'] ?? ' — ') ?></td>

                                    <td style="padding:12px;"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>

                                    <td style="padding:12px; text-align:center;">
                                        <?php if (!empty($doc['file_path']) && file_exists($filePath)): ?>
                                            <a href="<?= htmlspecialchars($filePath) ?>" download
                                               style="display:inline-block; text-decoration:none; background:#198754; color:#fff; padding:8px 10px; border-radius:6px;">
                                               Download
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#999; font-size:14px;">Not found</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include '../includes/footer.php'; ?>