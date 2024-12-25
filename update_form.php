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

// Récupérer les paramètres de l'URL
$matricule = $_GET['matricule'];
$id_post = $_GET['id_post'];

// Récupérer les détails de l'employé
$query_employe = "SELECT Salaire_Base, Differentiel, Heure_Normal, Brut_Theorique_STD FROM employe WHERE matricule = ?";
$stmt_employe = $conn->prepare($query_employe);
$stmt_employe->bind_param("s", $matricule);
$stmt_employe->execute();
$result_employe = $stmt_employe->get_result();

if ($result_employe->num_rows > 0) {
    $employe = $result_employe->fetch_assoc();
} else {
    echo "Employé introuvable.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mettre à jour les informations de l'employé</title>
    <link rel="stylesheet" href="cssview.css">
</head>
<body>
    <h1>Mettre à jour les informations de l'employé</h1>
    <form method="post" action="update_employe.php">
        <input type="hidden" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>">
        <input type="hidden" name="id_post" value="<?php echo htmlspecialchars($id_post); ?>">

        <p>
            <label for="salaire_base">Salaire de Base:</label>
            <input type="text" name="salaire_base" id="salaire_base" value="<?php echo htmlspecialchars($employe['Salaire_Base']); ?>">
        </p>
        <p>
            <label for="differentiel">Différentiel:</label>
            <input type="text" name="differentiel" id="differentiel" value="<?php echo htmlspecialchars($employe['Differentiel']); ?>">
        </p>
        <p>
            <label for="heure_normal">Heures Normales:</label>
            <input type="text" name="heure_normal" id="heure_normal" value="<?php echo htmlspecialchars($employe['Heure_Normal']); ?>">
        </p>
        <p>
            <label for="brut_theorique">Brut Théorique STD:</label>
            <input type="text" name="brut_theorique" id="brut_theorique" value="<?php echo htmlspecialchars($employe['Brut_Theorique_STD']); ?>">
        </p>
        <input type="submit" value="Enregistrer">
    </form>
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>
