<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$code = $_GET['code'];

// Préparer et exécuter la requête pour récupérer les valeurs des attributs
$stmt = $conn->prepare("SELECT valeur FROM rubrique_valeur WHERE rubrique_code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<select name='valeur'>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['valeur']) . "'>" . htmlspecialchars($row['valeur']) . "</option>";
    }
    echo "</select>";
} else {
    echo "Aucune valeur trouvée.";
}
$stmt->close();
$conn->close();
?>
