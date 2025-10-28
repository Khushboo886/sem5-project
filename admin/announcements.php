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

<!-- ✅ Inline CSS -->
<style>
    .announcement-container {
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
        text-align: center;
        margin-bottom: 25px;
    }

    form {
        margin-bottom: 30px;
    }

    label {
        font-weight: 600;
        color: #444;
    }

    input[type="text"], textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
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

    hr {
        margin: 30px 0;
        border: none;
        border-top: 1px solid #ddd;
    }

    /* Announcement List */
    .announcement-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .announcement-table th,
    .announcement-table td {
        border: 1px solid #ddd;
        padding: 12px 14px;
        text-align: left;
        vertical-align: top;
    }

    .announcement-table th {
        background: linear-gradient(180deg, #0b1220, #17202a); /* Same as sidebar */
        color: #e6eef8;
        font-weight: 600;
    }

    .announcement-table tr:hover {
        background-color: #f1f5ff;
    }

    .announcement-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
    }

    .announcement-content {
        color: #555;
        font-size: 14px;
        margin-top: 4px;
        white-space: pre-wrap;
    }

    .announcement-date {
        font-size: 13px;
        color: #777;
        text-align: right;
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

    .no-data {
        text-align: center;
        font-style: italic;
        color: #777;
        padding: 20px;
    }
</style>

<main class="cc-main">
    <div class="announcement-container">
        <h1 class="admin-dashboard-title">ANNOUNCEMENTS</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Create New Announcement -->
        <h4>Create New Announcement</h4>
        <form method="post">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>

            <div class="mb-3">
                <label>Content</label>
                <textarea name="content" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn-primary">Publish</button>
        </form>

        <hr>

        <!-- Previous Announcements -->
        <h4>Previous Announcements</h4>
        <?php if ($announcements): ?>
            <table class="announcement-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $a): ?>
                        <tr>
                            <td class="announcement-title"><?= htmlspecialchars($a['title']) ?></td>
                            <td class="announcement-content"><?= nl2br(htmlspecialchars($a['content'])) ?></td>
                            <td class="announcement-date"><?= date('d M Y, h:i A', strtotime($a['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No announcements yet.</p>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>