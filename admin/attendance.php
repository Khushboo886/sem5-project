<?php
require_once '../includes/session.php';
requireAdmin(); // Only admin can access this page
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

<!-- ✅ Updated Inline CSS -->
<style>
    .attendance-container {
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

    .filter-section input[type="date"],
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

    .btn-primary:hover {
        background: #0b5ed7;
    }

    /* ---------- Table Styling ---------- */
    .table-container {
        overflow-x: auto;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b8b8b8; /* visible border */
    }

    .attendance-table th,
    .attendance-table td {
        padding: 10px 12px;
        text-align: center;
        border: 1px solid #c2c2c2; /* visible grid lines */
    }

    .attendance-table th {
        background: linear-gradient(180deg, #0b1220, #17202a); /* match sidebar */
        color: #e6eef8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .attendance-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .attendance-table tr:hover {
        background-color: #eef3ff;
    }

    /* ---------- Status Badges ---------- */
    .status-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 500;
        color: white;
    }

    .status-present { background: #28a745; }
    .status-late { background: #ffc107; color: #333; }
    .status-absent { background: #dc3545; }
    .status-halfday { background: #17a2b8; }
    .status-other { background: #6c757d; }

    .no-data {
        text-align: center;
        font-style: italic;
        color: #777;
        padding: 20px;
    }

    @media (max-width: 768px) {
        .attendance-table th, .attendance-table td {
            font-size: 14px;
            padding: 8px;
        }
    }
</style>

<main class="cc-main">
    <div class="attendance-container">
        <h1 class="admin-dashboard-title">Attendance Records</h1>

        <div class="filter-section">
            <label for="date">Filter by Date:</label>
            <input type="date" id="date" name="date">

            <label for="employee">Filter by Employee:</label>
            <select id="employee">
                <option value="">All Employees</option>
                <?php
                $empStmt = $pdo->query("SELECT id, name FROM users ORDER BY name ASC");
                while ($emp = $empStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value=\"{$emp['id']}\">" . htmlspecialchars($emp['name']) . "</option>";
                }
                ?>
            </select>

            <button class="btn-primary">Apply Filters</button>
        </div>

        <div class="table-container">
            <table class="attendance-table">
                <thead>
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
                            <?php
                                $statusClass = match(strtolower($row['status'])) {
                                    'present' => 'status-present',
                                    'late' => 'status-late',
                                    'absent' => 'status-absent',
                                    'half-day' => 'status-halfday',
                                    default => 'status-other'
                                };
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['check_in']) ?></td>
                                <td><?= htmlspecialchars($row['check_out']) ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['remarks']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="no-data">No attendance records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>