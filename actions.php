<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //echo '<pre>';
    //print_r($_POST);
    //echo '</pre>';

    if (isset($_POST['matricule'])) {
        $matricule = $_POST['matricule'];

        // Prepare and bind
        $stmt = $conn->prepare("SELECT * FROM employe WHERE Matricule = ?");
        $stmt->bind_param("s", $matricule);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Résultat de la Recherche</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <?php
            if ($result->num_rows > 0) {
                echo "<h1>Employé trouvé</h1>";
                echo "<a href='view.php?matricule=$matricule'><button>Voir Attestation</button></a><br>";
                echo "<a href='modify.php?matricule=$matricule'><button>Modifier</button></a><br>";
                echo "<a href='delete.php?matricule=$matricule'><button>Supprimer</button></a><br>";
                echo "<a href='history.php?matricule=$matricule'><button>Voir Historique</button></a><br>";
            } else {
                echo "<h1>matricule introuver reseller encore</h1>";
                echo "<a href='attestation_travail.php'><button>reeselle</button></a><br>";
                
            }
            ?>
        </body>
        </html>
        <?php
        // Close the statement and the connection
        $stmt->close();
        $conn->close();
    } else {
        echo "<h1>Matricule non défini dans POST</h1>";
    }
} else {
    echo "<h1>Formulaire non soumis</h1>";
}






/*try {
    $pdo = new PDO('mysql:host=localhost;dbname=testdb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'PDO MySQL fonctionne !';
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
// Vérifier si PDO et PDO MySQL sont activés
if (!extension_loaded('pdo')) {
    die('PDO n\'est pas activé');
}
if (!extension_loaded('pdo_mysql')) {
    die('PDO MySQL n\'est pas activé');
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

try {
    $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection réussie!<br>";

    // Récupérer le matricule depuis le formulaire
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $matricule = $_POST['matricule'];

        // Préparer et exécuter la requête SQL
        $stmt = $connection->prepare("SELECT * FROM employe WHERE Matricule = :matricule");
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();

        // Vérifier si des résultats ont été trouvés
        if ($stmt->rowCount() > 0) {
            // Afficher les informations de l'employé
            $employe = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Matricule: " . htmlspecialchars($employe['Matricule']) . "<br>";
            echo "Nom: " . htmlspecialchars($employe['Nom']) . "<br>";
            echo "Prenom: " . htmlspecialchars($employe['Prenom']) . "<br>";
            echo "Date de Naissance: " . htmlspecialchars($employe['Date_Naissance']) . "<br>";
            echo "Date d'Embauche: " . htmlspecialchars($employe['Date_Embauche']) . "<br>";
            echo "Salaire de Base: " . htmlspecialchars($employe['Salaire_Base']) . "<br>";
            echo "Différentiel: " . htmlspecialchars($employe['Differentiel']) . "<br>";
            echo "Heure Normale: " . htmlspecialchars($employe['Heure_Normal']) . "<br>";
            echo "Brut Théorique STD: " . htmlspecialchars($employe['Brut_Theorique_STD']) . "<br>";
            echo "Emploi Occupé: " . htmlspecialchars($employe['Emploi_Occupe']) . "<br>";
            echo "Brut Théorique STD JR: " . htmlspecialchars($employe['Brut_Theorique_STD_JR']) . "<br>";
        } else {
            echo "Aucun employé trouvé avec le matricule " . htmlspecialchars($matricule);
        }
    } else {
        echo "Aucune donnée reçue.";
    }
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
}


/*$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";

try {
  $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connection réussie!";  // Use UTF-8 characters here
} catch (PDOException $e) {
  echo "Échec de la connexion : " . $e->getMessage();  // Use UTF-8 characters here
}
*/



/*$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attestation_salaire";


try{
    $connection=new PDO("mysql:host-$servername;$dbname",$username,$password);
    $connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    echo"connection reussite";
}
catch(PDOException $e){
    echo "echec de la connection".$e->getMessage();
}


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$matricule = $_POST['matricule'];

// Prepare and bind
$stmt = $conn->prepare("SELECT * FROM employe WHERE Matricule = ?");
$stmt->bind_param("s", $matricule);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h1>Employé trouvé</h1>";
    echo "<a href='view.php?matricule=$matricule'>Voir Attestation</a><br>";
    echo "<a href='modify.php?matricule=$matricule'>Modifier</a><br>";
    echo "<a href='delete.php?matricule=$matricule'>Supprimer</a><br>";
    echo "<a href='history.php?matricule=$matricule'>Voir Historique</a><br>";
} else {
    echo "<h1>Employé non trouvé</h1>";
    echo "<a href='add.php'>Ajouter Employé</a><br>";
}

// Close the statement and the connection
$stmt->close();
$conn->close();
*/



?>
