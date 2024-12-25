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

// Vérifiez si les données sont envoyées via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $matricule = isset($_POST['matricule']) ? $conn->real_escape_string($_POST['matricule']) : '';
    $reference = isset($_POST['ref']) ? $conn->real_escape_string($_POST['ref']) : '';
    $id_post = isset($_POST['poste']) ? $conn->real_escape_string($_POST['poste']) : '';

    // Préparer la requête SQL pour vérifier si la rubrique existe
    $query_check_rubrique = "SELECT COUNT(*) as count FROM rubrique WHERE code = ?";
    $stmt_check_rubrique = $conn->prepare($query_check_rubrique);
    if ($stmt_check_rubrique === false) {
        die("Erreur de préparation : " . $conn->error);
    }

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'valeur_rubrique_') === 0) {
            $rubrique_code = str_replace('valeur_rubrique_', '', $key);
            $valeur = $conn->real_escape_string($value);

            // Vérifiez si la rubrique existe
            $stmt_check_rubrique->bind_param("s", $rubrique_code);
            $stmt_check_rubrique->execute();
            $result_check_rubrique = $stmt_check_rubrique->get_result();
            $rubrique_exists = $result_check_rubrique->fetch_assoc()['count'] > 0;

            if ($rubrique_exists) {
                // Préparer la requête SQL pour vérifier si la valeur existe
                $query_check_value = "SELECT COUNT(*) as count FROM attribut WHERE code_rub = ? AND valeur = ?";
                $stmt_check_value = $conn->prepare($query_check_value);
                if ($stmt_check_value === false) {
                    die("Erreur de préparation : " . $conn->error);
                }
                
                // Vérifiez si la valeur existe
                $stmt_check_value->bind_param("ss", $rubrique_code, $valeur);
                $stmt_check_value->execute();
                $result_check_value = $stmt_check_value->get_result();
                $value_exists = $result_check_value->fetch_assoc()['count'] > 0;

                if (!$value_exists) {
                    // Préparer la requête SQL pour insérer ou mettre à jour les valeurs
                    $query_insert_update = "INSERT INTO attribut (code_rub, valeur)
                                            VALUES (?, ?)
                                            ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)";
                    $stmt_insert_update = $conn->prepare($query_insert_update);
                    if ($stmt_insert_update === false) {
                        die("Erreur de préparation : " . $conn->error);
                    }
                    $stmt_insert_update->bind_param("ss", $rubrique_code, $valeur);

                    if ($stmt_insert_update->execute()) {
                        echo "Données enregistrées avec succès.";
                    } else {
                        echo "Erreur : " . $stmt_insert_update->error;
                    }
                    $stmt_insert_update->close();
                }
                $stmt_check_value->close();
            } else {
                echo "La rubrique '$rubrique_code' n'existe pas.";
            }
        }
    }
    $stmt_check_rubrique->close();
}





// Fermer la connexion
$conn->close();
?>
