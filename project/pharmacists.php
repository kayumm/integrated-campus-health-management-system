<?php include 'header.php'; ?>
<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Pharmacists - ICHMS</title>
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
    <h2>🧑‍🔬 Pharmacists</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $role = $_SESSION['role'];
    $login_user_id = $_SESSION['user_id'];
    $current_pharmacist_id = null;

    // Resolve current pharmacist ID
    $res = mysqli_query($conn, "SELECT pharmacist_id FROM pharmacists WHERE user_id = $login_user_id");
    if (mysqli_num_rows($res) == 1) {
        $current_pharmacist_id = mysqli_fetch_assoc($res)['pharmacist_id'];
    }

    //  ADMIN: Full Table + Edit/Delete
    // --------------------------
    if ($role === 'admin') {
        if (isset($_GET['delete'])) {
            $pharma_id = intval($_GET['delete']);

            $res = mysqli_query($conn, "SELECT user_id FROM pharmacists WHERE pharmacist_id= $pharma_id");
            if ($row = mysqli_fetch_assoc($res)) {
                $pharmacist_user_id = intval($row['user_id']);

                mysqli_query($conn, "DELETE FROM users WHERE user_id = $pharmacist_user_id");
                
                echo "<div class='msg success'>Pharmacist deleted.</div>";
            }
        }


        // Update
        if (isset($_POST['update'])) {
            $id = intval($_POST['pharmacist_id']);
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $contact = mysqli_real_escape_string($conn, $_POST['contact']);
            $shift = mysqli_real_escape_string($conn, $_POST['shift']);

            $sql = "UPDATE pharmacists SET 
                        name='$name', 
                        contact='$contact', 
                        shift='$shift'
                    WHERE pharmacist_id = $id";
            mysqli_query($conn, $sql);
            echo "<div class='msg success'>Pharmacist updated.</div>";
            // redirect to hide form
            echo "<script>setTimeout(() => { window.location.href = 'pharmacists.php'; }, 750);</script>";
        }

        ?>
        <h3 class="section-title">📋 All Pharmacists</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Shift</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM pharmacists ORDER BY name");
                while ($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr id="row-<?php echo $row['pharmacist_id']; ?>">
                        <td><?php echo $row['pharmacist_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['shift']); ?></td>
                        <td class="actions">
                            <a href="pharmacists.php?edit=<?php echo $row['pharmacist_id']; ?>">Edit</a> |
                            <a href="pharmacists.php?delete=<?php echo $row['pharmacist_id']; ?>" 
                               class="delete-link"
                               onclick="return confirm('Delete this pharmacist?')">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Form Row -->
                    <?php if (isset($_GET['edit']) && intval($_GET['edit']) == $row['pharmacist_id']): ?>
                        <tr style="background: #e8f4fd; border-top: 2px solid #3498db;">
                            <td colspan="5">
                                <form method="post">
                                    <input type="hidden" name="pharmacist_id" value="<?php echo $row['pharmacist_id']; ?>">
                                    <table class="edit-form-table">
                                        <tr>
                                            <td style="width:120px"><strong>Name *</strong></td>
                                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required></td>
                                        </tr>
                                        <tr>
                                            <td>Contact</td>
                                            <td><input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td>Shift</td>
                                            <td><input type="text" name="shift" value="<?php echo htmlspecialchars($row['shift']); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="actions">
                                                <button type="submit" name="update" class="btn">Update</button>
                                                <a href="pharmacists.php">Cancel</a>
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

    //  PHARMACIST: View Own Profile Only
    // --------------------------
    elseif ($role === 'pharmacist' && $current_pharmacist_id) {
        $res = mysqli_query($conn, "SELECT * FROM pharmacists WHERE pharmacist_id = $current_pharmacist_id");
        $pharm = mysqli_fetch_assoc($res);

        ?>
        <h3 class="section-title">👤 My Profile</h3>
        <div class="profile-box">
            <div class="profile-row">
                <span class="profile-label">Name:</span>
                <span class="profile-value"><?php echo htmlspecialchars($pharm['name']); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Contact:</span>
                <span class="profile-value"><?php echo htmlspecialchars($pharm['contact']) ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Shift:</span>
                <span class="profile-value"><?php echo htmlspecialchars($pharm['shift']) ?: '-'; ?></span>
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


