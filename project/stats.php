<?php include 'header.php'; ?>
<?php include "db.php"; if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>📊 Admin Statistics - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            text-align: center;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #2980b9;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 6px;
        }
        .section {
            margin: 25px 0;
        }
        .section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 6px;
            margin-bottom: 15px;
        }
        .low-stock-table, .common-diag-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .low-stock-table th, .low-stock-table td,
        .common-diag-table th, .common-diag-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .low-stock-table th, .common-diag-table th {
            background: #3498db;
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Admin Statistics</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    // Only allow admin
    if ($_SESSION['role'] !== 'admin') {
        echo "<div class='msg error'>Access denied. Admins only.</div>";
        exit;
    }

    // Total Counts
    $total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
    $total_doctors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM doctors"))['count'];
    $total_pharmacists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pharmacists"))['count'];
    $total_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
    $total_prescriptions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM prescriptions_records"))['count'];
    $total_drugs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM drugs"))['count'];
    ?>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_students; ?></div>
            <div class="stat-label">Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_pharmacists; ?></div>
            <div class="stat-label">Pharmacists</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_appointments; ?></div>
            <div class="stat-label">Appointments</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_prescriptions; ?></div>
            <div class="stat-label">Prescriptions</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_drugs; ?></div>
            <div class="stat-label">Drug Types</div>
        </div>
    </div>

    <!-- Low Stock Drugs -->
    <div class="section">
        <h3>📦 Low Stock Drugs (Qty ≤ 5)</h3>
        <?php
        $low_stock = mysqli_query($conn, "SELECT drug_name, brand, stock_qty FROM drugs WHERE stock_qty <= 5 ORDER BY stock_qty");
        if (mysqli_num_rows($low_stock) == 0) {
            echo "<p>All drugs are sufficiently stocked.</p>";
        } else {
            echo "<table class='low-stock-table'>
                    <tr>
                        <th>Drug Name</th>
                        <th>Brand</th>
                        <th>Stock Qty</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($low_stock)) {
                echo "<tr>
                        <td>{$row['drug_name']}</td>
                        <td>{$row['brand']}</td>
                        <td>{$row['stock_qty']}</td>
                      </tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <!-- Most Common Diagnoses -->
    <div class="section">
        <h3>🩺 Most Common Diagnoses</h3>
        <?php
        $diagnoses = mysqli_query($conn, "SELECT diagnosis, COUNT(*) as cnt FROM prescriptions_records GROUP BY diagnosis ORDER BY cnt DESC LIMIT 5");
        if (mysqli_num_rows($diagnoses) == 0) {
            echo "<p>No prescriptions recorded yet.</p>";
        } else {
            echo "<table class='common-diag-table'>
                    <tr>
                        <th>Diagnosis</th>
                        <th>Count</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($diagnoses)) {
                echo "<tr>
                        <td>{$row['diagnosis']}</td>
                        <td>{$row['cnt']}</td>
                      </tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <!-- Recent Activity -->
    <div class="section">
        <h3>🆕 Recent Prescriptions (Last 5)</h3>
        <?php
        $recent = mysqli_query($conn, "SELECT pr.diagnosis, d.drug_name, s.name AS student_name, pr.created_at 
                                       FROM prescriptions_records pr
                                       JOIN drugs d ON pr.drug_id = d.drug_id
                                       JOIN appointments a ON pr.appointment_id = a.appointment_id
                                       JOIN students s ON a.student_id = s.student_id
                                       ORDER BY pr.created_at DESC LIMIT 5");
        if (mysqli_num_rows($recent) == 0) {
            echo "<p>No recent prescriptions.</p>";
        } else {
            echo "<table class='common-diag-table'>
                    <tr>
                        <th>Student</th>
                        <th>Diagnosis</th>
                        <th>Drug</th>
                        <th>Date</th>
                    </tr>";
            while ($row = mysqli_fetch_assoc($recent)) {
                $date = date('M j, Y', strtotime($row['created_at']));
                echo "<tr>
                        <td>{$row['student_name']}</td>
                        <td>{$row['diagnosis']}</td>
                        <td>{$row['drug_name']}</td>
                        <td>{$date}</td>
                      </tr>";
            }
            echo "</table>";
        }
        ?>
    </div>
</div>
</body>
</html>

