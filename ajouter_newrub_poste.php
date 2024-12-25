<?php
//ajouter_newrub_poste.php
header('Content-Type: application/json');

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}

$id_post = isset($_POST['id_post']) ? $_POST['id_post'] : '';
$nom_rubrique = isset($_POST['nouveau_nom_rubrique']) ? $_POST['nouveau_nom_rubrique'] : '';
$valeur_rubrique = isset($_POST['nouvelle_valeur_rubrique']) ? $_POST['nouvelle_valeur_rubrique'] : '';

if (!empty($nom_rubrique) && !empty($valeur_rubrique)) {
    // Déterminez le code de rubrique
    $stmt = $conn->prepare("SELECT MAX(code) AS max_code FROM rubrique");
    $stmt->execute();
    $stmt->bind_result($max_code);
    $stmt->fetch();
    $code_rub = $max_code ? $max_code + 1 : 1;
    $stmt->close();

    // Insérez la nouvelle rubrique
    $stmt = $conn->prepare("INSERT INTO rubrique (code, nom) VALUES (?, ?)");
    $stmt->bind_param("is", $code_rub, $nom_rubrique);
    if (!$stmt->execute()) {
        echo json_encode(array('success' => false, 'error' => 'Erreur lors de l\'ajout de la rubrique.'));
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Insérez l'attribut associé
    $stmt = $conn->prepare("INSERT INTO attribut (code_rub, valeur) VALUES (?, ?)");
    $stmt->bind_param("is", $code_rub, $valeur_rubrique);
    if (!$stmt->execute()) {
        echo json_encode(array('success' => false, 'error' => 'Erreur lors de l\'ajout de l\'attribut.'));
        $stmt->close();
        $conn->close();
        exit;
    }
    $id_att = $stmt->insert_id;
    $stmt->close();

    // Associez l'attribut au poste
    $stmt = $conn->prepare("INSERT INTO poste_rubrique_attr (id_post, id_att) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_post, $id_att);
    if (!$stmt->execute()) {
        echo json_encode(array('success' => false, 'error' => 'Erreur lors de l\'association de l\'attribut au poste.'));
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    echo json_encode(array(
        'success' => true,
        'rubrique_nom' => $nom_rubrique,
        'valeur' => $valeur_rubrique,
        'id_att' => $id_att
    ));
    
} else {
    echo json_encode(array('success' => false, 'error' => 'Données invalides.'));
}

$conn->close();
?>
