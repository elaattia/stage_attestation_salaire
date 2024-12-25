<?php
// update_poste.php
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

// Requête pour récupérer les postes avec les rubriques et les attributs, triés par id_type et id_post
$query = "
    SELECT p.id_post, p.nom AS poste_nom, p.id_type, r.nom AS rubrique_nom, a.valeur, a.id_att
    FROM poste p
    JOIN poste_rubrique_attr pra ON pra.id_post = p.id_post
    JOIN attribut a ON a.id_att = pra.id_att
    JOIN rubrique r ON r.code = a.code_rub
    ORDER BY p.id_type, p.id_post
";
$result = $conn->query($query);

if (!$result) {
    die("Échec de la requête : " . $conn->error);
}

// Structure pour stocker les résultats par type de poste
$postes_par_type = ['horaire' => [], 'mensuel' => []];

// Structure pour stocker les noms des types de poste
$types_de_postes = ['horaire' => 'Horaire', 'mensuel' => 'Mensuel'];

while ($row = $result->fetch_assoc()) {
    $type = ($row['id_type'] == 1) ? 'horaire' : 'mensuel';
    $postes_par_type[$type][$row['id_post']][] = [
        'poste_nom' => $row['poste_nom'],
        'rubrique' => $row['rubrique_nom'],
        'valeur' => $row['valeur'],
        'id_att' => $row['id_att'] // Ajout de l'ID de l'attribut pour les liens Modifier/Supprimer
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Update Poste</title>
    <style>
        /* Styles CSS */
        .container { display: flex; flex-direction: column; gap: 20px; margin: 20px; }
        .type-header { font-size: 20px; font-weight: bold; background-color: #e0e0e0; padding: 10px; border-radius: 5px; margin-top: 20px; }
        .post-container { padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .post-header { font-size: 18px; font-weight: bold; margin-bottom: 10px; background-color: #f4f4f4; padding: 10px; border-radius: 5px; }
        .post-details { border-top: 1px solid #ddd; padding-top: 10px; }
        .post-details table { width: 100%; border-collapse: collapse; }
        .post-details th, .post-details td { border: 1px solid #ddd; padding: 8px; }
        .post-details th { background-color: #f4f4f4; text-align: left; }
        .btn { padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 5px; }
        .btn:hover { background-color: #45a049; }
        .btn-delete { background-color: #f44336; }
        .btn-delete:hover { background-color: #e41f1c; }
        .btn-container { display: flex; }
        .success-message { color: green; font-weight: bold; margin: 20px 0; background-color: #e0f8e0; padding: 10px; border: 1px solid green; border-radius: 5px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
       

        <!-- Affichage des postes par type -->
        <?php foreach ($postes_par_type as $type => $postes): ?>
            <div class="type-header">
                <?php echo htmlspecialchars($types_de_postes[$type]); ?>
                <!-- Bouton Ajouter Poste -->
                <a href="ajouter_poste.php?type=<?php echo $type; ?>" class="btn">Ajouter Poste</a>
            </div>
            
            <?php if (!empty($postes)): ?>
                <?php foreach ($postes as $id_post => $details): ?>
                    <div class="post-container">
                        <div class="post-header">
                            <?php echo htmlspecialchars($details[0]['poste_nom']); ?>
                            <!-- Bouton Supprimer Poste -->
                            <form action="supprimer_poste.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
                                <button type="submit" class="btn btn-delete">Supprimer Poste</button>
                            </form>
                        </div>
                        <div class="post-details">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom de la Rubrique</th>
                                        <th>Valeur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $detail): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detail['rubrique']); ?></td>
                                            <td><?php echo htmlspecialchars($detail['valeur']); ?></td>
                                            <td class="btn-container">
                                                <a href="modifier_rub_poste.php?id_att=<?php echo $detail['id_att']; ?>" class="btn">Modifier</a>
                                                <a href="supprimer_rub_poste.php?id_att=<?php echo $detail['id_att']; ?>" class="btn btn-delete">Supprimer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Formulaire pour ajouter une nouvelle rubrique -->
                        <form id="ajout_rubrique_form_<?php echo $id_post; ?>" class="ajout_rubrique_form" method="POST">
                            <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
                            <input type="text" name="nouveau_nom_rubrique" placeholder="Nom de la nouvelle rubrique" required>
                            <input type="text" name="nouvelle_valeur_rubrique" placeholder="Valeur de la nouvelle rubrique" required>
                            <button type="submit" class="btn">Ajouter Rubrique</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun poste disponible.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <script>
        $(document).ready(function(){
            $('.ajout_rubrique_form').on('submit', function(e){
                e.preventDefault(); // Empêcher le rechargement de la page

                var form = $(this);

                $.ajax({
                    type: 'POST',
                    url: 'ajouter_newrub_poste.php',
                    data: form.serialize(),
                    success: function(response) {
                        console.log('Réponse brute : ', response); // Afficher la réponse brute pour diagnostic

                        // Vérifiez si la réponse est un JSON valide
                        try {
                            // Essayez de parser la réponse JSON
                            var data = typeof response === 'string' ? JSON.parse(response) : response;

                            if (data.success) {
                                // Ajouter la nouvelle rubrique à la table
                                form.closest('.post-container').find('table tbody').append(
                                    '<tr><td>' + data.rubrique_nom + '</td><td>' + data.valeur + '</td><td class="btn-container"><a href="modifier_rub_poste.php?id_att=' + data.id_att + '" class="btn">Modifier</a><a href="supprimer_rub_poste.php?id_att=' + data.id_att + '" class="btn btn-delete">Supprimer</a></td></tr>'
                                );

                                // Réinitialiser le formulaire après le succès
                                form[0].reset();
                            } else {
                                alert('Erreur : ' + data.error);
                            }
                        } catch (error) {
                            // Si JSON.parse échoue, affichez l'erreur et la réponse brute
                            alert('Erreur de format de réponse : ' + error + '\nRéponse brute : ' + response);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Erreur AJAX : ', textStatus, errorThrown);
                        alert('Erreur AJAX : ' + textStatus);
                    }
                });
            });
        });
    </script>
</body>
</html>
