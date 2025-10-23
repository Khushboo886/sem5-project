<?php
require_once '../includes/session.php';
requireAdmin(); // only admin access
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/admin_sidebar.php';

$errors = [];
$success = '';

// Handle new announcement form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$title || !$content) {
        $errors[] = "Both title and content are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO announcements (company_id, title, content, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['company_id'], $title, $content]);
            $success = "Announcement published successfully!";
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// Fetch all announcements
$stmt = $pdo->prepare("SELECT id, title, content, created_at FROM announcements WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['company_id']]);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content" style="margin-left:250px; padding:20px;">
  <h2>Admin Dashboard &gt; Announcements</h2>

  <div class="card" style="padding:20px; margin-top:20px; box-shadow:0 2px 6px rgba(0,0,0,0.1); border-radius:10px;">

    <?php if ($errors): ?>
      <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Create New Announcement -->
    <h4>Create New Announcement</h4>
    <form method="post" class="mb-4">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" class="form-control" rows="4" required></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Publish</button>
    </form>

    <hr>

    <!-- Previous Announcements -->
    <h4 class="mt-4">Previous Announcements</h4>
    <?php if ($announcements): ?>
      <ul class="list-group mt-3">
        <?php foreach ($announcements as $a): ?>
          <li class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row">
            <div>
              <strong><?= htmlspecialchars($a['title']) ?></strong>
              <div class="small text-muted mt-1"><?= nl2br(htmlspecialchars($a['content'])) ?></div>
            </div>
            <span class="text-muted mt-2 mt-md-0"><?= date('d M Y, h:i A', strtotime($a['created_at'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted mt-3">No announcements yet.</p>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
