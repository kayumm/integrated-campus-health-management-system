<?php include 'header.php'; ?>
<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>ICHMS - Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Center the form */
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #2c3e50;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            max-width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .role-fields {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px dashed #3498db;
            border-radius: 6px;
            display: none;
        }
        .role-fields h4 {
            color: #2980b9;
            margin-bottom: 12px;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        .btn:hover {
            background: #2980b9;
        }
        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
        .login-link a {
            color: #3498db;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>🔐 Create Account</h2>

    <?php
    if (isset($_POST['signup'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        // Validation
        if (empty($username)) {
            echo "<div class='msg error'>Username is required.</div>";
        } elseif (strlen($password) < 6) {
            echo "<div class='msg error'>Password must be at least 6 characters.</div>";
        } else {
            // Check if username exists
            $check = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$username'");
            if (mysqli_num_rows($check) > 0) {
                echo "<div class='msg error'>Username already taken.</div>";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into users
                $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
                if (mysqli_query($conn, $sql)) {
                    $user_id = mysqli_insert_id($conn);

                    // Insert into role-specific table
                    if ($role == 'student') {


                        $name = mysqli_real_escape_string($conn, $_POST['student_name'] ?? $username);
                        $dob = mysqli_real_escape_string($conn, $_POST['dob'] ?? '');
                        $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
                        $department = mysqli_real_escape_string($conn, $_POST['department'] ?? '');
                        $program = mysqli_real_escape_string($conn, $_POST['program'] ?? '');
                        $year_level = intval($_POST['year_level'] ?? 1);
                        $contact = mysqli_real_escape_string($conn, $_POST['student_contact'] ?? '');

                        $sql = "INSERT INTO students (user_id, name, dob, gender, department, program, year_level, contact)
                                VALUES ($user_id, '$name', '$dob', '$gender', '$department', '$program', $year_level, '$contact')";
                        mysqli_query($conn, $sql);
                    }

                    elseif ($role == 'doctor') {


                        $name = mysqli_real_escape_string($conn, $_POST['doctor_name'] ?? $username);
                        $specialization = mysqli_real_escape_string($conn, $_POST['specialization'] ?? '');
                        $contact = mysqli_real_escape_string($conn, $_POST['doctor_contact'] ?? '');
                        $availability = mysqli_real_escape_string($conn, $_POST['availability'] ?? '');

                        $sql = "INSERT INTO doctors (user_id, name, specialization, contact, availability)
                                VALUES ($user_id, '$name', '$specialization', '$contact', '$availability')";
                        mysqli_query($conn, $sql);
                    }

                    elseif ($role == 'pharmacist') {

                        $name = mysqli_real_escape_string($conn, $_POST['pharmacist_name'] ?? $username);
                        $contact = mysqli_real_escape_string($conn, $_POST['pharmacist_contact'] ?? '');
                        $shift = mysqli_real_escape_string($conn, $_POST['shift'] ?? '');

                        $sql = "INSERT INTO pharmacists (user_id, name, contact, shift)
                                VALUES ($user_id, '$name', '$contact', '$shift')";
                        mysqli_query($conn, $sql);
                    }

                    echo "<div class='msg success'>Signup successful! <a href='index.php'>Login now</a></div>";
                } else {
                    echo "<div class='msg error'>Error creating user: " . mysqli_error($conn) . "</div>";
                }
            }
        }
    }
    ?>

    <form method="post">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="role">Select Role *</label>
            <select name="role" id="role" required onchange="showRoleFields()">
                <option value="">-- Choose Role --</option>
                <option value="student">Student</option>
                <option value="doctor">Doctor</option>
                <option value="pharmacist">Pharmacist</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <!-- Dynamic Role Fields -->
        <div id="student-fields" class="role-fields">
            <h4>🎓 Student Info</h4>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="student_name" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>DOB</label>
                <input type="date" name="dob">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">-- Select --</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department">
            </div>
            <div class="form-group">
                <label>Program</label>
                <input type="text" name="program">
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <input type="number" name="year_level" min="1" max="6" value="1">
            </div>

            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="student_contact">
            </div>
        </div>

        <div id="doctor-fields" class="role-fields">
            <h4>👨‍⚕️ Doctor Info</h4>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="doctor_name" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Specialization</label>
                <input type="text" name="specialization">
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="doctor_contact">
            </div>
            <div class="form-group">
                <label>Availability</label>
                <input type="text" name="availability" placeholder="e.g., Mon-Fri, 9AM-5PM">
            </div>
        </div>

        <div id="pharmacist-fields" class="role-fields">
            <h4>🧑‍🔬 Pharmacist Info</h4>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="pharmacist_name" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="pharmacist_contact">
            </div>
            <div class="form-group">
                <label>Shift</label>
                <input type="text" name="shift" placeholder="e.g., Morning, Evening">
            </div>
        </div>

        <button type="submit" name="signup" class="btn">Sign Up</button>
    </form>

    <p class="login-link">Already have an account? <a href="index.php">Log in</a></p>
</div>

<script>
function showRoleFields() {
    const role = document.getElementById("role").value;
    document.getElementById("student-fields").style.display = "none";
    document.getElementById("doctor-fields").style.display = "none";
    document.getElementById("pharmacist-fields").style.display = "none";

    if (role === "student") {
        document.getElementById("student-fields").style.display = "block";
    } else if (role === "doctor") {
        document.getElementById("doctor-fields").style.display = "block";
    } else if (role === "pharmacist") {
        document.getElementById("pharmacist-fields").style.display = "block";
    }
}
</script>
</body>
</html>

