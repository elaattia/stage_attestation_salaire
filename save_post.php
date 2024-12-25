<?php
// Connexion à la base de données
$mysqli = new mysqli("localhost", "username", "password", "database");

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

// Récupérer les données du formulaire
$nom_poste = $_POST['nom_poste']; // Supposons que vous avez un champ pour le nom du poste
$new_rubrique_names = $_POST['new_rubrique_name'];
$new_rubrique_values = $_POST['new_rubrique_value'];

// Ajouter le poste à la base de données
// Remplacez cette ligne par votre logique d'insertion du poste
// Exemple : $mysqli->query("INSERT INTO poste (nom_poste) VALUES ('$nom_poste')");

// Récupérer l'ID du poste ajouté, si nécessaire
$post_id = $mysqli->insert_id;

// Ajouter les nouvelles rubriques
foreach ($new_rubrique_names as $index => $name) {
    $value = $new_rubrique_values[$index];
    
    // Échapper les valeurs pour éviter les injections SQL
    $name = $mysqli->real_escape_string($name);
    $value = $mysqli->real_escape_string($value);

    // Ajouter la rubrique
    $query = "INSERT INTO rubrique (nom) VALUES ('$name')";
    if ($mysqli->query($query) === TRUE) {
        $code_rub = $mysqli->insert_id;
        
        // Ajouter l'attribut correspondant
        $query = "INSERT INTO attribut (code_rub, valeur) VALUES ('$code_rub', '$value')";
        if (!$mysqli->query($query)) {
            echo "Erreur : " . $mysqli->error;
        }
    } else {
        echo "Erreur : " . $mysqli->error;
    }
}

// Fermer la connexion
$mysqli->close();

// Redirection ou affichage d'un message de succès
header("Location: success.php");
exit();
?>
