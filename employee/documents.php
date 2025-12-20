<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id    = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'] ?? null;

$documents = [];

if ($company_id) {
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

<?php include '../includes/emp_sidebar.php'; ?>

<main class="cc-main">

    <!-- Page Header -->
    <div class="cc-page-header">
        <div>
            <h1>Documents</h1>
            <p class="muted">Company shared files & uploads</p>
        </div>
    </div>

    <!-- Documents Card -->
    <div class="cc-card">

        <h3 style="margin-bottom:16px;">Shared Documents</h3>

        <?php if (empty($documents)): ?>
            <p class="muted">No documents uploaded yet.</p>
        <?php else: ?>

        <div class="cc-table-wrapper">
            <table class="cc-table">
                <thead>
                    <tr>
                        <th style="width:22%;">Document</th>
                        <th style="width:30%;">Description</th>
                        <th style="width:18%;">Uploaded By</th>
                        <th style="width:15%;">Date</th>
                        <th style="width:15%; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($documents as $doc):
                    $filePath = '../uploads/documents/' . $doc['file_path'];
                ?>
                    <tr>
                        <!-- Document -->
                        <td>
                            <div style="font-weight:600;">
                                <?= htmlspecialchars($doc['title']) ?>
                            </div>
                            <div class="muted" style="font-size:13px;">
                                <?= htmlspecialchars($doc['file_type'] ?? '') ?>
                            </div>
                        </td>

                        <!-- Description -->
                        <td>
                            <?= htmlspecialchars(
                                strlen($doc['description'] ?? '') > 120
                                    ? substr($doc['description'], 0, 120) . '...'
                                    : ($doc['description'] ?? '-')
                            ) ?>
                        </td>

                        <!-- Uploaded By -->
                        <td><?= htmlspecialchars($doc['uploader_name'] ?? '—') ?></td>

                        <!-- Date -->
                        <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>

                        <!-- Action -->
                        <td style="text-align:center;">
                            <?php if (!empty($doc['file_path']) && file_exists($filePath)): ?>
                                <a href="<?= htmlspecialchars($filePath) ?>"
                                   download
                                   class="btn btn-success btn-sm">
                                    Download
                                </a>
                            <?php else: ?>
                                <span class="muted">Not found</span>
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

<?php include '../includes/footer.php'; ?>

<style>
/* ===============================
   CloudConnect Table Styling
================================ */

.cc-main{
  margin-left:300px;
  padding:32px;
  padding-top:96px;
  min-height:calc(100vh - 140px);
}

.cc-table-wrapper{
  overflow-x:auto;
}

.cc-table{
  width:100%;
  border-collapse:collapse;
}

.cc-table th,
.cc-table td{
  padding:14px 16px;
  vertical-align:top;
  text-align:left;
}

.cc-table thead th{
  background:linear-gradient(180deg,#0b1220,#17202a);
  color:#fff;
  font-size:14px;
  font-weight:600;
}

.cc-table tbody tr{
  border-bottom:1px solid #eee;
}

.cc-table tbody tr:hover{
  background:#fafbff;
}

/* Footer alignment */
.cc-footer{
  margin-left:300px;
}

@media(max-width:1000px){
  .cc-main{margin-left:0;padding-top:88px;}
  .cc-footer{margin-left:0;}
}
</style>