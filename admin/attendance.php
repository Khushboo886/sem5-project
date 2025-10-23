<?php
require_once '../includes/session.php';
requireAdmin();  // Only admin can access this page
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/admin_sidebar.php';

// Fetch attendance records with employee names
try {
    $stmt = $pdo->prepare("
        SELECT u.name AS employee_name, 
               a.date, 
               a.check_in, 
               a.check_out, 
               a.status, 
               a.remarks
        FROM attendance a
        INNER JOIN users u ON a.user_id = u.id
        ORDER BY a.date DESC
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Query failed: ' . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>Attendance Records</h2>

    <div class="filter-section mb-3">
        <label for="date">Filter by Date:</label>
        <input type="date" id="date" name="date" class="form-control d-inline-block w-auto">

        <label for="employee" class="ms-3">Filter by Employee:</label>
        <select id="employee" class="form-control d-inline-block w-auto">
            <option value="">All Employees</option>
            <?php
            // Optional: populate employees dynamically
            $empStmt = $pdo->query("SELECT id, name FROM users ORDER BY name ASC");
            while ($emp = $empStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"{$emp['id']}\">" . htmlspecialchars($emp['name']) . "</option>";
            }
            ?>
        </select>

        <button class="btn btn-primary ms-3">Apply Filters</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records && count($records) > 0): ?>
                    <?php foreach ($records as $row): ?>
                        <?php $statusClass = 'text-' . match(strtolower($row['status'])) {
                            'present' => 'success',
                            'late' => 'warning',
                            'absent' => 'danger',
                            'half-day' => 'secondary',
                            default => 'dark'
                        }; ?>
                        <tr>
                            <td><?= htmlspecialchars($row['employee_name']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['check_in']) ?></td>
                            <td><?= htmlspecialchars($row['check_out']) ?></td>
                            <td class="<?= $statusClass ?>"><?= ucfirst($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No attendance records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
