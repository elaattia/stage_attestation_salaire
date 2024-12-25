<?php
// ajouter_poste.php
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
// Définition des types de postes (modifiez cette partie selon vos besoins réels)
$types_de_postes = ['horaire' => 'Horaire', 'mensuel' => 'Mensuel'];

// Vérifiez si le type est passé via l'URL
$type = $_GET['type'] ?? '';

// Vérifiez si le type est valide
if (!array_key_exists($type, $types_de_postes)) {
    die("Type de poste invalide.");
}

// Récupérer les rubriques existantes
$query_rubriques = "SELECT * FROM rubrique";
$result_rubriques = $conn->query($query_rubriques);
$rubriques = [];
while ($row = $result_rubriques->fetch_assoc()) {
    $rubriques[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Poste</title>
    <style>
        .container { padding: 20px; }
        .input-field { margin-bottom: 10px; }
        .btn { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background-color: #45a049; }
        .post_container { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .rubrique { margin-bottom: 5px; }
        .rubrique_input { display: flex; gap: 10px; margin-bottom: 5px; }
        .rubrique_input select, .rubrique_input input { width: 45%; padding: 5px; }
        .rubrique_input button { padding: 5px 10px; background-color: #f44336; color: white; border: none; cursor: pointer; }
        .rubrique_input button:hover { background-color: #e53935; }
    </style>
    <script>
        function ajouterRubrique() {
            var rubriqueContainer = document.getElementById('rubriqueContainer');
            var newRubrique = document.createElement('div');
            newRubrique.className = 'rubrique_input';
            newRubrique.innerHTML = `
                <input type="text" name="nom_rubrique[]" placeholder="Nom de la Rubrique" required>
                <input type="text" name="valeur_rubrique[]" placeholder="Valeur de la Rubrique" required>
                <button type="button" onclick="this.parentElement.remove()">Supprimer</button>
            `;
            rubriqueContainer.appendChild(newRubrique);
        }

        function ajouterRubriqueExistante() {
            var rubriqueContainer = document.getElementById('rubriqueContainer');
            var select = document.getElementById('rubriqueSelect');
            var selectedValue = select.value;
            var selectedText = select.options[select.selectedIndex].text;

            if (selectedValue === "") {
                alert("Veuillez sélectionner une rubrique.");
                return;
            }

            var existingRubrique = document.querySelector(`input[value="${selectedValue}"]`);

            if (existingRubrique) {
                alert("Cette rubrique est déjà ajoutée.");
                return;
            }

            var newRubrique = document.createElement('div');
            newRubrique.className = 'rubrique_input';
            newRubrique.innerHTML = `
                <input type="hidden" name="rubrique_code[]" value="${selectedValue}">
                <input type="text" name="rubrique_name[]" value="${selectedText}" readonly>
                <input type="text" name="valeur[]" placeholder="Valeur de la Rubrique" required>
                <button type="button" onclick="this.parentElement.remove()">Supprimer</button>
            `;
            rubriqueContainer.appendChild(newRubrique);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Ajouter un nouveau poste pour <?php echo htmlspecialchars($types_de_postes[$type]); ?></h2>
        <form id="formPoste" action="enregistrer_poste.php" method="POST">
            <div class="post_container">
                <div class="input-field">
                    <label for="nom_poste">Nom du Poste: </label>
                    <input type="text" id="nom_poste" name="nom_poste" required>
                </div>

                <!-- Liste déroulante pour les rubriques existantes -->
                <div class="input-field">
                    <label for="rubriqueSelect">Choisir une Rubrique: </label>
                    <select id="rubriqueSelect">
                        <option value="">-- Sélectionnez une rubrique --</option>
                        <?php foreach ($rubriques as $rubrique) : ?>
                            <option value="<?php echo htmlspecialchars($rubrique['code']); ?>">
                                <?php echo htmlspecialchars($rubrique['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn" onclick="ajouterRubriqueExistante()">Ajouter Rubrique Existante</button>
                </div>

                <!-- Div pour gérer les rubriques -->
                <div id="rubriqueContainer">
                    <!-- Initialement vide, les rubriques seront ajoutées ici -->
                </div>

                <button type="button" class="btn" onclick="ajouterRubrique()">Ajouter Rubrique</button>
            </div>

            <input type="hidden" name="type_poste" value="<?php echo htmlspecialchars($type); ?>">
            <button type="submit" class="btn">Enregistrer</button>
        </form>
    </div>
</body>
</html>
