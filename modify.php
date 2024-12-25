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

// Prepare and bind
$stmt = $conn->prepare("SELECT * FROM employe WHERE Matricule = ?");
$stmt->bind_param("s", $matricule);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Modifier Employé</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1>Modifier Employé</h1>
        <form action="update_employee.php" method="post">
            <input type="hidden" name="matricule" value="<?php echo $row['Matricule']; ?>">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?php echo $row['Nom']; ?>" required><br>
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo $row['Prenom']; ?>" required><br>
            <label for="date_naissance">Date de Naissance:</label>
            <input type="date" id="date_naissance" name="date_naissance" value="<?php echo $row['Date_Naissance']; ?>" required><br>
            <label for="date_embauche">Date d'Embauche:</label>
            <input type="date" id="date_embauche" name="date_embauche" value="<?php echo $row['Date_Embauche']; ?>" required><br>
            <label for="salaire_base">Salaire de Base:</label>
            <input type="number" id="salaire_base" name="salaire_base" value="<?php echo $row['Salaire_Base']; ?>" required><br>
            <button type="submit">Mettre à Jour</button>
        </form>
    </body>
    </html>
    <?php
} else {
    echo "Employé non trouvé.";
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
