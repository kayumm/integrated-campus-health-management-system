<?php include 'header.php'; ?>
<?php include "db.php"; if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>View Prescription - ICHMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .prescription-detail {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .detail-row {
            display: flex;
            margin-bottom: 14px;
        }
        .detail-label {
            font-weight: 500;
            color: #2c3e50;
            width: 120px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #34495e;
            flex: 1;
        }
        .drugs-list {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .drug-item {
            padding: 8px;
            margin-bottom: 6px;
            background: white;
            border-left: 3px solid #3498db;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📄 Prescription Details</h2>
    <a href="prescriptions_records.php" class="back-link">← Back to Records</a>

    <?php
    if (!isset($_GET['id'])) {
        echo "<div class='msg error'>No prescription ID provided.</div>";
        exit;
    }

    $appointment_id = intval($_GET['id']);

    // Get shared appointment data
    $sql = "SELECT 
                a.appointment_id,
                s.name AS student_name,
                a.datetime AS appointment_time,
                pr.diagnosis
            FROM prescriptions_records pr
            JOIN appointments a ON pr.appointment_id = a.appointment_id
            JOIN students s ON a.student_id = s.student_id
            WHERE pr.appointment_id = $appointment_id
            LIMIT 1";

    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) == 0) {
        echo "<div class='msg error'>Prescription not found.</div>";
        exit;
    }

    $main = mysqli_fetch_assoc($res);

    // Get ALL drugs prescribed in this appointment
    $drugs = mysqli_query($conn, "SELECT 
                                      d.drug_name, 
                                      pr.dosage, 
                                      pr.duration 
                                  FROM prescriptions_records pr
                                  JOIN drugs d ON pr.drug_id = d.drug_id
                                  WHERE pr.appointment_id = $appointment_id");
    ?>

    <div class="prescription-detail">
        <div class="detail-row">
            <span class="detail-label">Appointment #</span>
            <span class="detail-value"><?php echo $main['appointment_id']; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Student</span>
            <span class="detail-value"><?php echo htmlspecialchars($main['student_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date</span>
            <span class="detail-value"><?php echo date('M j, Y', strtotime($main['appointment_time'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Diagnosis</span>
            <span class="detail-value"><?php echo htmlspecialchars($main['diagnosis']); ?></span>
        </div>

        <h3>💊 Prescribed Drugs</h3>
        <div class="drugs-list">
            <?php if (mysqli_num_rows($drugs) == 0): ?>
                <p>No drugs prescribed.</p>
            <?php else: ?>
                <?php while ($drug = mysqli_fetch_assoc($drugs)): ?>
                    <div class="drug-item">
                        <strong><?php echo htmlspecialchars($drug['drug_name']); ?></strong> – 
                        <?php echo htmlspecialchars($drug['dosage']); ?> 
                        (for <?php echo htmlspecialchars($drug['duration']); ?>)
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>