<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";


$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_post'])) {
    $id_post = $_POST['id_post'];

    // Supprimer les enregistrements dans poste_rubrique_attr liés à ce poste
    $delete_related_query = "DELETE FROM poste_rubrique_attr WHERE id_post = ?";
    $stmt_related = $conn->prepare($delete_related_query);
    $stmt_related->bind_param("i", $id_post);

    if ($stmt_related->execute()) {
        // Ensuite, supprimer le poste lui-même
        $delete_post_query = "DELETE FROM poste WHERE id_post = ?";
        $stmt_post = $conn->prepare($delete_post_query);
        $stmt_post->bind_param("i", $id_post);

        if ($stmt_post->execute()) {
            // Rediriger avec un message de succès
            header("Location: update_poste.php?success=1");
            exit();
        } else {
            echo "Erreur lors de la suppression du poste : " . $conn->error;
        }
        $stmt_post->close();
    } else {
        echo "Erreur lors de la suppression des dépendances : " . $conn->error;
    }
    $stmt_related->close();
} else {
    echo "Requête invalide.";
}

$conn->close();
?>
