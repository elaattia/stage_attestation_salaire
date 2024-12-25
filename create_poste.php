<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si les données sont envoyées via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['matricule']) && isset($_POST['ref']) && isset($_POST['brut_employe'])) {
        $matricule = $_POST['matricule'];
        $reference = $_POST['ref'];
        $brut_employe = $_POST['brut_employe'];
    } else {
        die("Les données requises ne sont pas présentes.");
    }
} else {
    die("Aucune donnée POST reçue.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un nouveau poste</title>
    <link rel="stylesheet" href="cssview.css">
</head>
<body>
    <h1>Créer un nouveau poste</h1>
    <form method="post" action="save_poste.php">
        <input type="hidden" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>">
        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($reference); ?>">
        <input type="hidden" name="brut_employe" value="<?php echo htmlspecialchars($brut_employe); ?>">
        
        <label for="nom_poste">Nom du Poste:</label>
        <input type="text" id="nom_poste" name="nom_poste" required>
        <br>

        <label for="type_poste">Type de Poste:</label>
        <select id="type_poste" name="type_poste" required>
            <option value="1">Horaire</option>
            <option value="2">Mensuel</option>
        </select>
        <br>

        <button class="btn" type="submit">Créer le poste</button>
    </form>
    <a href="view.php" class="btn">Retour à la recherche</a>
</body>
</html>
