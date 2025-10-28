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

<!-- ✅ Inline CSS: perfectly matching attendance.php style -->
<style>
    .main-content {
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

    .filter-section {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-section label {
        font-weight: 600;
        color: #444;
    }

    .filter-section select {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .btn-primary {
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        cursor: pointer;
        transition: 0.2s ease-in-out;
    }

    .btn-primary:hover { background: #0b5ed7; }

    .btn-success {
        background: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 6px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-success:hover { background: #218838; }

    .btn-danger {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 6px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-danger:hover { background: #c82333; }

    .btn-secondary {
        background: #6c757d;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 6px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-secondary:hover { background: #5a6268; }

    /* ✅ Table Styling - identical to attendance.php */
    .table-container {
        overflow-x: auto;
    }

    .leave-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b8b8b8;
    }

    .leave-table th,
    .leave-table td {
        padding: 10px 12px;
        text-align: center;
        border: 1px solid #c2c2c2;
    }

    .leave-table th {
        background: linear-gradient(180deg, #0b1220, #17202a);
        color: #e6eef8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .leave-table tr:nth-child(even) { background-color: #f9f9f9; }
    .leave-table tr:hover { background-color: #eef3ff; }

    .status-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 500;
        color: white;
    }

    .status-pending { background: #ffc107; color: #333; }
    .status-approved { background: #28a745; }
    .status-rejected { background: #dc3545; }

    .no-data {
        text-align: center;
        font-style: italic;
        color: #777;
        padding: 20px;
    }

    @media (max-width: 768px) {
        .filter-section { flex-direction: column; align-items: flex-start; }
        .leave-table th, .leave-table td { font-size: 14px; padding: 8px; }
    }
</style>

<!-- ✅ Updated HTML -->
<main class="cc-main">
    <div class="main-content">
        <h1 class="admin-dashboard-title">Leave Records</h1>

        <div class="filter-section">
            <form method="get">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $statusFilter == 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $statusFilter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                <button type="submit" class="btn-primary">Apply Filters</button>
            </form>
        </div>

        <div class="table-container">
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date Range</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leaves && count($leaves) > 0): ?>
                        <?php foreach ($leaves as $leave): ?>
                            <?php
                                $statusClass = match($leave['status']) {
                                    'Pending' => 'status-pending',
                                    'Approved' => 'status-approved',
                                    'Rejected' => 'status-rejected',
                                    default => ''
                                };
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($leave['employee_name']) ?></td>
                                <td><?= htmlspecialchars($leave['start_date']) ?> → <?= htmlspecialchars($leave['end_date']) ?></td>
                                <td><?= htmlspecialchars($leave['reason']) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($leave['status']) ?></span></td>
                                <td>
                                    <?php if ($leave['status'] == 'Pending'): ?>
                                        <a href="update_leave_status.php?id=<?= $leave['id'] ?>&status=Approved" class="btn-success">Approve</a>
                                        <a href="update_leave_status.php?id=<?= $leave['id'] ?>&status=Rejected" class="btn-danger">Reject</a>
                                    <?php else: ?>
                                        <a href="view_leave.php?id=<?= $leave['id'] ?>" class="btn-secondary">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="no-data">No leave requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
