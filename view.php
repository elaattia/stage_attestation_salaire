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

$salaire_de_base = null;
$differentiel = null;
$prime_anciennete_valeur = null;
$brut_theorique = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['matricule']) && isset($_POST['ref'])) {
        $matricule = $_POST['matricule'];
        $reference = $_POST['ref'];
        $has_indemnite = isset($_POST['has_indemnite']) ? $_POST['has_indemnite'] : 'non';
        $indemnite_value = isset($_POST['indemnite']) ? (float)$_POST['indemnite'] : 0;


        // Récupérer les informations de l'employé
        $query_employe = "SELECT * FROM employe WHERE Matricule = ?";
        $stmt_employe = $conn->prepare($query_employe);
        $stmt_employe->bind_param("s", $matricule);
        $stmt_employe->execute();
        $result_employe = $stmt_employe->get_result();

        if ($result_employe->num_rows > 0) {
            $employe = $result_employe->fetch_assoc();
            $brut_employe = $employe['Brut_Theorique_STD'];
            $differentiel = $employe['Differentiel'];
            
            // Rechercher les postes horaires et mensuels
            $query_postes = "SELECT p.id_post, p.nom, p.id_type, t.type, SUM(a.valeur) as attributs_total
                             FROM poste p
                             JOIN type_poste t ON p.id_type = t.id_type
                             JOIN poste_rubrique_attr pra ON p.id_post = pra.id_post
                             JOIN attribut a ON pra.id_att = a.id_att
                             GROUP BY p.id_post";
            $result_postes = $conn->query($query_postes);

            $postes_correspondants = [];
            $primes = [0, 0.03, 0.06, 0.09, 0.12, 0.15];
            $meilleure_prime = 0;

            while ($poste = $result_postes->fetch_assoc()) {
                $id_post = $poste['id_post'];
                $id_type = $poste['id_type'];
                $attributs_total = $poste['attributs_total'];

                // Calcul du Salaire_de_Base
                if ($id_type == 1) {
                    $salaire_de_base = 173.33 * $employe['Heure_Normal'];
                } elseif ($id_type == 2) {
                    $salaire_de_base = $employe['Salaire_Base'];
                } else {
                    continue;
                }

                foreach ($primes as $prime_taux) {

                    

                    $prime_anciennete_valeur = ($salaire_de_base + $differentiel) * $prime_taux;
                    $brut_theorique = $attributs_total + $salaire_de_base + $differentiel + $prime_anciennete_valeur;
                    
                    // Si une indemnité forfaitaire a été saisie et la rubrique 10010 est présente
                    if ($has_indemnite == 'oui') {
                        $query_rubrique = "SELECT * FROM rubrique WHERE code = 10010";
                        $result_rubrique = $conn->query($query_rubrique);

                        if ($result_rubrique->num_rows > 0) {
                            // Ajouter l'indemnité saisie dans le calcul
                            $brut_theorique += $indemnite_value;
                        }
                    }

                    $precision_actuelle = abs($brut_employe - $brut_theorique);

                   
                    if (abs($precision_actuelle ) < 0.001) {
                        if ($prime_taux > $meilleure_prime) {
                            $meilleure_prime = $prime_taux;
                        }
                        $prime_anciennete_valeur = ($salaire_de_base + $differentiel) * $meilleure_prime;
                        $meilleur_prime_anciennete_valeur = $prime_anciennete_valeur;
                        $x=($salaire_de_base + $differentiel) *$meilleure_prime;
                        $brut_theorique = $attributs_total + $salaire_de_base + $differentiel + $prime_anciennete_valeur;
                        $final_brut_theorique = $brut_theorique;
                        echo "<script>console.log(" . json_encode($x) . ");</script>";
                        echo "<script>console.log(" . json_encode($prime_anciennete_valeur) . ");</script>";
                        echo "<script>console.log(" . json_encode($final_brut_theorique) . ");</script>";
                        $postes_correspondants[] = [
                            'id_post' => $id_post,
                            'nom' => $poste['nom'],
                            'type' => $poste['type'],
                            'attributs_total' => $attributs_total,
                            'salaire_de_base' => $salaire_de_base,
                            'meilleur_prime_anciennete_valeur' => $meilleur_prime_anciennete_valeur,
                            'brut_theorique' => $final_brut_theorique,
                            'indemnite' => $indemnite_value  // Affichage de l'indemnité
                        ];
                        echo "<script>console.log(" . json_encode($postes_correspondants) . ");</script>";
                        
                        
                        $_SESSION['postes_correspondants'] = $postes_correspondants;
                        
                    }
                }
            }
            
            echo "<script>console.log(" . json_encode($meilleur_prime_anciennete_valeur) . ");</script>";
            if (count($postes_correspondants) > 0) {
                ?>
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Choisir un poste</title>
                    <link rel="stylesheet" href="cssview.css">
                    <script>
                        
                        // Afficher les valeurs dans la console
                        console.log("Valeur finale de prime_anciennete_valeur: <?php echo $meilleur_prime_anciennete_valeur; ?>");
                        console.log("Valeur finale de indemnite_value: <?php echo $indemnite_value; ?>");
                        console.log("nb poste: <?php echo count($postes_correspondants); ?>");
                    </script>
                </head>
                <body>
                    <h1>Choisir un poste</h1>
                    <form method="post" action="">
                        <input type="hidden" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>">
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($reference); ?>">
                        <input type="hidden" name="poste" value="<?php echo htmlspecialchars($poste['id_post']); ?>">
                        <input type="hidden" name="meilleur_prime_anciennete_valeur" value="<?php echo htmlspecialchars($meilleur_prime_anciennete_valeur); ?>">
                        <input type="hidden" name="indemnite" value="<?php echo htmlspecialchars($indemnite_value); ?>">
                        <input type="hidden" name="has_indemnite" value="<?php echo htmlspecialchars($has_indemnite); ?>"> <!-- AJOUT DE CE CHAMP -->

                        <select name="poste" id="poste">
                            <?php foreach ($postes_correspondants as $poste) { ?>
                                <option value="<?php echo htmlspecialchars($poste['id_post']); ?>">
                                    <?php echo htmlspecialchars($poste['nom']); ?> (Type : <?php echo htmlspecialchars($poste['type']); ?>)
                                    
                                </option>
                            <?php } ?>
                        </select>
                        <br>
                        <button class="btn" type="submit" name="choisir_poste">Choisir le poste</button>
                    </form>

                </body>
                </html>
                <?php
               
            } else {
                // Afficher un message indiquant que le poste n'existe pas
                ?>
                <!DOCTYPE html>
                <html lang="fr">
                <head>
                    <meta charset="UTF-8">
                    <title>Poste non trouvé</title>
                    <link rel="stylesheet" href="cssview.css">
                </head>
                <body>
                    <h1>Poste non trouvé</h1>
                    <p class="message">Aucun poste correspondant trouvé. Veuillez en créer un.</p>
                    <form method="post" action="update_poste.php">
                        <input type="hidden" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>">
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($reference); ?>">
                        <button class="btn" type="submit">Créer un poste</button>
                    </form>
                    <form method="post" action="attestation_travail.php">
                        <button class="btn" type="submit">Retourner</button>
                    </form>
                </body>
                </html>
                <?php
            }

        } else {
            // Afficher un message indiquant que l'employé n'est pas trouvé
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Employé non trouvé</title>
                <link rel="stylesheet" href="cssview.css">
            </head>
            <body>
                <h1>Employé non trouvé</h1>
                <p class="message">Aucun employé trouvé avec le matricule spécifié. Veuillez vérifier et réessayer.</p>
                <form method="post" action="attestation_travail.php">
                    <button class="btn" type="submit">réessayer</button>
                </form>
            </body>
            </html>
            <?php
        }


    }

    if (isset($_POST['choisir_poste'])) {
        include 'choisirposte.php';
    }
}

$conn->close();
?>
