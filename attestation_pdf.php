<?php
require('fpdf/fpdf.php'); // Assurez-vous que ce chemin est correct

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

if (isset($_GET['matricule'])) {
    $matricule = $_GET['matricule'];
    $reference = isset($_GET['ref']) ? $_GET['ref'] : '';
    $id_post = isset($_GET['id_post']) ? intval($_GET['id_post']) : 0;
    $prime_anciennete_valeur = isset($_GET['prime_anciennete_valeur']) ? floatval($_GET['prime_anciennete_valeur']) : 0;
    $indemnite = isset($_GET['indemnite']) ? floatval($_GET['indemnite']) : 0;


    // Récupérer les informations de l'employé
    $query_employe = "SELECT * FROM employe WHERE Matricule = ?";
    $stmt_employe = $conn->prepare($query_employe);
    $stmt_employe->bind_param("s", $matricule);
    $stmt_employe->execute();
    $result_employe = $stmt_employe->get_result();

    if ($result_employe->num_rows > 0) {
        $employe = $result_employe->fetch_assoc();
        $nom = $employe['Nom'];
        $prenom = $employe['Prenom'];
        $brut_employe = floatval($employe['Brut_Theorique_STD']);
        $differentiel = floatval($employe['Differentiel']);

        // Récupérer les informations du poste
        $query_poste = "SELECT p.nom, p.id_type
                        FROM poste p
                        WHERE p.id_post = ?";
        $stmt_poste = $conn->prepare($query_poste);
        $stmt_poste->bind_param("i", $id_post);
        $stmt_poste->execute();
        $result_poste = $stmt_poste->get_result();
        $poste = $result_poste->fetch_assoc();

        // Calcul du Salaire_de_Base
        if ($poste['id_type'] == 1) { // Poste horaire
            $salaire_de_base = floatval($employe['Heure_Normal']) * 173.33;
        } elseif ($poste['id_type'] == 2) { // Poste mensuel
            $salaire_de_base = floatval($employe['Salaire_Base']);
        } else {
            die("Type de poste inconnu.");
        }

        // Récupérer les rubriques du poste
        $query_rubriques = "SELECT r.nom, a.valeur
                            FROM poste_rubrique_attr pra
                            JOIN attribut a ON pra.id_att = a.id_att
                            JOIN rubrique r ON r.code = a.code_rub
                            WHERE pra.id_post = ?";
        $stmt_rubriques = $conn->prepare($query_rubriques);
        $stmt_rubriques->bind_param("i", $id_post);
        $stmt_rubriques->execute();
        $result_rubriques = $stmt_rubriques->get_result();
        $rubriques_details = [];

        while ($rubrique = $result_rubriques->fetch_assoc()) {
            $valeur = floatval($rubrique['valeur']);
            if ($valeur > 0) { // Ajouter la rubrique seulement si la valeur est supérieure à zéro
                $rubriques_details[] = [
                    'nom' => $rubrique['nom'],
                    'valeur' => $valeur
                ];
            }
        }

        // Calcul du total brut
        $total_brut = $salaire_de_base + $differentiel + array_sum(array_column($rubriques_details, 'valeur')) + $prime_anciennete_valeur + $indemnite;

        // Initialize FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Header
        $pdf->Cell(0, 10, 'Ref: ' . $reference . '/' . date("Y"), 0, 0, 'L');
        $pdf->Cell(0, 10, 'M\'saken, le ' . date("d/m/Y"), 0, 1, 'R');
        
        // Title
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'ATTESTATION DE SALAIRE', 0, 1, 'C');

        // Body
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, "Nous soussignes, Societe Tunisienne des Industries de Pneumatiques, attestons que le salaire mensuel brut de monsieur $nom $prenom, Mle $matricule, est decompose comme suit:");

        // Rubriques
        $pdf->Ln(10);
        
        if ($salaire_de_base > 0) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 10, mb_convert_encoding("SALAIRE DE BASE", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, number_format($salaire_de_base, 3) . ' D', 0, 1, 'R');
        }

        if ($differentiel > 0) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 10, mb_convert_encoding("IND. DIFFERENTIELLE", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, number_format($differentiel, 3) . ' D', 0, 1, 'R');
        }
       
        if ($prime_anciennete_valeur > 0) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 10, mb_convert_encoding("Prime D'ANCIENNETE", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, number_format($prime_anciennete_valeur, 3) . ' D', 0, 1, 'R');
        }
       
        foreach ($rubriques_details as $rubrique) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 10, mb_convert_encoding($rubrique['nom'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, number_format($rubrique['valeur'], 3) . ' D', 0, 1, 'R');
        }
        if ($indemnite > 0) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(100, 10, mb_convert_encoding("Indemnité Forfaitaire", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, number_format($indemnite, 3) . ' D', 0, 1, 'R');
        }
        

        // Total brut
        $pdf->Ln(10);
        $pdf->Cell(100, 10, 'Total brut', 0, 0, 'L');
        $pdf->Cell(40, 10, number_format($total_brut, 3) . ' D', 0, 1, 'R');

        // Footer
        $pdf->Ln(10);
        $pdf->MultiCell(0, 10, "La presente attestation est delivree a l'interesse sur sa demande pour servir et valoire ce que de droit");

        // Signature
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'LE DIRECTEUR DE L\'USINE', 0, 1, 'R');
        $pdf->Cell(0, 10, 'Fethi El Abed', 0, 1, 'R');

        $pdf->Output();
    } else {
        echo "Aucun employé trouvé avec le matricule spécifié.";
    }
    
    $conn->close();
} else {
    echo "Matricule non spécifié.";
}
?>
