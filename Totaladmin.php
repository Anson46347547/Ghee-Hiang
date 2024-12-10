<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Update with your database password if necessary
$dbname = "gheehiang";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_POST['delete_admin'])) {
    $admin_id = $_POST['admin_id'];
    $delete_sql = "DELETE FROM admins WHERE id='$admin_id'";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Admin deleted successfully.'); window.location.href='Totaladmin.php';</script>";
    } else {
        echo "Error deleting admin: " . $conn->error;
    }
}

// Fetch all admins from the admins table
$sql = "SELECT id, username FROM admins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Admins</title>
    <style>
        table {
            width: 60%;
            margin: 50px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 5px 10px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">List of Admins</h2>

<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Username</th><th>Manage</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>";
        echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this admin?\");'>";
        echo "<input type='hidden' name='admin_id' value='" . $row['id'] . "'>";
        echo "<button type='submit' name='delete_admin'>Delete</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align: center;'>No admins found.</p>";
}

$conn->close();
?>

</body>
</html>
