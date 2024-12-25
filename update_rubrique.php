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

// Vérifier que les données sont présentes
if (!isset($_POST['id_att'], $_POST['nom_rubrique'], $_POST['valeur_rubrique'])) {
    die("Données manquantes.");
}

$id_att = $_POST['id_att'];
$nom_rubrique = $_POST['nom_rubrique'];
$valeur_rubrique = $_POST['valeur_rubrique'];

// Commencer une transaction
$conn->begin_transaction();

try {
    // Mettre à jour le nom de la rubrique
    $sql_update_rubrique = "UPDATE rubrique r
                            JOIN attribut a ON a.code_rub = r.code
                            SET r.nom = ?
                            WHERE a.id_att = ?";
    $stmt_rubrique = $conn->prepare($sql_update_rubrique);
    $stmt_rubrique->bind_param('si', $nom_rubrique, $id_att);
    $stmt_rubrique->execute();
    $stmt_rubrique->close();

    // Mettre à jour la valeur de l'attribut
    $sql_update_valeur = "UPDATE attribut SET valeur = ? WHERE id_att = ?";
    $stmt_valeur = $conn->prepare($sql_update_valeur);
    $stmt_valeur->bind_param('si', $valeur_rubrique, $id_att);
    $stmt_valeur->execute();
    $stmt_valeur->close();

    // Si tout est correct, on valide la transaction
    $conn->commit();

    // Redirection vers la page de mise à jour des postes
    header('Location: update_poste.php');
    exit();
} catch (Exception $e) {
    // En cas d'erreur, on annule la transaction
    $conn->rollback();
    echo "Erreur lors de la modification : " . $e->getMessage();
}

$conn->close();
?>
