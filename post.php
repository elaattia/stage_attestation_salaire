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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['matricule']) && isset($_POST['id_post'])) {
        $matricule = trim($_POST['matricule']);
        $id_post = trim($_POST['id_post']);

        // Assure-toi que le matricule et l'id_post sont bien formatés (par exemple, des nombres entiers)
        if (preg_match('/^\d+$/', $matricule) && preg_match('/^\d+$/', $id_post)) {
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

                // Récupérer les informations des attributs pour le poste spécifique
                $query_poste = "SELECT p.id_post, p.nom, p.id_type, t.type, a.id_att, a.valeur
                                FROM poste p
                                JOIN type_poste t ON p.id_type = t.id_type
                                JOIN poste_rubrique_attr pra ON p.id_post = pra.id_post
                                JOIN attribut a ON pra.id_att = a.id_att
                                WHERE p.id_post = ?";
                $stmt_poste = $conn->prepare($query_poste);
                $stmt_poste->bind_param("s", $id_post);
                $stmt_poste->execute();
                $result_poste = $stmt_poste->get_result();

                $attributs = [];
                $attributs_total = 0;

                if ($result_poste->num_rows > 0) {
                    $poste = $result_poste->fetch_assoc();
                    $id_type = $poste['id_type'];
                    $nom_poste = $poste['nom'];
                    $type_poste = $poste['type'];

                    do {
                        $attributs[] = [
                            'id' => $poste['id_att'],
                            'valeur' => $poste['valeur']
                        ];
                        $attributs_total += $poste['valeur'];
                    } while ($poste = $result_poste->fetch_assoc());

                    // Calcul du Salaire_de_Base
                    if ($id_type == 1) { // Poste horaire
                        $salaire_de_base = (2080 / 12) * $employe['Heure_Normal'];
                    } elseif ($id_type == 2) { // Poste mensuel
                        $salaire_de_base = $employe['Salaire_Base'];
                    } else {
                        echo "<h1>Type de poste inconnu</h1>";
                        exit;
                    }

                    // Calcul du prime d'ancienneté
                    $prime_anciennete_valeur = ($salaire_de_base + $differentiel) * 0.15;

                    // Calcul du brut théorique pour le poste
                    $brut_theorique = $attributs_total + $salaire_de_base + $differentiel + $prime_anciennete_valeur;

                    // Afficher les détails du poste trouvé
                    ?>
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                        <meta charset="UTF-8">
                        <title>Résultat de la Recherche</title>
                        <link rel="stylesheet" href="styles.css">
                    </head>
                    <body>
                        <h1>Employé trouvé</h1>
                        <p>Poste : <?php echo $nom_poste; ?> (Type : <?php echo $type_poste; ?>)</p>
                        <p>Valeur du salaire de base : <?php echo number_format($salaire_de_base, 3); ?></p>
                        <p>Brut théorique : <?php echo number_format($brut_theorique, 3); ?></p>
                        <p>brut_employe : <?php echo number_format($brut_employe, 3); ?></p>
                        <h2>Détails des attributs</h2>
                        <ul>
                            <?php foreach ($attributs as $attribut) { ?>
                                <li>ID Attribut : <?php echo $attribut['id']; ?> - Valeur : <?php echo number_format($attribut['valeur'], 3); ?></li>
                            <?php } ?>
                        </ul>
                        <p><a href="attestation_pdf.php?matricule=<?php echo $matricule; ?>"><button>Générer Attestation</button></a></p>
                    </body>
                    </html>
                    <?php
                } else {
                    echo "<h1>Aucun attribut trouvé pour
                    le poste</h1>";
                }
                $stmt_poste->close();
            } else {
                echo "<h1>Matricule introuvable</h1>";
            }
    
            $stmt_employe->close();
        } else {
            echo "<h1>Matricule ou ID de poste non valide</h1>";
        }
    
        $conn->close();
    } else {
        echo "<h1>Matricule ou ID de poste non défini dans POST</h1>";
    }
} else {
    echo "<h1>Formulaire non soumis</h1>";
    }
    ?>