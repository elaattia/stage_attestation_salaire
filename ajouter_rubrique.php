<?php
// ajouter_rubrique.php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_post']) && isset($_POST['nom_rubrique']) && isset($_POST['valeur_rubrique'])) {
    $id_post = $_POST['id_post'];
    $nom_rubrique = $_POST['nom_rubrique'];
    $valeur_rubrique = $_POST['valeur_rubrique'];

    // Insérer la nouvelle rubrique dans la table `rubrique` (si elle n'existe pas déjà)
    $stmt = $conn->prepare("INSERT INTO rubrique (nom) VALUES (?) ON DUPLICATE KEY UPDATE nom = nom");
    $stmt->bind_param("s", $nom_rubrique);
    $stmt->execute();

    // Récupérer l'ID de la rubrique
    $rubrique_id = $conn->insert_id ? $conn->insert_id : $conn->query("SELECT code FROM rubrique WHERE nom='$nom_rubrique'")->fetch_assoc()['code'];

    // Insérer l'attribut associé dans la table `attribut`
    $stmt = $conn->prepare("INSERT INTO attribut (code_rub, valeur) VALUES (?, ?)");
    $stmt->bind_param("is", $rubrique_id, $valeur_rubrique);
    $stmt->execute();

    // Récupérer l'ID de l'attribut
    $id_att = $stmt->insert_id;

    // Associer la rubrique au poste via `poste_rubrique_attr`
    $stmt = $conn->prepare("INSERT INTO poste_rubrique_attr (id_post, id_att) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_post, $id_att);
    $stmt->execute();

    $stmt->close();
}

// Rediriger vers update_poste.php après ajout
header("Location: update_poste.php");
exit();

?>
