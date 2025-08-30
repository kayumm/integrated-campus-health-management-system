<?php include 'header.php'; ?>
<?php include "db.php"; if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Drugs - ICHMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>💊 Drug Inventory</h2>
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php
    $role = $_SESSION['role'];
    $can_edit = ($role === 'pharmacist');

    // Search
    $search = "";
    $condition = "";
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $condition = " WHERE drug_name LIKE '%$search%' OR brand LIKE '%$search%' OR dosage LIKE '%$search%'";
    }

    // Delete (pharmacist only)
    if ($can_edit && isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        mysqli_query($conn, "DELETE FROM drugs WHERE drug_id = $id");
        echo "<div class='msg success'>Drug deleted successfully.</div>";
    }

    // Add Drug
    if ($can_edit && isset($_POST['add_drug'])) {
        $name = mysqli_real_escape_string($conn, $_POST['drug_name']);
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
        $stock = intval($_POST['stock_qty']);
        $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);

        if (empty($name)) {
            echo "<div class='msg error'>Drug name is required.</div>";
        } else {
            $sql = "INSERT INTO drugs (drug_name, brand, dosage, stock_qty, expiry_date) 
                    VALUES ('$name', '$brand', '$dosage', $stock, '$expiry')";
            mysqli_query($conn, $sql);
            echo "<div class='msg success'>Drug added successfully.</div>";
        }
    }

    // Update Drug
    if ($can_edit && isset($_POST['update_drug'])) {
        $id = intval($_POST['drug_id']);
        $name = mysqli_real_escape_string($conn, $_POST['drug_name']);
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
        $stock = intval($_POST['stock_qty']);
        $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);

        if (empty($name)) {
            echo "<div class='msg error'>Drug name is required.</div>";
        } else {
            $sql = "UPDATE drugs SET 
                        drug_name = '$name', 
                        brand = '$brand', 
                        dosage = '$dosage', 
                        stock_qty = $stock, 
                        expiry_date = '$expiry' 
                    WHERE drug_id = $id";
            mysqli_query($conn, $sql);
            echo "<div class='msg success'>Drug updated successfully.</div>";
            // Redirect to hide form
            echo "<script>setTimeout(() => { window.location.href = 'drugs.php'; }, 750);</script>";
        }
    }

    // Show Edit Form
    if ($can_edit && isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $res = mysqli_query($conn, "SELECT * FROM drugs WHERE drug_id = $id");
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            ?>
            <h3>✏️ Edit Drug</h3>
            <form method="post">
                <input type="hidden" name="drug_id" value="<?php echo $row['drug_id']; ?>">
                <table>
                    <tr>
                        <td><strong>Drug Name *</strong></td>
                        <td><input type="text" name="drug_name" value="<?php echo htmlspecialchars($row['drug_name']); ?>" required></td>
                    </tr>
                    <tr>
                        <td>Brand</td>
                        <td><input type="text" name="brand" value="<?php echo htmlspecialchars($row['brand']); ?>"></td>
                    </tr>
                    <tr>
                        <td>Dosage</td>
                        <td><input type="text" name="dosage" value="<?php echo htmlspecialchars($row['dosage']); ?>"></td>
                    </tr>
                    <tr>
                        <td>Stock Quantity</td>
                        <td><input type="number" name="stock_qty" value="<?php echo $row['stock_qty']; ?>" min="0"></td>
                    </tr>
                    <tr>
                        <td>Expiry Date</td>
                        <td><input type="date" name="expiry_date" value="<?php echo $row['expiry_date']; ?>"></td>
                    </tr>
                </table>
                <div style="margin-top: 15px;">
                    <input type="submit" name="update_drug" value="Update Drug">
                    <a href="drugs.php?search=<?php echo urlencode($search); ?>">Cancel</a>
                </div>
            </form>
            <hr>
            <?php
        }
    }
    ?>

    <!-- Search Box -->
    <div class="search-box">
        <form method="get">
            <input type="text" name="search" placeholder="Search drugs by name, brand, or dosage..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <input type="submit" value="Search">
            <?php if (isset($_GET['search'])): ?>
                <a href="drugs.php">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Add Drug Button (Only for Pharmacist) -->
    <?php if ($can_edit): ?>
        <button id="addDrugBtn" class="toggle-btn" style="margin: 15px 0; background: #27ae60; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer;">
            ➕ Add New Drug
        </button>

        <!-- Hidden Add Form -->
        <div id="addDrugForm" style="display: none; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; margin: 15px 0;">
            <h3>➕ Add New Drug</h3>
            <form method="post">
                <table>
                    <tr>
                        <td><strong>Drug Name *</strong></td>
                        <td><input type="text" name="drug_name" required></td>
                    </tr>
                    <tr>
                        <td>Brand</td>
                        <td><input type="text" name="brand"></td>
                    </tr>
                    <tr>
                        <td>Dosage</td>
                        <td><input type="text" name="dosage"></td>
                    </tr>
                    <tr>
                        <td>Stock Quantity</td>
                        <td><input type="number" name="stock_qty" value="0" min="0"></td>
                    </tr>
                    <tr>
                        <td>Expiry Date</td>
                        <td><input type="date" name="expiry_date"></td>
                    </tr>
                </table>
                <div style="margin-top: 15px;">
                    <input type="submit" name="add_drug" value="Add Drug">
                    <button type="button" onclick="document.getElementById('addDrugForm').style.display='none';" style="background: #95a5a6; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer;">Cancel</button>
                </div>
            </form>
        </div>

        <script>
            document.getElementById('addDrugBtn').onclick = function () {
                const form = document.getElementById('addDrugForm');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            };
        </script>
    <?php endif; ?>

    <!-- Drug List -->
    <h3>📋 Available Drugs</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Drug Name</th>
                <th>Brand</th>
                <th>Dosage</th>
                <th>Stock</th>
                <th>Expiry</th>
                <?php if ($can_edit): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM drugs $condition ORDER BY drug_name";
            $res = mysqli_query($conn, $sql);

            if (mysqli_num_rows($res) == 0) {
                echo "<tr><td colspan='" . ($can_edit ? '7' : '6') . "' style='text-align: center; color: #7f8c8d; padding: 20px;'>No drugs found.</td></tr>";
            } else {
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr>
                            <td>{$row['drug_id']}</td>
                            <td><strong>" . htmlspecialchars($row['drug_name']) . "</strong></td>
                            <td>" . htmlspecialchars($row['brand']) . "</td>
                            <td>{$row['dosage']}</td>
                            <td>{$row['stock_qty']}</td>
                            <td>{$row['expiry_date']}</td>";
                    if ($can_edit) {
                        echo "<td class='actions'>
                                <a href='drugs.php?edit={$row['drug_id']}&search=" . urlencode($search) . "'>Edit</a> | 
                                <a href='drugs.php?delete={$row['drug_id']}&search=" . urlencode($search) . "' 
                                   class='delete-link'
                                   onclick=\"return confirm('Delete {$row['drug_name']}? This cannot be undone.');\">Delete</a>
                              </td>";
                    }
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div> <!-- /.container -->

</body>
</html>

