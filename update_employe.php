<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les données du formulaire
$matricule = $_POST['matricule'];
$id_post = $_POST['id_post'];
$salaire_base = $_POST['salaire_base'];
$differentiel = $_POST['differentiel'];
$heure_normal = $_POST['heure_normal'];
$brut_theorique = $_POST['brut_theorique'];

// Mettre à jour les informations dans la base de données
$query_update = "UPDATE employe SET Salaire_Base = ?, Differentiel = ?, Heure_Normal = ?, Brut_Theorique_STD = ? WHERE matricule = ?";
$stmt_update = $conn->prepare($query_update);
$stmt_update->bind_param("dddds", $salaire_base, $differentiel, $heure_normal, $brut_theorique, $matricule);

if ($stmt_update->execute()) {
    // Redirection vers la page attestation_travail.php
    header("Location: attestation_travail.php?matricule=" . urlencode($matricule) . "&id_post=" . urlencode($id_post));
    exit();
} else {
    echo "Erreur lors de la mise à jour.";
}

// Fermer la connexion
$conn->close();
?>
