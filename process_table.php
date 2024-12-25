<?php
// process_table.php

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer Tableau</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #e4e9f7;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 90vh;
            padding: 20px;
        }

        .box {
            background: #fdfdfd;
            display: flex;
            flex-direction: column;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 0 128px 0 rgba(0,0,0,0.1),
                        0 32px 64px -48px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 800px;
            max-height: calc(100vh - 100px); /* Ajustez selon l'espace disponible pour le titre et le bouton */
            overflow: hidden; /* Masque le débordement */
            position: relative;
        }

        .table-container {
            max-height: calc(100vh - 200px); /* Ajustez cette hauteur selon vos besoins */
            overflow-y: auto;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto; /* Permet le défilement horizontal si le tableau est trop large */
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        textarea {
            width: 100%;
            max-width: 800px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background: rgba(76,68,182,0.808);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: rgba(76,68,182,0.82);
        }

        .message {
            text-align: center;
            background: #f9eded;
            padding: 15px;
            border: 1px solid #699053;
            border-radius: 5px;
            margin-bottom: 10px;
            color: red;
        }

        .footer {
            margin-top: auto; /* Permet de coller le pied de page au bas du conteneur */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>Importer Tableau</h1>
            <?php
            if (isset($_POST['submit'])) {
                // Récupération des données du formulaire
                $tableau_excel = $_POST['tableau_excel'] ?? '';

                if (empty($tableau_excel)) {
                    echo '<div class="message">Le tableau ne peut pas être vide.</div>';
                    exit;
                }

                // Nom de la table fixe
                $nom_table = 'employe';

                // Conversion du texte en tableau PHP
                $rows = explode("\n", trim($tableau_excel));
                $data = [];
                foreach ($rows as $row) {
                    // Utilisation de la tabulation comme séparateur
                    $data[] = str_getcsv($row, "\t");  
                }

                if (empty($data)) {
                    echo '<div class="message">Le tableau est vide.</div>';
                    exit;
                }

                // Affichage du tableau HTML
                echo '<h2>Tableau HTML</h2>';
                echo '<div class="table-container">';
                $html_table = '<table>';
                $html_table .= '<tr>';
                foreach ($data[0] as $cell) {
                    $html_table .= '<th>' . htmlspecialchars($cell) . '</th>';
                }
                $html_table .= '</tr>';

                foreach ($data as $row) {
                    $html_table .= '<tr>';
                    foreach ($row as $cell) {
                        $html_table .= '<td>' . htmlspecialchars($cell) . '</td>';
                    }
                    $html_table .= '</tr>';
                }
                $html_table .= '</table>';

                echo $html_table;
                echo '</div>';

                // Formulaire pour l'insertion des données
                echo '<div class="footer">';
                echo '<h2>Supprimer et Ajouter les Données dans la Table</h2>';
                echo '<form action="insert_into_table.php" method="post">';
                echo '<input type="hidden" name="nom_table" value="' . htmlspecialchars($nom_table) . '">';
                echo '<input type="hidden" name="tableau_excel" value="' . htmlspecialchars($tableau_excel) . '">';
                echo '<input type="submit" name="submit" value="Supprimer et Ajouter">';
                echo '</form>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
