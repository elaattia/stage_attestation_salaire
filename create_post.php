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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['type_poste'], $_POST['nom_poste'], $_POST['salaire_base'], $_POST['differentiel'], $_POST['prime_anciennete'])) {
        $type_poste = $_POST['type_poste'];
        $nom_poste = $_POST['nom_poste'];
        $salaire_base = $_POST['salaire_base'];
        $differentiel = $_POST['differentiel'];
        $prime_anciennete = $_POST['prime_anciennete'];
        $prime_anciennete_value = $salaire_base * ($prime_anciennete / 100);

        // Insérer les données de base du poste
        $query = "INSERT INTO poste (type_poste, nom, salaire_base, differentiel, prime_anciennete) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdd", $type_poste, $nom_poste, $salaire_base, $differentiel, $prime_anciennete_value);
        $stmt->execute();
        $id_post = $stmt->insert_id;

        // Ajouter les augmentations spécifiques
        $augment_16_18 = $_POST['augment_16_18'] ?? '';
        $augment_salariale = $_POST['augment_salariale'] ?? '';
        $augment_2021 = $_POST['augment_2021'] ?? '';
        $augment_2022 = $_POST['augment_2022'] ?? '';

        $augmentations = [
            '19120' => $augment_16_18,
            '19130' => $augment_salariale,
            '19140' => $augment_2021,
            '19150' => $augment_2022
        ];

        $query_insert_rubrique = "INSERT INTO poste_rubrique_attr (id_post, code_rub, valeur) VALUES (?, ?, ?)";
        $stmt_insert_rubrique = $conn->prepare($query_insert_rubrique);

        foreach ($augmentations as $code => $valeur) {
            $stmt_insert_rubrique->bind_param("iss", $id_post, $code, $valeur);
            $stmt_insert_rubrique->execute();
        }

        // Ajouter les rubriques sélectionnées
        if (isset($_POST['rubriques'], $_POST['rubrique_values'])) {
            $rubriques = $_POST['rubriques'];
            $rubrique_values = $_POST['rubrique_values'];

            foreach ($rubriques as $code) {
                $valeur = $rubrique_values[$code];
                $stmt_insert_rubrique->bind_param("iss", $id_post, $code, $valeur);
                $stmt_insert_rubrique->execute();
            }
        }

        // Ajouter les rubriques personnalisées
        if (isset($_POST['new_rubrique_name']) && isset($_POST['new_rubrique_value'])) {
            $new_rubrique_names = $_POST['new_rubrique_name'];
            $new_rubrique_values = $_POST['new_rubrique_value'];

            $total_sum = floatval($salaire_base) + floatval($differentiel) + floatval($prime_anciennete_value);
            for ($i = 0; $i < count($new_rubrique_names); $i++) {
                $new_rubrique_name = $new_rubrique_names[$i];
                $new_rubrique_value = $new_rubrique_values[$i];

                // Ajouter la nouvelle rubrique si elle n'existe pas
                $query_check_rubrique = "SELECT code FROM rubrique WHERE nom = ?";
                $stmt_check_rubrique = $conn->prepare($query_check_rubrique);
                $stmt_check_rubrique->bind_param("s", $new_rubrique_name);
                $stmt_check_rubrique->execute();
                $result_check_rubrique = $stmt_check_rubrique->get_result();

                if ($result_check_rubrique->num_rows == 0) {
                    $query_insert_rubrique = "INSERT INTO rubrique (nom) VALUES (?)";
                    $stmt_insert_rubrique = $conn->prepare($query_insert_rubrique);
                    $stmt_insert_rubrique->bind_param("s", $new_rubrique_name);
                    $stmt_insert_rubrique->execute();
                    $rubrique_code = $stmt_insert_rubrique->insert_id;
                } else {
                    $rubrique = $result_check_rubrique->fetch_assoc();
                    $rubrique_code = $rubrique['code'];
                }

                // Ajouter la rubrique au poste
                $query_insert_rubrique_attr = "INSERT INTO poste_rubrique_attr (id_post, code_rub, valeur) VALUES (?, ?, ?)";
                $stmt_insert_rubrique_attr = $conn->prepare($query_insert_rubrique_attr);
                $stmt_insert_rubrique_attr->bind_param("iss", $id_post, $rubrique_code, $new_rubrique_value);
                $stmt_insert_rubrique_attr->execute();

                $total_sum += floatval($new_rubrique_value);
            }

            // Vérifier si la somme des valeurs est égale à la valeur cible
            if ($total_sum != $total_expected) {
                echo "La somme des valeurs des rubriques ne correspond pas à la valeur cible.";
                exit();
            }
        }

        echo "Poste créé avec succès !";
    }
}
$conn->close();
?>
