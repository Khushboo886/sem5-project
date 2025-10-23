<?php
require_once '../includes/session.php';
requireAdmin(); // only admin access
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/admin_sidebar.php';

// Fetch leave data
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT l.id, u.name AS employee_name, l.start_date, l.end_date, l.reason, l.status 
          FROM leaves l
          JOIN users u ON l.user_id = u.id";

if ($statusFilter) {
    $query .= " WHERE l.status = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$statusFilter]);
} else {
    $stmt = $pdo->query($query);
}

$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content" style="margin-left:250px; padding:20px;">
  <h2>Admin Dashboard &gt; Leaves</h2>

  <div class="card" style="padding:20px; margin-top:20px; box-shadow:0 2px 6px rgba(0,0,0,0.1); border-radius:10px;">
    <form method="get" class="row g-3 align-items-center mb-4">
      <div class="col-auto">
        <label for="status" class="col-form-label"><strong>Filter by Status:</strong></label>
      </div>
      <div class="col-auto">
        <select name="status" id="status" class="form-select">
          <option value="">All</option>
          <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Approved" <?= $statusFilter == 'Approved' ? 'selected' : '' ?>>Approved</option>
          <option value="Rejected" <?= $statusFilter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Employee</th>
            <th>Date Range</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($leaves): ?>
            <?php foreach ($leaves as $leave): ?>
              <tr>
                <td><?= htmlspecialchars($leave['employee_name']) ?></td>
                <td><?= htmlspecialchars($leave['start_date']) ?> → <?= htmlspecialchars($leave['end_date']) ?></td>
                <td><?= htmlspecialchars($leave['reason']) ?></td>
                <td>
                  <?php if ($leave['status'] == 'Pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif ($leave['status'] == 'Approved'): ?>
                    <span class="badge bg-success">Approved</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($leave['status'] == 'Pending'): ?>
                    <a href="update_leave_status.php?id=<?= $leave['id'] ?>&status=Approved" class="btn btn-success btn-sm">Approve</a>
                    <a href="update_leave_status.php?id=<?= $leave['id'] ?>&status=Rejected" class="btn btn-danger btn-sm">Reject</a>
                  <?php else: ?>
                    <a href="view_leave.php?id=<?= $leave['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center">No leave requests found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>