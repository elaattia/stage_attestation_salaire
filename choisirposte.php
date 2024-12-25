
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
$id_post = $_POST['poste'];

$postes_correspondants = $_SESSION['postes_correspondants'] ?? [];

foreach ($postes_correspondants as $poste) {
    if ($poste['id_post'] == $id_post) {
        
        $brut_theorique = $poste['brut_theorique'];
        $prime_anciennete_valeur = $poste['meilleur_prime_anciennete_valeur'];
        $indemnite = $poste['indemnite'];
        break;
    }
}

//$id_post = $_POST['poste'];
//$prime_anciennete_valeur = $_POST['prime_anciennete_valeur'];
//$indemnite = $_POST['indemnite'];

        // Récupérer les détails du poste choisi
        $query_poste_details = "SELECT p.nom, p.id_type, t.type, SUM(a.valeur) as attributs_total
                                FROM poste p
                                JOIN type_poste t ON p.id_type = t.id_type
                                JOIN poste_rubrique_attr pra ON p.id_post = pra.id_post
                                JOIN attribut a ON pra.id_att = a.id_att
                                WHERE p.id_post = ?
                                GROUP BY p.id_post";
        $stmt_poste_details = $conn->prepare($query_poste_details);
        $stmt_poste_details->bind_param("i", $id_post);
        $stmt_poste_details->execute();
        $result_poste_details = $stmt_poste_details->get_result();

        if ($result_poste_details->num_rows > 0) {
            $poste = $result_poste_details->fetch_assoc();
            
            $salaire_de_base = ($poste['id_type'] == 1) ? 173.33 * $employe['Heure_Normal'] : $employe['Salaire_Base'];
            //$prime_anciennete_valeur = ($salaire_de_base + $differentiel) * $meilleure_prime;
            $brut_theorique = $poste['attributs_total'] + $salaire_de_base + $differentiel + $prime_anciennete_valeur+$indemnite ;

            // Récupérer les rubriques du poste
            $query_rubriques = "SELECT r.nom, a.valeur, r.code
                                FROM poste_rubrique_attr pra
                                JOIN attribut a ON pra.id_att = a.id_att
                                JOIN rubrique r ON r.code = a.code_rub
                                WHERE pra.id_post = ?";
            $stmt_rubriques = $conn->prepare($query_rubriques);
            $stmt_rubriques->bind_param("i", $id_post);
            $stmt_rubriques->execute();
            $result_rubriques = $stmt_rubriques->get_result();
            $rubriques_details = '';

            while ($rubrique = $result_rubriques->fetch_assoc()) {
                $rubriques_details .= '<p>
                <strong><label for="valeur_rubrique">'.htmlspecialchars($rubrique['nom']).':</label></strong>';
                
                // Affichage statique de la valeur de la rubrique
                if ($rubrique['code'] == '10010') {
                    $rubriques_details .= '<span id="valeur_rubrique_' . htmlspecialchars($rubrique['code']) . '">'
                                          . htmlspecialchars($indemnite) . '</span>';
                } else {
                    $rubriques_details .= '<span id="valeur_rubrique_' . htmlspecialchars($rubrique['code']) . '">'
                                          . htmlspecialchars($rubrique['valeur']) . '</span>';
                }
                
                $rubriques_details .= '</p>';
            }
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Détails du poste</title>
                <link rel="stylesheet" href="cssview.css">
            </head>
            <body>
                <h1>Détails du poste choisi</h1>
                <p><strong>Nom du poste</strong> : <?php echo htmlspecialchars($poste['nom']); ?></p>
                <p><strong>Type de poste</strong> : <?php echo htmlspecialchars($poste['type']); ?></p>
            
                <p>
                    <strong><label for="salaire_de_base">Salaire de Base:</label></strong>
                    <span><?php echo htmlspecialchars($salaire_de_base, ENT_QUOTES, 'UTF-8'); ?></span>
                </p>

                <p>
                    <strong><label for="Différentiel">Différentiel:</label></strong>
                    <span><?php echo htmlspecialchars($differentiel, ENT_QUOTES, 'UTF-8'); ?></span>
                </p>

                <p>
                    <strong><label for="Prime_anciennete">Prime d'ancienneté:</label></strong>
                    <span><?php echo htmlspecialchars($prime_anciennete_valeur, ENT_QUOTES, 'UTF-8'); ?></span>
                </p>

                <p>
                    <strong><label for="Brut_théorique">Brut théorique:</label></strong>
                    <span><?php echo htmlspecialchars($brut_theorique, ENT_QUOTES, 'UTF-8'); ?></span>
                </p>


                <script>
                // Fonction pour afficher ou masquer le champ de saisie
                function checkAddOption() {
                    const select = document.getElementById('my-select');
                    const input = document.getElementById('custom-option');

                    if (select.value === 'add-new') {
                    input.style.display = 'inline-block';
                    input.focus(); // Met le focus sur le champ de saisie
                    } else {
                    input.style.display = 'none';
                    }
                }

                // Fonction pour ajouter la nouvelle option à la liste déroulante
                document.getElementById('custom-option').addEventListener('change', function() {
                    const select = document.getElementById('my-select');
                    const input = document.getElementById('custom-option');
                    const newValue = input.value.trim();

                    if (newValue) {
                    // Créer une nouvelle option
                    const newOption = document.createElement('option');
                    newOption.value = newValue;
                    newOption.text = newValue;
                    select.add(newOption);
                    
                    // Sélectionner la nouvelle option
                    select.value = newValue;
                    
                    // Réinitialiser le champ de saisie
                    input.value = '';
                    input.style.display = 'none';
                    }
                });
                </script>

                <form method="post" action="update.php">
                    <p></p>
                    <input type="hidden" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>">
                    <input type="hidden" name="ref" value="<?php echo htmlspecialchars($reference); ?>">
                    <input type="hidden" name="poste" value="<?php echo htmlspecialchars($id_post); ?>">
                    <?php echo $rubriques_details; ?>
                    <br>
                    
                </form>

                <?php
                $salaire_base = isset($salaire_de_base) ? number_format($salaire_de_base, 3) : '';
                $differentiel = isset($differentiel) ? number_format($differentiel, 3) : '';
                $prime_anciennete_valeur = isset($prime_anciennete_valeur) ? number_format($prime_anciennete_valeur, 5) : '';
                
                $url = "attestation_pdf.php?matricule=" . urlencode($matricule) .
                    "&ref=" . urlencode($reference) .
                    "&id_post=" . urlencode($id_post) .
                    "&salaire_base=" . urlencode($salaire_base) .
                    "&differentiel=" . urlencode($differentiel) .
                    "&prime_anciennete_valeur=" . urlencode($prime_anciennete_valeur).
                    "&indemnite=" . urlencode($indemnite);
                
                ?>

                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Gestion des Rubriques</title>
                    <style>
                        .field {
                            margin-bottom: 10px;
                        }
                        .btn {
                            padding: 10px 15px;
                            background-color: #007bff;
                            color: white;
                            border: none;
                            cursor: pointer;
                        }
                    </style>
                    
                </head>
                <body>
                
            </body>
            </html>
       
                <div class="field">
                    <a href="<?php echo htmlspecialchars($url); ?>"><input type="submit" class="btn" value="Générer Attestation"></a>
                </div>

                <div class="field">
                    <a href="update_form.php?matricule=<?php echo htmlspecialchars($matricule); ?>&id_post=<?php echo htmlspecialchars($id_post); ?>">
                        <input type="submit" class="btn" value="Mettre à jour">
                    </a>
                </div>
                   
            </body>
            </html>
            <?php
        } else {
            echo "Détails du poste introuvables.";
        }
