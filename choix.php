<?php
// choix.php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Page de Choix</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Bienvenue</header>
            <div class="button-container">
                <a href="update_data.php" class="btn-large">Update Data</a>
            </div>
            <div class="button-container">
                <a href="attestation_travail.php" class="btn-large">Attestation de Travail</a>
            </div>
            <div class="button-container">
                <a href="update_poste.php" class="btn-large">Update Poste</a>
            </div>

        </div>
    </div>
</body>
</html>
