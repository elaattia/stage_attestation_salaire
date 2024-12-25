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

$sql = "DELETE FROM employe WHERE Matricule='$matricule'";

if ($conn->query($sql) === TRUE) {
    echo "Employé supprimé avec succès";
    // Enregistrement de l'historique
    $sql_hist = "INSERT INTO historique (matricule, action) VALUES ('$matricule', 'Suppression de l\'employé')";
    $conn->query($sql_hist);
} else {
    echo "Erreur: " . $conn->error;
}

$conn->close();
?>
