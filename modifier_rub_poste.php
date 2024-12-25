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

// Vérifier que l'ID de l'attribut est fourni
if (!isset($_GET['id_att'])) {
    die("ID de l'attribut manquant.");
}

$id_att = $_GET['id_att'];

// Récupérer les données actuelles de l'attribut
$sql = "SELECT a.valeur, r.nom AS rubrique_nom
        FROM attribut a
        JOIN rubrique r ON a.code_rub = r.code
        WHERE a.id_att = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_att);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Aucun attribut trouvé pour l'ID donné.");
}

$row = $result->fetch_assoc();
$valeur = $row['valeur'];
$nom_rubrique = $row['rubrique_nom'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Rubrique</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Assure que la hauteur minimum soit de 100% de la hauteur de la vue */
            margin: 0;
            background-color: #e4e9f7;
        }


        h1 {
            text-align: center;
            color: #8783CE;
            padding: 20px;
            margin: 0;
            border-bottom: 2px solid #ddd;
        }

        .container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto; /* Garde le centrage horizontal */
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /*text-align: center; /* Centrage du contenu à l'intérieur */
        }


        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 18px;
            font-weight: 900;
            margin: 8px;
           
        }

        input[type="text"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #8783CE;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #357abd;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #8783CE;
            text-decoration: none;
            font-size: 16px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modifier Rubrique</h1>
        <form action="update_rubrique.php" method="POST">
            <input type="hidden" name="id_att" value="<?php echo htmlspecialchars($id_att); ?>">
            <label for="nom_rubrique">Nom de la Rubrique:</label>
            <input type="text" id="nom_rubrique" name="nom_rubrique" value="<?php echo htmlspecialchars($nom_rubrique); ?>" required>
            <label for="valeur_rubrique">Valeur:</label>
            <input type="text" id="valeur_rubrique" name="valeur_rubrique" value="<?php echo htmlspecialchars($valeur); ?>" required>
            <input type="submit" value="Enregistrer">
        </form>
        <a href="update_poste.php">Retour à la liste des postes</a>
    </div>
</body>
</html>
