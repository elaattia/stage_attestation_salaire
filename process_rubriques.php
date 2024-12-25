<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traitement des nouvelles rubriques
if (isset($_POST['new_rubrique_name'])) {
    $new_rubrique_names = $_POST['new_rubrique_name'];
    $new_rubrique_values = $_POST['new_rubrique_value'];
    
    foreach ($new_rubrique_names as $index => $name) {
        $value = isset($new_rubrique_values[$index]) ? $new_rubrique_values[$index] : '';
        
        // Échapper les valeurs pour éviter les injections SQL
        $name = $conn->real_escape_string($name);
        $value = $conn->real_escape_string($value);
        
        // Insérer la nouvelle rubrique dans la table rubrique
        $sql = "INSERT INTO rubrique (nom) VALUES ('$name')";
        if ($conn->query($sql)) {
            // Récupérer le code de la nouvelle rubrique insérée
            $code_rub = $conn->insert_id;
            
            // Insérer la valeur associée dans la table attribut
            $sql_attr = "INSERT INTO attribut (code_rub, valeur) VALUES ('$code_rub', '$value')";
            if (!$conn->query($sql_attr)) {
                echo "Erreur lors de l'ajout de l'attribut de la nouvelle rubrique: " . $conn->error;
            }
        } else {
            echo "Erreur lors de l'ajout de la nouvelle rubrique: " . $conn->error;
        }
    }
}

// Traitement des rubriques existantes
if (isset($_POST['rubriques']) && isset($_POST['rubrique_values'])) {
    $selected_rubriques = $_POST['rubriques'];
    $rubrique_values = $_POST['rubrique_values'];
    
    foreach ($selected_rubriques as $rubrique_code) {
        $value = isset($rubrique_values[$rubrique_code]) ? $rubrique_values[$rubrique_code] : '';
        
        // Échapper les valeurs pour éviter les injections SQL
        $rubrique_code = $conn->real_escape_string($rubrique_code);
        $value = $conn->real_escape_string($value);
        
        // Vérifier si une entrée attribut existe déjà pour cette rubrique
        $sql_check = "SELECT * FROM attribut WHERE code_rub='$rubrique_code'";
        $result_check = $conn->query($sql_check);
        
        if ($result_check->num_rows > 0) {
            // Mettre à jour la valeur existante
            $sql_update = "UPDATE attribut SET valeur='$value' WHERE code_rub='$rubrique_code'";
            if (!$conn->query($sql_update)) {
                echo "Erreur lors de la mise à jour de la rubrique: " . $conn->error;
            }
        } else {
            // Insérer la nouvelle valeur pour la rubrique existante
            $sql_insert = "INSERT INTO attribut (code_rub, valeur) VALUES ('$rubrique_code', '$value')";
            if (!$conn->query($sql_insert)) {
                echo "Erreur lors de l'insertion de la nouvelle valeur de la rubrique: " . $conn->error;
            }
        }
    }
}

// Redirection après traitement
header("Location: gestion_rubriques.php");
exit;


?>
