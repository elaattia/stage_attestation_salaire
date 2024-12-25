<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$matricule = $_GET['matricule'];

$sql = "SELECT * FROM historique WHERE matricule='$matricule' ORDER BY date DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Historique des Actions</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Historique des Actions pour l'Employé: <?php echo $matricule; ?></h1>
    <?php
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>Action</th><th>Date</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['action'] . "</td><td>" . $row['date'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Aucune action enregistrée.";
    }

    $conn->close();
    ?>
</body>
</html>
