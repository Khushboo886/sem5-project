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
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ? AND company_id = ?");
        $stmt->execute([$deleteId, $_SESSION['company_id']]);
        $doc = $stmt->fetch();

        if ($doc) {
            $filePath = '../uploads/documents/' . $doc['file_path'];
            if (file_exists($filePath)) unlink($filePath);

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

    if (empty($title)) $errors[] = "Document title is required.";
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        $errors[] = "Please select a valid file to upload.";

    if (empty($errors)) {
        try {
            $file = $_FILES['file'];
            $uploadDir = '../uploads/documents/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $stmt = $pdo->prepare("INSERT INTO documents (title, description, file_path, file_type, uploaded_by, company_id, access_level, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description ?: null, $fileName, $fileType, $uploaded_by, $company_id, $access_level, $created_at]);
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

<!-- ✅ Inline CSS -->
<style>
    .documents-container {
        background: #ffffff;
        padding: 30px;
        margin: 20px auto;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-width: 95%;
    }

    .admin-dashboard-title {
        font-size: 26px;
        font-weight: 600;
        color: #333;
        margin-bottom: 25px;
        text-align: center;
    }

    form {
        margin-bottom: 30px;
    }

    label {
        font-weight: 600;
        color: #444;
    }

    input[type="text"],
    textarea,
    input[type="file"],
    select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        margin-top: 4px;
    }

    .btn-primary {
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        cursor: pointer;
        transition: 0.2s ease-in-out;
    }

    .btn-primary:hover {
        background: #0b5ed7;
    }

    .alert {
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .alert-success {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .alert-danger {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    hr {
        margin: 25px 0;
        border: none;
        border-top: 1px solid #ddd;
    }

    /* Documents Table */
    .documents-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .documents-table th,
    .documents-table td {
        border: 1px solid #ddd;
        padding: 12px 14px;
        text-align: left;
        vertical-align: middle;
    }

    .documents-table th {
        background: linear-gradient(180deg, #0b1220, #17202a); /* Same as sidebar */
        color: #e6eef8;
        font-weight: 600;
    }

    .documents-table tr:hover {
        background-color: #f1f5ff;
    }

    .documents-table td {
        color: #333;
        font-size: 14.5px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 13px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
    }

    .btn-success {
        background: #28a745;
        color: #fff;
    }

    .btn-danger {
        background: #dc3545;
        color: #fff;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .no-data {
        text-align: center;
        font-style: italic;
        color: #777;
        padding: 20px;
    }
</style>

<main class="cc-main">
    <div class="documents-container">
        <h1 class="admin-dashboard-title">DOCUMENTS</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Upload New Document -->
        <h4>Upload New Document</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Document Title</label>
                <input type="text" name="title" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label>File Upload</label>
                <input type="file" name="file" required>
            </div>

            <div class="mb-3">
                <label>Access Level</label>
                <select name="access_level">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                    <option value="restricted">Restricted</option>
                </select>
            </div>

            <button class="btn-primary" type="submit" name="upload">Upload</button>
        </form>

        <hr>

        <!-- Shared Documents List -->
        <h4>Shared Documents List</h4>
        <?php if ($documents): ?>
            <table class="documents-table">
                <thead>
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
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['title']) ?></td>
                            <td><?= htmlspecialchars($doc['description']) ?></td>
                            <td><?= htmlspecialchars($doc['uploader_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($doc['access_level'])) ?></td>
                            <td><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                            <td>
                                <a class="btn-sm btn-success" href="../uploads/documents/<?= htmlspecialchars($doc['file_path']) ?>" download>Download</a>
                                <a class="btn-sm btn-danger" href="?delete_id=<?= $doc['id'] ?>" onclick="return confirm('Are you sure you want to delete this document?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No documents uploaded yet.</p>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>