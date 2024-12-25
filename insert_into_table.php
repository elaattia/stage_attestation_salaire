<?php
// insert_into_table.php

$messages = []; // Tableau pour stocker les messages

if (isset($_POST['submit'])) {
    // Récupération des données
    $tableau_excel = $_POST['tableau_excel'] ?? '';
    $nom_table = $_POST['nom_table'] ?? '';

    if (empty($tableau_excel) || empty($nom_table)) {
        $messages[] = 'Le tableau ou le nom de la table ne peut pas être vide.';
    } else {
        // Connexion à la base de données
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "attestation_salaire";

        // Création d'une instance mysqli
        $mysqli = new mysqli($servername, $username, $password, $dbname);

        // Vérification de la connexion
        if ($mysqli->connect_error) {
            $messages[] = 'Erreur de connexion : ' . $mysqli->connect_error;
        } else {
            // Conversion du texte en tableau PHP
            $rows = explode("\n", trim($tableau_excel));
            $data = [];
            foreach ($rows as $row) {
                $data[] = str_getcsv($row, "\t");  // Utilise tabulation comme séparateur
            }

            if (empty($data)) {
                $messages[] = 'Le tableau est vide.';
            } else {
                // Vérifier l'existence de la table
                $result = $mysqli->query("SHOW TABLES LIKE '$nom_table'");
                if ($result->num_rows === 0) {
                    $messages[] = 'La table spécifiée n\'existe pas.';
                } else {
                    // Supprimer le contenu de la table
                    if ($mysqli->query("DELETE FROM $nom_table")) {
                        $messages[] = 'Le contenu de la table ' . htmlspecialchars($nom_table) . ' a été supprimé.';
                    } else {
                        $messages[] = 'Erreur lors de la suppression des données : ' . $mysqli->error;
                    }

                    // Préparer la requête d'insertion
                    $colonnes = array_shift($data); // Première ligne comme en-tête
                    $colonnes = array_map('trim', $colonnes);  // Nettoyer les espaces

                    // Vérifier si le nombre de colonnes est correct
                    if (count($colonnes) !== 11) {
                        $messages[] = 'Le nombre de colonnes dans le tableau ne correspond pas à celui de la table employe.';
                    } else {
                        // Ajouter les backticks autour des noms de colonnes
                        $colonnes = array_map(function($colonne) {
                            return '`' . $colonne . '`';
                        }, $colonnes);

                        // Vérifier que le nombre de colonnes et de paramètres correspond
                        $placeholders = implode(', ', array_fill(0, count($colonnes), '?'));  // Placeholders pour les valeurs
                        $colonnes_list = implode(', ', $colonnes);  // Liste des colonnes

                        $query = "INSERT INTO $nom_table ($colonnes_list) VALUES ($placeholders)";

                        // Préparer et exécuter la requête d'insertion
                        $stmt = $mysqli->prepare($query);
                        if ($stmt === false) {
                            $messages[] = 'Erreur de préparation de la requête : ' . $mysqli->error;
                        } else {
                            $affected_rows = 0;
                            foreach ($data as $row) {
                                if (count($row) === 11) {
                                    // Remplacer les champs vides par NULL ou 0
                                    foreach ($row as $key => $value) {
                                        if (empty($value)) {
                                            $row[$key] = ($key == 0 || $key >= 5) ? 0 : null;  // Remplacer par 0 pour certaines colonnes, null pour les autres
                                        }
                                    }

                                    // Convertir les valeurs avec des virgules en points pour les décimales
                                    if ($row[5] !== null) $row[5] = str_replace(',', '.', $row[5]); // Salaire_Base
                                    if ($row[6] !== null) $row[6] = str_replace(',', '.', $row[6]); // Salaire_Base
                                    if ($row[7] !== null) $row[7] = str_replace(',', '.', $row[7]); // Heure_Normal
                                    if ($row[8] !== null) $row[8] = str_replace(',', '.', $row[8]); // Brut_Theorique_STD
                                    if ($row[10] !== null) $row[10] = str_replace(',', '.', $row[10]); // Brut_Theorique_STD_JR

                                    // Convertir les dates en format Y-m-d
                                    $date_naissance = !empty($row[3]) ? DateTime::createFromFormat('d/m/Y', $row[3]) : false;
                                    $date_embauche = !empty($row[4]) ? DateTime::createFromFormat('d/m/Y', $row[4]) : false;

                                    if ($date_naissance) {
                                        $row[3] = $date_naissance->format('Y-m-d'); // Date_Naissance
                                    } else {
                                        $row[3] = null;
                                    }

                                    if ($date_embauche) {
                                        $row[4] = $date_embauche->format('Y-m-d'); // Date_Embauche
                                    } else {
                                        $row[4] = null;
                                    }

                                    // Convertir en types appropriés pour `bind_param`
                                    $types = str_repeat('s', 11);  // Tous les paramètres sont des chaînes
                                    $stmt->bind_param($types, ...$row);

                                    if ($stmt->execute()) {
                                        $affected_rows += $stmt->affected_rows;
                                    } else {
                                        $messages[] = 'Erreur lors de l\'insertion des données : ' . $stmt->error;
                                    }
                                } else {
                                    $messages[] = 'Le nombre de colonnes ne correspond pas pour la ligne : ' . implode(', ', $row);
                                }
                            }

                            $stmt->close();
                            $messages[] = $affected_rows . ' ligne(s) ont été ajoutées avec succès dans la table ' . htmlspecialchars($nom_table) . '.';
                        }
                    }
                }
            }
        }

        $mysqli->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Résultat de l'Insertion</title>
    <style>
        .button-container {
            display: flex;
            justify-content: center;
            margin: 20px;
        }
        .btn-large {
            display: inline-block;
            padding: 20px 40px;
            font-size: 18px;
            color: #fff;
            background: rgba(76,68,182,0.808);
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-large:hover {
            opacity: 0.82;
        }
        .message {
            text-align: center;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Résultat de l'Insertion</header>
            <div class="message">
                <?php
                if (!empty($messages)) {
                    foreach ($messages as $message) {
                        echo '<p>' . htmlspecialchars($message) . '</p>';
                    }
                }
                ?>
            </div>
            <div class="button-container">
                <a href="choix.php" class="btn-large">Revenir à la page choix</a>
            </div>
        </div>
    </div>
</body>
</html>
