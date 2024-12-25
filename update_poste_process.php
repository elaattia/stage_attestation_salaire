<?php
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

$id_post = $_POST['id_post'];
$poste_nom = $_POST['poste_nom'];

// Mettre à jour le nom du poste
$update_poste_query = "UPDATE poste SET nom = ? WHERE id_post = ?";
$stmt = $conn->prepare($update_poste_query);
$stmt->bind_param("si", $poste_nom, $id_post);
$stmt->execute();

// Supprimer toutes les rubriques associées au poste
$delete_rubriques_query = "DELETE FROM poste_rubrique_attr WHERE id_post = ?";
$stmt = $conn->prepare($delete_rubriques_query);
$stmt->bind_param("i", $id_post);
$stmt->execute();

// Ajouter les rubriques existantes et nouvelles
if (isset($_POST['rubriques'])) {
    foreach ($_POST['rubriques'] as $index => $rubrique_data) {
        $rubrique = $rubrique_data['rubrique'];
        $valeur = $rubrique_data['valeur'];

        // Vérifier si la rubrique existe déjà
        $get_rubrique_id_query = "SELECT code FROM rubrique WHERE nom = ?";
        $stmt = $conn->prepare($get_rubrique_id_query);
        $stmt->bind_param("s", $rubrique);
        $stmt->execute();
        $rubrique_result = $stmt->get_result();

        if ($rubrique_result->num_rows > 0) {
            // La rubrique existe, obtenir le code
            $rubrique_row = $rubrique_result->fetch_assoc();
            $rubrique_code = $rubrique_row['code'];
        } else {
            // La rubrique n'existe pas, l'ajouter
            $insert_rubrique_query = "INSERT INTO rubrique (nom) VALUES (?)";
            $stmt = $conn->prepare($insert_rubrique_query);
            $stmt->bind_param("s", $rubrique);
            $stmt->execute();

            // Récupérer le code généré pour la nouvelle rubrique
            $rubrique_code = $conn->insert_id;
        }

        // Vérifier si l'attribut existe déjà
        $get_att_id_query = "SELECT id_att FROM attribut WHERE code_rub = ? AND valeur = ?";
        $stmt = $conn->prepare($get_att_id_query);
        $stmt->bind_param("is", $rubrique_code, $valeur);
        $stmt->execute();
        $att_result = $stmt->get_result();

        if ($att_result->num_rows > 0) {
            // L'attribut existe, obtenir l'id
            $att_row = $att_result->fetch_assoc();
            $att_id = $att_row['id_att'];
        } else {
            // L'attribut n'existe pas, l'ajouter
            $insert_att_query = "INSERT INTO attribut (code_rub, valeur) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_att_query);
            $stmt->bind_param("is", $rubrique_code, $valeur);
            $stmt->execute();

            // Récupérer l'id généré pour le nouvel attribut
            $att_id = $conn->insert_id;
        }

        // Insérer dans poste_rubrique_attr
        $insert_rubrique_attr_query = "INSERT INTO poste_rubrique_attr (id_post, id_att) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_rubrique_attr_query);
        $stmt->bind_param("ii", $id_post, $att_id);
        $stmt->execute();
    }
}

// Supprimer une rubrique spécifique si elle est demandée
if (isset($_POST['remove_rubrique'])) {
    $remove_rubrique = $_POST['remove_rubrique'];

    // Obtenir le code de la rubrique à supprimer
    $get_rubrique_code_query = "SELECT code FROM rubrique WHERE nom = ?";
    $stmt = $conn->prepare($get_rubrique_code_query);
    $stmt->bind_param("s", $remove_rubrique);
    $stmt->execute();
    $rubrique_result = $stmt->get_result();
    if ($rubrique_result->num_rows > 0) {
        $rubrique_row = $rubrique_result->fetch_assoc();
        $rubrique_code = $rubrique_row['code'];

        // Obtenir l'id_att pour la rubrique à supprimer
        $get_att_id_query = "SELECT id_att FROM attribut WHERE code_rub = ?";
        $stmt = $conn->prepare($get_att_id_query);
        $stmt->bind_param("i", $rubrique_code);
        $stmt->execute();
        $att_result = $stmt->get_result();
        if ($att_result->num_rows > 0) {
            $att_row = $att_result->fetch_assoc();
            $att_id = $att_row['id_att'];

            // Supprimer la rubrique de poste_rubrique_attr
            $delete_rubrique_query = "DELETE FROM poste_rubrique_attr WHERE id_post = ? AND id_att = ?";
            $stmt = $conn->prepare($delete_rubrique_query);
            $stmt->bind_param("ii", $id_post, $att_id);
            $stmt->execute();
        } else {
            // Gestion de l'erreur si l'attribut à supprimer n'est pas trouvé
            error_log("Attribut non trouvé pour suppression: $remove_rubrique");
        }
    } else {
        // Gestion de l'erreur si la rubrique à supprimer n'est pas trouvée
        error_log("Rubrique non trouvée pour suppression: $remove_rubrique");
    }
}

header("Location: update_poste_form.php?id_post=" . $id_post);
exit();
?>
