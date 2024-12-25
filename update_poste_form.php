<?php
session_start();

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

$poste_nom = isset($_POST['poste_nom']) ? $_POST['poste_nom'] : '';

// Récupérer les détails du poste sélectionné
$query = "
    SELECT p.id_post, p.nom AS poste_nom, r.nom AS rubrique_nom, a.valeur
    FROM poste p
    JOIN poste_rubrique_attr pra ON pra.id_post = p.id_post
    JOIN attribut a ON a.id_att = pra.id_att
    JOIN rubrique r ON r.code = a.code_rub
    WHERE p.nom = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $poste_nom);
$stmt->execute();
$result = $stmt->get_result();

$poste_details = [];
while ($row = $result->fetch_assoc()) {
    $poste_details[] = [
        'id_post' => $row['id_post'],
        'rubrique' => $row['rubrique_nom'],
        'valeur' => $row['valeur']
    ];
}

// Récupérer les rubriques existantes pour les afficher dans le sélecteur
$select_rubriques_query = "SELECT nom FROM rubrique";
$select_result = $conn->query($select_rubriques_query);
$rubriques = [];
while ($row = $select_result->fetch_assoc()) {
    $rubriques[] = $row['nom'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Modifier Poste</title>
    <style>
        .container {
            margin: 20px;
        }
        .post-form {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .post-form input, .post-form select, .post-form button {
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modifier Poste</h1>
        <form action="update_poste_process.php" method="post">
            <input type="hidden" name="id_post" value="<?php echo isset($poste_details[0]['id_post']) ? htmlspecialchars($poste_details[0]['id_post']) : ''; ?>">

            <label for="poste_nom">Nom du Poste :</label>
            <input type="text" id="poste_nom" name="poste_nom" value="<?php echo htmlspecialchars($poste_nom); ?>" required>

            <div id="rubriques-container">
                <?php foreach ($poste_details as $index => $detail): ?>
                    <div class="post-form">
                        <label for="rubrique_<?php echo $index; ?>">Nom de la Rubrique :</label>
                        <input type="text" id="rubrique_<?php echo $index; ?>" name="rubriques[<?php echo $index; ?>][rubrique]" value="<?php echo htmlspecialchars($detail['rubrique']); ?>" required>

                        <label for="valeur_<?php echo $index; ?>">Valeur :</label>
                        <input type="number" id="valeur_<?php echo $index; ?>" name="rubriques[<?php echo $index; ?>][valeur]" step="0.01" value="<?php echo htmlspecialchars($detail['valeur']); ?>" required>
                    </div>
                <?php endforeach; ?>
            </div>

            <label for="new_rubrique">Ajouter Rubrique :</label>
            <select id="new_rubrique" name="new_rubrique">
                <option value="">Sélectionner une rubrique</option>
                <?php foreach ($rubriques as $rubrique): ?>
                    <option value="<?php echo htmlspecialchars($rubrique); ?>"><?php echo htmlspecialchars($rubrique); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="addRubrique()">Ajouter Rubrique</button>
            <button type="button" onclick="removeRubrique()">Supprimer Rubrique</button>
            <button type="submit">Enregistrer</button>
        </form>

        <script>
            let rubriqueIndex = <?php echo count($poste_details); ?>;

            function addRubrique() {
                const container = document.getElementById('rubriques-container');
                const newRubrique = document.createElement('div');
                newRubrique.className = 'post-form';
                newRubrique.innerHTML = `
                    <label for="rubrique_${rubriqueIndex}">Nom de la Rubrique :</label>
                    <input type="text" id="rubrique_${rubriqueIndex}" name="rubriques[${rubriqueIndex}][rubrique]" required>

                    <label for="valeur_${rubriqueIndex}">Valeur :</label>
                    <input type="number" id="valeur_${rubriqueIndex}" name="rubriques[${rubriqueIndex}][valeur]" step="0.01" required>
                `;
                container.appendChild(newRubrique);
                rubriqueIndex++;
            }

            function removeRubrique() {
                const container = document.getElementById('rubriques-container');
                const select = document.getElementById('remove_rubrique');
                const selectedIndex = select.selectedIndex;
                if (selectedIndex > 0) {
                    container.removeChild(container.children[selectedIndex - 1]);
                    select.remove(selectedIndex);
                    rubriqueIndex--;
                }
            }
        </script>
    </div>
</body>
</html>
