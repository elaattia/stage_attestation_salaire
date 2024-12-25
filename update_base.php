<?php
// insert_into_table.php

if (isset($_POST['submit'])) {
    // Récupération des données
    $tableau_excel = $_POST['tableau_excel'] ?? '';
    $nom_table = $_POST['nom_table'] ?? '';

    if (empty($tableau_excel) || empty($nom_table)) {
        die('Le tableau ou le nom de la table ne peut pas être vide.');
    }

    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "attestation_salaire";

    // Création d'une instance mysqli
    $mysqli = new mysqli($servername, $username, $password, $dbname);

    // Vérification de la connexion
    if ($mysqli->connect_error) {
        die('Erreur de connexion : ' . $mysqli->connect_error);
    }

    // Conversion du texte en tableau PHP
    $rows = explode("\n", trim($tableau_excel));
    $data = [];
    foreach ($rows as $row) {
        $data[] = str_getcsv($row, "\t");  // Utilise tabulation comme séparateur
    }

    if (empty($data)) {
        die('Le tableau est vide.');
    }

    // Préparation de la requête d'insertion
    $colonnes = array_shift($data); // Première ligne comme en-tête
    $colonnes = array_map('trim', $colonnes);  // Nettoyer les espaces

    // Vérifier l'existence de la table
    $result = $mysqli->query("SHOW TABLES LIKE '$nom_table'");
    if ($result->num_rows === 0) {
        die('La table spécifiée n\'existe pas.');
    }

    // Ajouter des colonnes manquantes dans la table
    $existing_columns_query = $mysqli->query("SHOW COLUMNS FROM $nom_table");
    $existing_columns = [];
    while ($row = $existing_columns_query->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }

    foreach ($colonnes as $col) {
        if (!in_array($col, $existing_columns)) {
            $add_column_query = "ALTER TABLE $nom_table ADD COLUMN `$col` VARCHAR(255)";
            if (!$mysqli->query($add_column_query)) {
                die('Erreur lors de l\'ajout de la colonne : ' . $mysqli->error);
            }
        }
    }

    // Préparer la requête d'insertion
    $colonnes_sql = implode(', ', array_map(fn($col) => "`$col`", $colonnes));
    $placeholders = implode(', ', array_fill(0, count($colonnes), '?'));
    $query = "INSERT INTO $nom_table ($colonnes_sql) VALUES ($placeholders)";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die('Erreur de préparation de la requête : ' . $mysqli->error);
    }

    // Insérer les données dans la base de données
    foreach ($data as $row) {
        if (count($row) === count($colonnes)) {
            $stmt->bind_param(str_repeat('s', count($colonnes)), ...$row);
            if (!$stmt->execute()) {
                die('Erreur lors de l\'insertion des données : ' . $stmt->error);
            }
        }
    }

    echo '<p>Les données ont été insérées avec succès dans la table ' . htmlspecialchars($nom_table) . '.</p>';

    $stmt->close();
    $mysqli->close();
}
?>
