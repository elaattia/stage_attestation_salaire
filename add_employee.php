<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database";

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

$sql = "INSERT INTO employe (Matricule, Nom, Prenom, Date_Naissance, Date_Embauche, Salaire_Base)
        VALUES ('$matricule', '$nom', '$prenom', '$date_naissance', '$date_embauche', '$salaire_base')";

if ($conn->query($sql) === TRUE) {
    echo "Nouvel employé ajouté avec succès";
} else {
    echo "Erreur: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
