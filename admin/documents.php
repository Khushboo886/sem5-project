<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../includes/db.php';
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$errors = [];
$success = '';

/* ------------------------------------------------------
   DELETE DOCUMENT
------------------------------------------------------ */
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];

    $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id=? AND company_id=?");
    $stmt->execute([$id, $_SESSION['company_id']]);
    $doc = $stmt->fetch();

    if ($doc) {
        $path = '../uploads/documents/' . $doc['file_path'];
        if (file_exists($path)) unlink($path);

        $del = $pdo->prepare("DELETE FROM documents WHERE id=? AND company_id=?");
        $del->execute([$id, $_SESSION['company_id']]);
        $success = "Document deleted successfully.";
    } else {
        $errors[] = "Document not found or access denied.";
    }
}

/* ------------------------------------------------------
   UPLOAD DOCUMENT
------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $access = $_POST['access_level'] ?? 'public';

    if (!$title) $errors[] = "Document title is required.";
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "Please select a valid file.";

    if (!$errors) {
        $dir = '../uploads/documents/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $name = time().'_'.basename($_FILES['file']['name']);
        $type = pathinfo($name, PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir.$name)) {
            $stmt = $pdo->prepare("
                INSERT INTO documents
                (title, description, file_path, file_type, uploaded_by, company_id, access_level)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $description ?: null,
                $name,
                $type,
                $_SESSION['user_id'],
                $_SESSION['company_id'],
                $access
            ]);
            $success = "Document uploaded successfully.";
        } else {
            $errors[] = "File upload failed.";
        }
    }
}

/* ------------------------------------------------------
   FETCH DOCUMENTS
------------------------------------------------------ */
$stmt = $pdo->prepare("
    SELECT d.*, u.name AS uploader
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    WHERE d.company_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$_SESSION['company_id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Documents — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="/cloudconnect/assets/css/theme.css">

<style>
:root{
  --header-height:64px;
  --sidebar-width:300px;
}

/* ===== MAIN LAYOUT FIX ===== */
.cc-main{
  margin-left:var(--sidebar-width);
  padding:32px;
  padding-top: calc(var(--header-height) + 24px);
  min-height: calc(100vh - var(--header-height));
}

.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:24px;
}

.page-header h1{font-size:26px;margin-bottom:4px}
.page-header p{color:var(--muted)}

.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:24px;
  margin-bottom:28px;
}

.doc-header h3{font-size:20px;margin-bottom:4px}
.doc-header p{color:var(--muted);font-size:14px;margin-bottom:18px}

.doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.doc-grid .full{grid-column:1/-1}

label{font-size:13px;color:var(--muted);display:block;margin-bottom:6px}

input,textarea,select{
  width:100%;
  padding:12px 14px;
  border-radius:10px;
  border:1px solid var(--glass-border);
  background:transparent;
  color:var(--text);
  font-size:14px
}

.file-box{
  border:1px dashed var(--glass-border);
  border-radius:12px;
  padding:16px;
  display:flex;
  justify-content:space-between;
  align-items:center
}

.file-box span{font-size:12px;color:var(--muted)}

.form-actions{text-align:right;margin-top:18px}

table{width:100%;border-collapse:collapse}
th,td{padding:14px;border-bottom:1px solid rgba(255,255,255,.06)}
th{font-size:13px;color:var(--muted);text-align:left}

.badge{
  padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600
}
.badge-public{background:#39d353;color:#081018}
.badge-private{background:#ff6b6b;color:#140b0b}
.badge-specific{background:#4f8bff;color:#fff}

.actions a{margin-left:8px}

footer{
  margin-top:40px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
}

/* ===== MOBILE ===== */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:20px;
    padding-top: calc(var(--header-height) + 20px);
  }
  .doc-grid{grid-template-columns:1fr}
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">

  <div class="page-header">
    <div>
      <h1>Documents</h1>
      <p>Upload and manage company documents</p>
    </div>
  </div>

  <!-- UPLOAD FORM -->
  <div class="panel">
    <div class="doc-header">
      <h3>Upload New Document</h3>
      <p>Add files for employees securely</p>
    </div>

    <?php if($errors): ?>
      <div class="alert error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="doc-grid">
        <div>
          <label>Document Title</label>
          <input type="text" name="title" required>
        </div>

        <div>
          <label>Access Level</label>
          <select name="access_level">
            <option value="public">Public</option>
            <option value="private">Private</option>
            <option value="specific">Specific</option>
          </select>
        </div>

        <div class="full">
          <label>Description</label>
          <textarea name="description" rows="3"></textarea>
        </div>

        <div class="full">
          <label>Upload File</label>
          <div class="file-box">
            <input type="file" name="file" required>
            <span>PDF, DOCX, XLS, PNG, JPG</span>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit" name="upload">⬆ Upload Document</button>
      </div>
    </form>
  </div>

  <!-- DOCUMENT LIST -->
  <div class="panel">
    <h3 style="margin-bottom:16px">Uploaded Documents</h3>

    <?php if($documents): ?>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Uploaded By</th>
            <th>Access</th>
            <th>Date</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($documents as $d): ?>
            <tr>
              <td><?= e($d['title']) ?></td>
              <td><?= e($d['uploader']) ?></td>
              <td><span class="badge badge-<?= e($d['access_level']) ?>"><?= ucfirst($d['access_level']) ?></span></td>
              <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
              <td class="actions" style="text-align:right">
                <a class="btn-ghost" href="../uploads/documents/<?= e($d['file_path']) ?>" download>Download</a>
                <a class="btn-ghost" href="?delete_id=<?= $d['id'] ?>" onclick="return confirm('Delete this document?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div style="text-align:center;padding:50px;color:var(--muted)">
        📁 No documents uploaded yet
      </div>
    <?php endif; ?>
  </div>

  <footer>© <?= date('Y') ?> CloudConnect — Built with care</footer>
</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
