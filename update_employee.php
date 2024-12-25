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

$matricule = $_POST['matricule'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$date_naissance = $_POST['date_naissance'];
$date_embauche = $_POST['date_embauche'];
$salaire_base = $_POST['salaire_base'];

$sql = "UPDATE employe SET Nom='$nom', Prenom='$prenom', Date_Naissance='$date_naissance', Date_Embauche='$date_embauche', Salaire_Base='$salaire_base' WHERE Matricule='$matricule'";

if ($conn->query($sql) === TRUE) {
    echo "Employé mis à jour avec succès";
    // Enregistrement de l'historique
    $sql_hist = "INSERT INTO historique (matricule, action) VALUES ('$matricule', 'Modification des informations')";
    $conn->query($sql_hist);
} else {
    echo "Erreur: " . $conn->error;
}

$conn->close();
?>
