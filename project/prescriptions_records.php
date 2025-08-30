<?php include 'header.php'; ?>
<?php include "db.php"; if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Prescriptions - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .add-form {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin: 20px 0;
        }
        .add-form table {
            width: 100%;
            margin-bottom: 15px;
        }
        .add-form td {
            padding: 8px 0;
        }
        .add-form input,
        .add-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .action-btn {
            padding: 6px 12px;
            font-size: 13px;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .action-btn.view {
            color: white;
            background: #2980b9;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Prescription Records</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    $doctor_id = null;

    if ($role == 'doctor') {
        $res = mysqli_query($conn, "SELECT doctor_id FROM doctors WHERE user_id = $user_id");
        if (mysqli_num_rows($res) == 1) {
            $doctor_id = mysqli_fetch_assoc($res)['doctor_id'];
        }
    }

    // DOCTOR: Add Prescription
    // --------------------------
    if ($role == 'doctor' && isset($_POST['add_prescription'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
        $drug_id = intval($_POST['drug_id']);
        $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
        $duration = mysqli_real_escape_string($conn, $_POST['duration']);

        // Verify ownership
        $check = mysqli_query($conn, "SELECT 1 FROM appointments a 
                                      WHERE a.appointment_id = $appointment_id 
                                      AND a.doctor_id = $doctor_id");
        if (mysqli_num_rows($check) == 1 && !empty($diagnosis) && $drug_id > 0) {
            mysqli_query($conn, "INSERT INTO prescriptions_records (appointment_id, diagnosis, drug_id, dosage, duration)
                                VALUES ($appointment_id, '$diagnosis', $drug_id, '$dosage', '$duration')");
            echo "<div class='msg success'>Prescription added successfully!</div>";
        } else {
            echo "<div class='msg error'>Invalid data or unauthorized access.</div>";
        }
    }

    // DOCTOR: Show Add Form Toggle
    // --------------------------
    $show_form = ($role == 'doctor' && isset($_GET['add']));
    if ($show_form) echo "<script>document.getElementById('addForm').style.display='block';</script>";

    if ($role == 'doctor'):
    ?>
        <button id="toggleBtn" class="btn" style="margin:15px 0;" 
                onclick="toggleForm()">➕ <?php echo $show_form ? 'Hide Form' : 'Add Prescription'; ?></button>

        <div id="addForm" style="display: <?php echo $show_form ? 'block' : 'none'; ?>;">
            <div class="add-form">
                <h3>➕ Add Prescription</h3>
                <form method="post">
                    <table>
                        <tr>
                            <td><strong>Appointment *</strong></td>
                            <td>
                                <select name="appointment_id" required>
                                    <option value="">-- Select Appointment --</option>
                                    <?php
                                    $appts = mysqli_query($conn, "SELECT a.appointment_id, s.name AS student_name
                                                                  FROM appointments a
                                                                  JOIN students s ON a.student_id = s.student_id
                                                                  WHERE a.doctor_id = $doctor_id AND a.status = 'completed'
                                                                  ORDER BY a.datetime DESC");
                                    while ($a = mysqli_fetch_assoc($appts)) {
                                        echo "<option value='{$a['appointment_id']}'>#{$a['appointment_id']} - {$a['student_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Diagnosis *</strong></td>
                            <td><input type="text" name="diagnosis" required></td>
                        </tr>
                        <tr>
                            <td><strong>Drug *</strong></td>
                            <td>
                                <select name="drug_id" required>
                                    <option value="">-- Select Drug --</option>
                                    <?php
                                    $drugs = mysqli_query($conn, "SELECT * FROM drugs ORDER BY drug_name");
                                    while ($d = mysqli_fetch_assoc($drugs)) {
                                        echo "<option value='{$d['drug_id']}'>{$d['drug_name']} ({$d['dosage']})</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Dosage</td>
                            <td><input type="text" name="dosage" placeholder="e.g., 1 tablet"></td>
                        </tr>
                        <tr>
                            <td>Duration</td>
                            <td><input type="text" name="duration" placeholder="e.g., 7 days"></td>
                        </tr>
                    </table>
                    <input type="submit" name="add_prescription" value="Save Prescription" class="btn">
                    <!-- <a href="prescriptions_records.php" class="btn" style="background:#95a5a6">Cancel</a> -->
                    <a href="prescriptions_records.php" class="btn" style="display: block; margin: 0 auto; 
                            background: #cfefffff; color: #2d1616ff; padding: 6px 10px; 
                            border-radius: 8px; font-weight: 500; text-align: center; max-width: 200px; 
                            transition: background 0.3s ease;">Cancel
                    </a>

                </form>
            </div>
        </div>

        <script>
            function toggleForm() {
                const form = document.getElementById('addForm');
                const isHidden = form.style.display === 'none';
                form.style.display = isHidden ? 'block' : 'none';
                document.getElementById('toggleBtn').innerText = isHidden ? '➖ Hide Form' : '➕ Add Prescription';
            }
        </script>
    <?php endif; ?>

    <!-- Prescription Table -->
    <h3 class="section-title">📋 All Prescriptions</h3>
    <table>
        <thead>
            <tr>
                <th>Appointment</th>
                <th>Student</th>
                <th>Diagnosis</th>
                <th>Drug Count</th>
                <th>Prescribed On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query: One row per appointment
            $sql = "SELECT 
                        a.appointment_id,
                        s.name AS student_name,
                        pr.diagnosis,
                        COUNT(pr.record_id) AS drug_count,
                        MIN(pr.created_at) AS created_at
                    FROM prescriptions_records pr
                    JOIN appointments a ON pr.appointment_id = a.appointment_id
                    JOIN students s ON a.student_id = s.student_id";

            // Role-based filtering
            if ($role == 'student') {
                $res = mysqli_query($conn, "SELECT student_id FROM students WHERE user_id = $user_id");
                if (mysqli_num_rows($res) == 1) {
                    $sid = mysqli_fetch_assoc($res)['student_id'];
                    $sql .= " WHERE a.student_id = $sid";
                }
            } elseif ($role == 'doctor' && $doctor_id) {
                $sql .= " WHERE a.doctor_id = $doctor_id";
            }

            $sql .= " GROUP BY a.appointment_id, s.name, pr.diagnosis
                      ORDER BY created_at DESC";

            $res = mysqli_query($conn, $sql);
            if (mysqli_num_rows($res) == 0) {
                echo "<tr><td colspan='6'>No prescriptions found.</td></tr>";
            } else {
                while ($row = mysqli_fetch_assoc($res)) {
                    $date = date('M j, Y', strtotime($row['created_at']));
                    echo "<tr>
                            <td>#{$row['appointment_id']}</td>
                            <td>{$row['student_name']}</td>
                            <td>{$row['diagnosis']}</td>
                            <td>{$row['drug_count']}</td>
                            <td>$date</td>
                            <td class='actions'>
                                <a href='view_prescription.php?id={$row['appointment_id']}' 
                                   class='action-btn view'>View</a>
                            </td>
                          </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>