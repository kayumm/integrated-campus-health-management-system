<?php include 'header.php'; ?>
<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctors - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        .profile-row {
            display: flex;
            margin-bottom: 12px;
            justify-content: space-between;
        }
        .profile-label {
            font-weight: 500;
            color: #2c3e50;
            width: 120px;
        }
        .profile-value {
            flex: 1;
            color: #34495e;
        }
        .section-title {
            color: #2980b9;
            border-bottom: 2px solid #3498db;
            padding-bottom: 6px;
            margin-top: 20px;
        }
        .edit-form-table {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        .edit-form-table td {
            padding: 12px;
        }
        .edit-form-table input,
        .edit-form-table select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .edit-form-table .actions {
            text-align: right;
        }
        .edit-form-table .actions button,
        .edit-form-table .actions a {
            margin-left: 8px;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍⚕️ Doctors</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $role = $_SESSION['role'];
    $login_user_id = $_SESSION['user_id'];
    $current_doctor_id = null;

    // Resolve current doctor ID
    $res = mysqli_query($conn, "SELECT doctor_id FROM doctors WHERE user_id = $login_user_id");
    if (mysqli_num_rows($res) == 1) {
        $current_doctor_id = mysqli_fetch_assoc($res)['doctor_id'];
    }

    // ADMIN: Full Table + Edit/Delete
    // --------------------------
    if ($role === 'admin') {
        // Delete
        try {
            if (isset($_GET['delete'])) {
                $doctor_id = intval($_GET['delete']);

                // Find the linked user_id for this doctor
                $res = mysqli_query($conn, "SELECT user_id FROM doctors WHERE doctor_id = $doctor_id");
                if ($row = mysqli_fetch_assoc($res)) {
                    $doctor_user_id = intval($row['user_id']);

                    // Delete user (this will also delete doctor row if you used ON DELETE CASCADE)
                    mysqli_query($conn, "DELETE FROM users WHERE user_id = $doctor_user_id");
                    
                    echo "<div class='msg success'>Doctor deleted.</div>";
                }
            } 
        } catch (mysqli_sql_exception $e) {
                echo "<div class='msg error'>❌ Cannot delete doctor: Existing appointments are linked to this doctor.</div>";
        }

        // Update
        if (isset($_POST['update'])) {
            $id = intval($_POST['doctor_id']);
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
            $contact = mysqli_real_escape_string($conn, $_POST['contact']);
            $availability = mysqli_real_escape_string($conn, $_POST['availability']);

            $sql = "UPDATE doctors SET 
                        name='$name', 
                        specialization='$specialization', 
                        contact='$contact', 
                        availability='$availability'
                    WHERE doctor_id = $id";
            mysqli_query($conn, $sql);
            echo "<div class='msg success'>Doctor updated.</div>";
            // Redirect to hide form
            echo "<script>setTimeout(() => { window.location.href = 'doctors.php'; }, 750);</script>";
        }

        ?>
        <h3 class="section-title">📋 All Doctors</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Contact</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");
                while ($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr id="row-<?php echo $row['doctor_id']; ?>">
                        <td><?php echo $row['doctor_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['availability']); ?></td>
                        <td class="actions">
                            <a href="doctors.php?edit=<?php echo $row['doctor_id']; ?>">Edit</a> |
                            <a href="doctors.php?delete=<?php echo $row['doctor_id']; ?>" 
                               class="delete-link"
                               onclick="return confirm('Delete this doctor?')">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Form Row (shown only if ?edit=ID matches) -->
                    <?php if (isset($_GET['edit']) && intval($_GET['edit']) == $row['doctor_id']): ?>
                        <tr style="background: #e8f4fd; border-top: 2px solid #3498db;">
                            <td colspan="6">
                                <form method="post">
                                    <input type="hidden" name="doctor_id" value="<?php echo $row['doctor_id']; ?>">
                                    <table class="edit-form-table">
                                        <tr>
                                            <td style="width:120px"><strong>Name *</strong></td>
                                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td>Specialization</td>
                                            <td><input type="text" name="specialization" value="<?php echo htmlspecialchars($row['specialization']); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td>Contact</td>
                                            <td><input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td>Availability</td>
                                            <td><input type="text" name="availability" value="<?php echo htmlspecialchars($row['availability']); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="actions">
                                                <button type="submit" name="update" class="btn">Update</button>
                                                <a href="doctors.php">Cancel</a>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php } ?>
            </tbody>
        </table>
    <?php
    }

    // DOCTOR: View Own Profile Only
    // --------------------------
    elseif ($role === 'doctor' && $current_doctor_id) {
        $res = mysqli_query($conn, "SELECT * FROM doctors WHERE doctor_id = $current_doctor_id");
        $doc = mysqli_fetch_assoc($res);

        ?>
        <h3 class="section-title">👤 My Profile</h3>
        <div class="profile-box">
            <div class="profile-row">
                <span class="profile-label">Name:</span>
                <span class="profile-value"><?php echo htmlspecialchars($doc['name']); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Specialization:</span>
                <span class="profile-value"><?php echo htmlspecialchars($doc['specialization']) ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Contact:</span>
                <span class="profile-value"><?php echo htmlspecialchars($doc['contact']) ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Availability:</span>
                <span class="profile-value"><?php echo htmlspecialchars($doc['availability']) ?: '-'; ?></span>
            </div>
        </div>
    <?php
    }
    else {
        echo "<div class='msg error'>Access denied.</div>";
    }
    ?>
</div>
</body>
</html>


