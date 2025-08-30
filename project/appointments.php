<?php include 'header.php'; ?>
<?php include "db.php"; if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Appointments - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 8px;
            font-size: 13px;
            font-weight: bold;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-completed { background: #ccff00ff; }
        .btn-cancelled { background: #97ff74ff; }

        .prescribe-form {
            margin: 15px 0;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .prescribe-form h4 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .prescribe-form table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .prescribe-form th, .prescribe-form td {
            padding: 8px;
            text-align: left;
        }
        .prescribe-form input,
        .prescribe-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .add-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
        }
        .status-scheduled { background: #5a99e7ff; }
        .status-completed { background: #27ae60; }
        .status-cancelled { background: #e74c3c; }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 Appointments</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    $role = $_SESSION['role'];
    $login_user_id = $_SESSION['user_id'];
    $student_id = $doctor_id = null;

    // Resolve IDs
    if ($role == 'student') {
        $res = mysqli_query($conn, "SELECT student_id FROM students WHERE user_id = $login_user_id");
        if (mysqli_num_rows($res)) $student_id = mysqli_fetch_assoc($res)['student_id'];
        else die("<div class='msg error'>Student profile not found.</div>");
    }

    if ($role == 'doctor') {
        $res = mysqli_query($conn, "SELECT doctor_id FROM doctors WHERE user_id = $login_user_id");
        if (mysqli_num_rows($res)) $doctor_id = mysqli_fetch_assoc($res)['doctor_id'];
        else die("<div class='msg error'>Doctor profile not found.</div>");
    }

    // ADMIN: Delete Appointment
    // --------------------------
    if ($role == 'admin' && isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        mysqli_query($conn, "DELETE FROM appointments WHERE appointment_id = $id");
        echo "<div class='msg success'>Appointment deleted.</div>";
    }

    // DOCTOR: Cancel → Delete
    // --------------------------
    if ($role == 'doctor' && isset($_GET['cancel'])) {
        $id = intval($_GET['cancel']);
        $own = mysqli_query($conn, "SELECT 1 FROM appointments WHERE appointment_id = $id AND doctor_id = $doctor_id");
        if (mysqli_num_rows($own)) {
            mysqli_query($conn, "DELETE FROM appointments WHERE appointment_id = $id");
            echo "<div class='msg success'>Appointment cancelled and deleted.</div>";
        } else {
            echo "<div class='msg error'>Access denied.</div>";
        }
    }

    // DOCTOR: Save Prescription & Mark as Completed
    // --------------------------
    if ($role == 'doctor' && isset($_POST['prescribe'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
        $drug_ids = $_POST['drug_id'] ?? [];
        $dosages = $_POST['dosage'] ?? [];
        $durations = $_POST['duration'] ?? [];

        // Verify ownership
        $check = mysqli_query($conn, "SELECT 1 FROM appointments WHERE appointment_id = $appointment_id AND doctor_id = $doctor_id");
        if (mysqli_num_rows($check) != 1 || empty($diagnosis)) {
            echo "<div class='msg error'>Invalid appointment or missing diagnosis.</div>";
        } else {
            // Mark appointment as completed
            mysqli_query($conn, "UPDATE appointments SET status = 'completed' WHERE appointment_id = $appointment_id");

            // Insert each drug
            $valid = true;
            for ($i = 0; $i < count($drug_ids); $i++) {
                $drug_id = intval($drug_ids[$i]);
                $dosage = mysqli_real_escape_string($conn, $dosages[$i]);
                $duration = mysqli_real_escape_string($conn, $durations[$i]);

                if ($drug_id > 0 && !empty($dosage)) {
                    $sql = "INSERT INTO prescriptions_records (appointment_id, diagnosis, drug_id, dosage, duration)
                            VALUES ($appointment_id, '$diagnosis', $drug_id, '$dosage', '$duration')";
                    mysqli_query($conn, $sql);
                }
            }

            echo "<div class='msg success'>Appointment completed and prescription saved!</div>";
        }
    }



    // STUDENT: Book Appointment
    // --------------------------
    if ($role == 'student' && isset($_POST['add'])) {
        $doc_id = intval($_POST['doctor_id']);
        $datetime = mysqli_real_escape_string($conn, $_POST['datetime']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);

        $d = mysqli_query($conn, "SELECT 1 FROM doctors WHERE doctor_id = $doc_id");
        if (mysqli_num_rows($d)) {
            mysqli_query($conn, "INSERT INTO appointments (student_id, doctor_id, datetime, reason, status)
                                VALUES ($student_id, $doc_id, '$datetime', '$reason', 'scheduled')");
            echo "<div class='msg success'>Appointment booked.</div>";
            // Redirect to hide form
            echo "<script>setTimeout(() => { window.location.href = 'appointments.php'; }, 800);</script>";
        } else {
            echo "<div class='msg error'>Doctor not found.</div>";
        }
    }

    // STUDENT: Book Form (hidden by default)
    // --------------------------
    if ($role == 'student') {
        ?>
        <h3 class="section-title">Appointments</h3>
        <!-- Button -->
        <button id="bookBtn" class="btn">➕ Book Appointment</button>

        <!-- Hidden Form -->
        <div id="bookForm" style="display:none; margin-top:20px;">
            <h3 class="section-title">➕ Book Appointment</h3>
            <form method="post">
                <table>
                    <tr>
                        <td>Doctor *</td>
                        <td>
                            <select name="doctor_id" required>
                                <option value="">-- Select --</option>
                                <?php
                                $res = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");
                                while ($d = mysqli_fetch_assoc($res)) {
                                    echo "<option value='{$d['doctor_id']}'>{$d['name']} ({$d['specialization']})</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Date & Time *</td>
                        <td><input type="datetime-local" name="datetime" required></td>
                    </tr>
                    <tr>
                        <td>Reason *</td>
                        <td><input type="text" name="reason" required></td>
                    </tr>
                </table>
                <input type="submit" name="add" value="Book Appointment" class="btn">
                <button type="button" onclick="document.getElementById('bookForm').style.display='none';" 
                        style="margin-left: 10px; background: #95a5a6; border: none; padding: 8px 12px; color: white; border-radius: 6px;">
                    Cancel
                </button>
            </form>
        </div>

        <script>
            document.getElementById('bookBtn').onclick = function() {
                const form = document.getElementById('bookForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            };
        </script>
        <hr>
        <?php
    }

    //  List Appointments
    // --------------------------
    echo "<h3 class='section-title'>📋 Appointments</h3>";
    echo "<table>";
    echo "<thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Doctor</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
          </thead><tbody>";

    $sql = "SELECT a.*, s.name AS student_name, d.name AS doctor_name 
            FROM appointments a
            JOIN students s ON a.student_id = s.student_id
            JOIN doctors d ON a.doctor_id = d.doctor_id";

    if ($role == 'student') $sql .= " WHERE a.student_id = $student_id";
    elseif ($role == 'doctor') $sql .= " WHERE a.doctor_id = $doctor_id";

    $sql .= " ORDER BY a.datetime DESC";

    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $status_class = 'status-' . $row['status'];
        echo "<tr>
                <td>{$row['appointment_id']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['doctor_name']}</td>
                <td>" . date('M j, Y H:i', strtotime($row['datetime'])) . "</td>
                <td>{$row['reason']}</td>
                <td><span class='status-badge $status_class'>" . ucfirst($row['status']) . "</span></td>
                <td class='actions'>";

        if ($role == 'doctor' && $row['status'] == 'scheduled') {
            echo "<a href='appointments.php?complete={$row['appointment_id']}' class='action-btn btn-completed'>Complete</a>";
            echo "<a href='appointments.php?cancel={$row['appointment_id']}' 
                   class='action-btn btn-cancelled'
                   onclick=\"return confirm('Delete permanently?')\">Cancel</a>";

            // Prescription Form
            if (isset($_GET['complete']) && intval($_GET['complete']) == $row['appointment_id']) {
                echo "</td></tr><tr><td colspan='7'>";
                ?>
                <div class="prescribe-form">
                    <h4>💊 Prescribe Treatment</h4>
                    <form method="post">
                        <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                        <table id="drug-table">
                            <tr>
                                <th>Diagnosis *</th>
                                <th>Drug *</th>
                                <th>Dosage</th>
                                <th>Duration</th>
                                <th>Action</th>
                            </tr>
                            <tr>
                                <td rowspan="99" style="width:25%">
                                    <input type="text" name="diagnosis" placeholder="e.g., Flu, Infection" required style="height:60px">
                                </td>
                                <td>
                                    <select name="drug_id[]" required>
                                        <option value="">-- Drug --</option>
                                        <?php
                                        $drugs = mysqli_query($conn, "SELECT * FROM drugs ORDER BY drug_name");
                                        while ($d = mysqli_fetch_assoc($drugs)) {
                                            echo "<option value='{$d['drug_id']}'>{$d['drug_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td><input type="text" name="dosage[]" placeholder="e.g., 1 tablet" required></td>
                                <td><input type="text" name="duration[]" placeholder="e.g., 5 days"></td>
                                <td><button type="button" class="remove-btn" onclick="removeRow(this)">✕</button></td>
                            </tr>
                        </table>
                        <button type="button" class="add-btn" onclick="addDrugRow()">+ Add Another Drug</button>
                        <div style="margin-top: 15px;">
                            <input type="submit" name="prescribe" value="Save & Complete" class="btn">
                            <a href="appointments.php" class="btn" style="display: block; margin: 0 auto; 
                                    background: #cfefffff; color: #2d1616ff; padding: 6px 10px; 
                                    border-radius: 8px; font-weight: 500; text-align: center; max-width: 200px; 
                                    transition: background 0.3s ease;">Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <script>
                    function addDrugRow() {
                        const table = document.getElementById("drug-table");
                        const newRow = table.insertRow();
                        newRow.innerHTML = `
                            <td>
                                <select name="drug_id[]" required>
                                    <option value="">-- Drug --</option>
                                    <?php
                                    $drugs = mysqli_query($conn, "SELECT * FROM drugs ORDER BY drug_name");
                                    while ($d = mysqli_fetch_assoc($drugs)) {
                                        echo "<option value='{$d['drug_id']}'>{$d['drug_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="text" name="dosage[]" placeholder="e.g., 1 tablet" required></td>
                            <td><input type="text" name="duration[]" placeholder="e.g., 5 days"></td>
                            <td><button type="button" class="remove-btn" onclick="removeRow(this)">✕</button></td>
                        `;
                    }
                    function removeRow(button) {
                        const row = button.closest("tr");
                        if (document.querySelectorAll("#drug-table tr").length > 2) {
                            row.remove();
                        }
                    }
                </script>
                <?php
            }
        }

        if ($role == 'admin') {
            echo "<a href='appointments.php?delete={$row['appointment_id']}' class='delete-link'>Delete</a>";
        }

        echo "</td></tr>";
    }
    echo "</tbody></table>";
    ?>
</div>
</body>
</html>

