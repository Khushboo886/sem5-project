<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Ensure only logged-in employees can access
requireEmployee();

// Get logged-in employee ID
$employeeId = $_SESSION['id'] ?? null;
if (!$employeeId) {
    die('User not logged in.');
}

// Fetch this employee's attendance records
try {
    $stmt = $pdo->prepare("
        SELECT date, check_in, check_out, status, remarks
        FROM attendance
        WHERE user_id = :user_id
        ORDER BY date DESC
    ");
    $stmt->execute(['user_id' => $employeeId]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Query failed: ' . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container mt-4">
    <h2>My Attendance Records</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($records && count($records) > 0): ?>
                    <?php foreach ($records as $row):
                        $statusClass = 'text-' . match(strtolower($row['status'])) {
                            'present' => 'success',
                            'late' => 'warning',
                            'absent' => 'danger',
                            'half-day' => 'secondary',
                            default => 'dark'
                        };
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['check_in']) ?></td>
                            <td><?= htmlspecialchars($row['check_out']) ?></td>
                            <td class="<?= $statusClass ?>"><?= ucfirst($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No attendance records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
