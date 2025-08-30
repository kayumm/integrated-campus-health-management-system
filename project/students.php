<?php include 'header.php'; ?>
<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Students - ICHMS</title>
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
        .edit-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .edit-btn:hover {
            background: #229954;
        }
        .section-title {
            color: #2980b9;
            border-bottom: 2px solid #3498db;
            padding-bottom: 6px;
            margin-top: 20px;
        }
        .msg.success {
            padding: 10px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🎓 Students</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php

    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $role = $_SESSION['role'];
    $login_user_id = $_SESSION['user_id'];
    $current_student_id = null;

    // Resolve current user's student_id
    $res = mysqli_query($conn, "SELECT student_id FROM students WHERE user_id = $login_user_id");
    if ($res && mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        $current_student_id = intval($row['student_id']);
    } else {
        // echo "<div class='msg error'>Student profile not found. Contact admin.</div>";
        // exit;
    }

    // ADMIN: Delete & Update
    // --------------------------
    if ($role === 'admin') {
        // Delete Student
        if (isset($_GET['delete'])) {
            $stud_id = intval($_GET['delete']);
            $res = mysqli_query($conn, "SELECT user_id FROM students WHERE student_id = $stud_id");
            if ($row = mysqli_fetch_assoc($res)) {
                $student_user_id = intval($row['user_id']);
                mysqli_query($conn, "DELETE FROM users WHERE user_id = $student_user_id");
            }
            echo "<div class='msg success'>Student deleted.</div>";
            echo "<script>setTimeout(() => { window.location.href = 'students.php'; }, 800);</script>";
            exit;
        }

        // Update Student
        if (isset($_POST['update'])) {
            $id = intval($_POST['student_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $dob = !empty($_POST['dob']) ? "'".mysqli_real_escape_string($conn, $_POST['dob'])."'" : "NULL";
            $gender = !empty($_POST['gender']) ? "'".mysqli_real_escape_string($conn, $_POST['gender'])."'" : "NULL";
            $department = mysqli_real_escape_string($conn, trim($_POST['department']));
            $program = mysqli_real_escape_string($conn, trim($_POST['program']));
            $year = max(1, min(6, intval($_POST['year_level'])));
            $contact = mysqli_real_escape_string($conn, trim($_POST['contact']));

            $sql = "UPDATE students SET 
                        name='$name', 
                        dob=$dob, 
                        gender=$gender, 
                        department='$department',
                        program='$program', 
                        year_level=$year, 
                        contact='$contact'
                    WHERE student_id = $id";

            mysqli_query($conn, $sql);
            echo "<div class='msg success'>Student updated successfully.</div>";
            echo "<script>setTimeout(() => { window.location.href = 'students.php'; }, 800);</script>";
            exit;
        }

        // Show Edit Form
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            $res = mysqli_query($conn, "SELECT * FROM students WHERE student_id = $id");
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
                ?>
                <h3 class="section-title">✏️ Edit Student</h3>
                <form method="post">
                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                    <table>
                        <tr>
                            <td>Name *</td>
                            <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required></td>
                        </tr>
                        <tr>
                            <td>DOB</td>
                            <td><input type="date" name="dob" value="<?php echo $row['dob']; ?>"></td>
                        </tr>
                        <tr>
                            <td>Gender</td>
                            <td>
                                <select name="gender">
                                    <option value="">--</option>
                                    <option value="M" <?php if($row['gender']=='M') echo "selected"; ?>>Male</option>
                                    <option value="F" <?php if($row['gender']=='F') echo "selected"; ?>>Female</option>
                                    <option value="O" <?php if($row['gender']=='O') echo "selected"; ?>>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td><input type="text" name="department" value="<?php echo htmlspecialchars($row['department']); ?>"></td>
                        </tr>
                        <tr>
                            <td>Program</td>
                            <td><input type="text" name="program" value="<?php echo htmlspecialchars($row['program']); ?>"></td>
                        </tr>
                        <tr>
                            <td>Year Level</td>
                            <td>
                                <input type="number" name="year_level" value="<?php echo $row['year_level']; ?>" min="1" max="6">
                            </td>
                        </tr>
                        <tr>
                            <td>Contact</td>
                            <td><input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>"></td>
                        </tr>
                    </table>
                    <div style="margin: 15px 0;">
                        <input type="submit" name="update" value="Update Student" class="btn">
                        <a href="students.php" style="margin-left: 10px;">Cancel</a>
                    </div>
                </form>
                <hr>
                <?php
            }
        }

        // Full Table
        ?>
        <h3 class="section-title">📋 All Students</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Department</th>
                    <th>Program</th>
                    <th>Year</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM students ORDER BY name");
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr>
                            <td>{$row['student_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['dob']}</td>
                            <td>{$row['gender']}</td>
                            <td>{$row['department']}</td>
                            <td>{$row['program']}</td>
                            <td>{$row['year_level']}</td>
                            <td>{$row['contact']}</td>
                            <td class='actions'>
                                <a href='students.php?edit={$row['student_id']}'>Edit</a> | 
                                <a href='students.php?delete={$row['student_id']}' class='delete-link' 
                                   onclick=\"return confirm('Delete student?')\">Delete</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    <?php
    }

    // STUDENT: Own Profile
    // --------------------------
    elseif ($role === 'student') {
        // ✅ Handle update first
        if (isset($_POST['update'])) {
            $posted_id = intval($_POST['student_id']);

            // Debug: Uncomment to check values
            // error_log("Posted ID: $posted_id, Current Student ID: $current_student_id");

            if ($posted_id !== $current_student_id) {
                echo "<div class='msg error'>Error: Invalid student ID.</div>";
                exit;
            }

            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $dob = !empty($_POST['dob']) ? "'".mysqli_real_escape_string($conn, $_POST['dob'])."'" : "NULL";
            $gender = !empty($_POST['gender']) ? "'".mysqli_real_escape_string($conn, $_POST['gender'])."'" : "NULL";
            $department = mysqli_real_escape_string($conn, trim($_POST['department']));
            $program = mysqli_real_escape_string($conn, trim($_POST['program']));
            $year = max(1, min(6, intval($_POST['year_level'])));
            $contact = mysqli_real_escape_string($conn, trim($_POST['contact']));

            $sql = "UPDATE students SET 
                        name='$name', 
                        dob=$dob, 
                        gender=$gender, 
                        department='$department',
                        program='$program', 
                        year_level=$year, 
                        contact='$contact'
                    WHERE student_id = $current_student_id";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                echo "<div class='msg success'>Profile updated successfully.</div>";
                echo "<script>setTimeout(() => { window.location.href = 'students.php'; }, 800);</script>";
                exit;
            } else {
                echo "<div class='msg error'>Database error: " . mysqli_error($conn) . "</div>";
            }
        }

        // Fetch student data after any update
        $res = mysqli_query($conn, "SELECT * FROM students WHERE student_id = $current_student_id");
        if (!$res || mysqli_num_rows($res) !== 1) {
            echo "<div class='msg error'>Failed to load profile.</div>";
            exit;
        }
        $student = mysqli_fetch_assoc($res);
        ?>

        <h3 class="section-title">👤 My Profile</h3>

        <!-- View Mode -->
        <div class="profile-box">
            <div class="profile-row">
                <span class="profile-label">Name:</span>
                <span class="profile-value"><?php echo htmlspecialchars($student['name']); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">DOB:</span>
                <span class="profile-value"><?php echo $student['dob'] ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Gender:</span>
                <span class="profile-value"><?php echo $student['gender'] ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Department:</span>
                <span class="profile-value"><?php echo htmlspecialchars($student['department']); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Program:</span>
                <span class="profile-value"><?php echo htmlspecialchars($student['program']); ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Year Level:</span>
                <span class="profile-value"><?php echo $student['year_level'] ?: '-'; ?></span>
            </div>
            <div class="profile-row">
                <span class="profile-label">Contact:</span>
                <span class="profile-value"><?php echo htmlspecialchars($student['contact']); ?></span>
            </div>
        </div>

        <!-- Edit Button -->
        <button id="editBtn" class="edit-btn">✏️ Edit Profile</button>

        <!-- Edit Form -->
        <div id="editForm" style="display: none; margin-top: 20px;">
            <h3>✏️ Edit Your Profile</h3>
            <form method="post">
                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                <table>
                    <tr>
                        <td>Name *</td>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required></td>
                    </tr>
                    <tr>
                        <td>DOB</td>
                        <td><input type="date" name="dob" value="<?php echo $student['dob']; ?>"></td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td>
                            <select name="gender">
                                <option value="">--</option>
                                <option value="M" <?php if($student['gender']=='M') echo "selected"; ?>>Male</option>
                                <option value="F" <?php if($student['gender']=='F') echo "selected"; ?>>Female</option>
                                <option value="O" <?php if($student['gender']=='O') echo "selected"; ?>>Other</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td><input type="text" name="department" value="<?php echo htmlspecialchars($student['department']); ?>"></td>
                    </tr>
                    <tr>
                        <td>Program</td>
                        <td><input type="text" name="program" value="<?php echo htmlspecialchars($student['program']); ?>"></td>
                    </tr>
                    <tr>
                        <td>Year Level</td>
                        <td><input type="number" name="year_level" value="<?php echo $student['year_level']; ?>" min="1" max="6"></td>
                    </tr>
                    <tr>
                        <td>Contact</td>
                        <td><input type="text" name="contact" value="<?php echo htmlspecialchars($student['contact']); ?>"></td>
                    </tr>
                </table>
                <div style="margin: 15px 0;">
                    <input type="submit" name="update" value="Save Changes" class="btn">
                    <button type="button" onclick="document.getElementById('editForm').style.display='none';"
                            style="margin-left: 10px; background: #95a5a6; border: none; padding: 8px 12px; color: white; border-radius: 6px;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <script>
            document.getElementById('editBtn').onclick = function () {
                const form = document.getElementById('editForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            };
        </script>

        <?php
    } else {
        echo "<div class='msg error'>Access denied.</div>";
    }
    ?>
</div>
</body>
</html>