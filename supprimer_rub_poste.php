<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id_att = $_GET['id_att'];

// Supprimer l'association
$sql_association = "DELETE FROM poste_rubrique_attr WHERE id_att = ?";
$stmt_association = $conn->prepare($sql_association);
$stmt_association->bind_param('i', $id_att);
$stmt_association->execute();

// Supprimer l'attribut
$sql_attribut = "DELETE FROM attribut WHERE id_att = ?";
$stmt_attribut = $conn->prepare($sql_attribut);
$stmt_attribut->bind_param('i', $id_att);
$stmt_attribut->execute();

// Supprimer la rubrique (si aucun attribut n'est associé)
$sql_rubrique = "DELETE FROM rubrique WHERE code NOT IN (SELECT code_rub FROM attribut)";
$stmt_rubrique = $conn->prepare($sql_rubrique);
$stmt_rubrique->execute();

$conn->close();

header('Location: update_poste.php'); // Redirection vers la page de mise à jour des postes
