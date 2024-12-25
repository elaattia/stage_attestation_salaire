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
        echo "<h1>Employé non trouvé</h1>";
        echo "<a href='add.php'><button>Ajouter Employé</button></a><br>";
    }
    ?>
</body>
</html>