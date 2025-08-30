<?php
include 'header.php';
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];
$user_name = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .header h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #2980b9;
        }
        .header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .nav-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e9ecef;
        }
        .nav-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
            background: #e8f5fd;
        }
        .nav-item a {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            text-decoration: none;
        }
        .nav-item a:hover {
            color: #3498db;
        }
        .nav-item i {
            font-size: 24px;
            color: #3498db;
            margin-bottom: 10px;
        }
        .logout {
            display: block;
            text-align: center;
            margin-top: 25px;
            padding: 10px;
            background: #ecf0f1;
            color: #7f8c8d;
            border-radius: 6px;
            font-size: 14px;
            width: fit-content;
            margin-left: auto;
        }
        .logout:hover {
            background: #bdc3c7;
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard">

        <div class="header">
            <h2>👋 Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
            <p>Role: <strong><?php echo ucfirst($role); ?></strong></p>
        </div>

        <div class="nav-grid">
            <?php if ($role == 'admin'): ?>
                <div class="nav-item">
                    <i>🎓</i>
                    <a href="students.php">Manage Students</a>
                </div>
                <div class="nav-item">
                    <i>👨‍⚕️</i>
                    <a href="doctors.php">Manage Doctors</a>
                </div>
                <div class="nav-item">
                    <i>🧑‍🔬</i>
                    <a href="pharmacists.php">Manage Pharmacists</a>
                </div>
                <div class="nav-item">
                    <i>💊</i>
                    <a href="drugs.php">View Drugs</a>
                </div>
                <div class="nav-item">
                    <i>📅</i>
                    <a href="appointments.php">View Appointments</a>
                </div>
                <div class="nav-item">
                    <i>📊</i>
                    <a href="stats.php">View Statistics</a>
                </div>

            <?php elseif ($role == 'student'): ?>
                <div class="nav-item">
                    <i>📅</i>
                    <a href="appointments.php">Book Appointment</a>
                </div>
                <div class="nav-item">
                    <i>📋</i>
                    <a href="prescriptions_records.php">Prescriptions and Records</a>
                </div>
                <div class="nav-item">
                    <i>💊</i>
                    <a href="drugs.php">Available Drugs</a>
                </div>
                <div class="nav-item">
                    <i>👤</i>
                    <a href="students.php">View and Update Profile</a>
                </div>


            <?php elseif ($role == 'doctor'): ?>
                <div class="nav-item">
                    <i>📅</i>
                    <a href="appointments.php">My Appointments</a>
                </div>
                <div class="nav-item">
                    <i>📋</i>
                    <a href="prescriptions_records.php">Prescriptions and Records</a>
                </div>
                <div class="nav-item">
                    <i>👨‍⚕️</i>
                    <a href="doctors.php">View Profile</a>
                </div>

            <?php elseif ($role == 'pharmacist'): ?>
                <div class="nav-item">
                    <i>💊</i>
                    <a href="drugs.php">Manage Drugs</a>
                </div>
                <div class="nav-item">
                    <i>📋</i>
                    <a href="prescriptions_records.php">Prescriptions and Records</a>
                </div>
                <div class="nav-item">
                    <i>🧑‍🔬</i>
                    <a href="pharmacists.php">View Profile</a>
                </div>
            <?php endif; ?>
        </div>

        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>
</body>
</html>



