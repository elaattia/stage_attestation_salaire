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

// Récupération des données envoyées par le formulaire
$nom_poste = isset($_POST['nom_poste']) ? $_POST['nom_poste'] : '';
$id_post = isset($_POST['id_post']) ? $_POST['id_post'] : '';
$nom_rubriques = isset($_POST['nom_rubrique']) ? $_POST['nom_rubrique'] : [];
$valeurs_rubriques = isset($_POST['valeur_rubrique']) ? $_POST['valeur_rubrique'] : [];
$codes_rubriques_existantes = isset($_POST['rubrique_code']) ? $_POST['rubrique_code'] : [];
$valeurs_rubriques_existantes = isset($_POST['valeur']) ? $_POST['valeur'] : [];
$type_poste = isset($_POST['type_poste']) ? $_POST['type_poste'] : '';

if (!empty($nom_poste) && !empty($type_poste)) {
    // Démarrer une transaction
    $conn->begin_transaction();

    try {
        // Déterminer le type de poste (1 pour horaire, 2 pour mensuel)
        $id_type = ($type_poste === 'horaire') ? 1 : 2;

        // Insérer le nom du poste si c'est un nouveau poste
        if (empty($id_post)) {
            $stmt = $conn->prepare("INSERT INTO poste (nom, id_type) VALUES (?, ?)");
            $stmt->bind_param("si", $nom_poste, $id_type);
            $stmt->execute();
            $id_post = $stmt->insert_id;
            $stmt->close();
        }

        // Ajouter les rubriques existantes
        foreach ($codes_rubriques_existantes as $index => $code_rubrique) {
            $valeur_rubrique = $valeurs_rubriques_existantes[$index];

            // Insérer l'attribut associé
            $stmt = $conn->prepare("INSERT INTO attribut (code_rub, valeur) VALUES (?, ?)");
            $stmt->bind_param("is", $code_rubrique, $valeur_rubrique);
            $stmt->execute();
            $id_att = $stmt->insert_id;
            $stmt->close();

            // Associer l'attribut au poste
            $stmt = $conn->prepare("INSERT INTO poste_rubrique_attr (id_post, id_att) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_post, $id_att);
            $stmt->execute();
            $stmt->close();
        }

        // Ajouter les nouvelles rubriques
        foreach ($nom_rubriques as $index => $nom_rubrique) {
            $valeur_rubrique = $valeurs_rubriques[$index];

            // Déterminer le code de la nouvelle rubrique
            $stmt = $conn->prepare("SELECT MAX(code) AS max_code FROM rubrique");
            $stmt->execute();
            $stmt->bind_result($max_code);
            $stmt->fetch();
            $stmt->close();

            $code_rub = $max_code ? $max_code + 1 : 1;

            // Insérer la nouvelle rubrique
            $stmt = $conn->prepare("INSERT INTO rubrique (code, nom) VALUES (?, ?)");
            $stmt->bind_param("is", $code_rub, $nom_rubrique);
            $stmt->execute();
            $stmt->close();

            // Insérer l'attribut associé
            $stmt = $conn->prepare("INSERT INTO attribut (code_rub, valeur) VALUES (?, ?)");
            $stmt->bind_param("is", $code_rub, $valeur_rubrique);
            $stmt->execute();
            $id_att = $stmt->insert_id;
            $stmt->close();

            // Associer l'attribut au poste
            $stmt = $conn->prepare("INSERT INTO poste_rubrique_attr (id_post, id_att) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_post, $id_att);
            $stmt->execute();
            $stmt->close();
        }

        // Commit de la transaction
        $conn->commit();

        // Redirection vers la page update_poste.php après un ajout réussi
        header("Location: update_poste.php");
        exit;

    } catch (Exception $e) {
        // Rollback de la transaction en cas d'erreur
        $conn->rollback();
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }

} else {
    echo json_encode(array('success' => false, 'error' => 'Données invalides.'));
}

// Fermeture de la connexion
$conn->close();
?>
