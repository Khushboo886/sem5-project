<?php
require_once '../includes/session.php';
requireAdmin(); // Only admin can access
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/admin_sidebar.php';

$errors = [];
$success = '';

/* ------------------------------------------------------
   HANDLE DELETE DOCUMENT
------------------------------------------------------ */
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    try {
        // Get file path from DB
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ? AND company_id = ?");
        $stmt->execute([$deleteId, $_SESSION['company_id']]);
        $doc = $stmt->fetch();

        if ($doc) {
            $filePath = '../uploads/documents/' . $doc['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath); // Delete physical file
            }

            // Delete record from DB
            $del = $pdo->prepare("DELETE FROM documents WHERE id = ? AND company_id = ?");
            $del->execute([$deleteId, $_SESSION['company_id']]);

            $success = "Document deleted successfully!";
        } else {
            $errors[] = "Document not found or unauthorized.";
        }
    } catch (Exception $e) {
        $errors[] = "Error deleting document: " . $e->getMessage();
    }
}

/* ------------------------------------------------------
   HANDLE FILE UPLOAD
------------------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $access_level = trim($_POST['access_level'] ?? 'public');
    $uploaded_by = $_SESSION['user_id'];
    $company_id = $_SESSION['company_id'];
    $created_at = date('Y-m-d H:i:s');

    if (empty($title)) {
        $errors[] = "Document title is required.";
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please select a valid file to upload.";
    }

    if (empty($errors)) {
        try {
            $file = $_FILES['file'];
            $uploadDir = '../uploads/documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $stmt = $pdo->prepare("INSERT INTO documents (title, description, file_path, file_type, uploaded_by, company_id, access_level, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title,
                    $description ?: null,
                    $fileName,
                    $fileType,
                    $uploaded_by,
                    $company_id,
                    $access_level,
                    $created_at
                ]);
                $success = "Document uploaded successfully!";
            } else {
                $errors[] = "Failed to upload the file.";
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

/* ------------------------------------------------------
   FETCH DOCUMENTS LIST
------------------------------------------------------ */
$stmt = $pdo->prepare("SELECT d.*, u.name AS uploader_name 
                       FROM documents d 
                       LEFT JOIN users u ON d.uploaded_by = u.id 
                       WHERE d.company_id = ? 
                       ORDER BY d.created_at DESC");
$stmt->execute([$_SESSION['company_id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content p-4">
  <h2 class="mb-4">ADMIN DASHBOARD > DOCUMENTS</h2>
  <hr>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <!-- Upload New Document -->
  <div class="card mb-4 p-3">
    <h4>Upload New Document</h4>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Document Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">File Upload</label>
        <input type="file" name="file" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Access Level</label>
        <select name="access_level" class="form-select">
          <option value="public">Public</option>
          <option value="private">Private</option>
          <option value="restricted">Restricted</option>
        </select>
      </div>

      <button class="btn btn-primary" type="submit" name="upload">Upload</button>
    </form>
  </div>

  <!-- Shared Documents List -->
  <div class="card p-3">
    <h4>Shared Documents List</h4>
    <table class="table table-bordered mt-3">
      <thead class="table-light">
        <tr>
          <th>Document Name</th>
          <th>Description</th>
          <th>Uploaded By</th>
          <th>Access Level</th>
          <th>Uploaded On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($documents): ?>
          <?php foreach ($documents as $doc): ?>
            <tr>
              <td><?= htmlspecialchars($doc['title']) ?></td>
              <td><?= htmlspecialchars($doc['description']) ?></td>
              <td><?= htmlspecialchars($doc['uploader_name']) ?></td>
              <td><?= htmlspecialchars(ucfirst($doc['access_level'])) ?></td>
              <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
              <td>
                <a class="btn btn-sm btn-success" href="../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" download>Download</a>
                <a class="btn btn-sm btn-danger" href="?delete_id=<?= $doc['id'] ?>" onclick="return confirm('Are you sure you want to delete this document?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No documents uploaded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>