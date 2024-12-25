<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Employé</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Ajouter Employé</h1>
    <form action="add_employee.php" method="post">
        <label for="matricule">Matricule:</label>
        <input type="text" id="matricule" name="matricule" required><br>
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom" required><br>
        <label for="prenom">Prénom:</label>
        <input type="text" id="prenom" name="prenom" required><br>
        <label for="date_naissance">Date de Naissance:</label>
        <input type="date" id="date_naissance" name="date_naissance" required><br>
        <label for="date_embauche">Date d'Embauche:</label>
        <input type="date" id="date_embauche" name="date_embauche" required><br>
        <label for="salaire_base">Salaire de Base:</label>
        <input type="number" id="salaire_base" name="salaire_base" required><br>
        <label for="matricule">Différentiel:</label>
        <input type="text" id="matricule" name="matricule" required><br>
        <label for="matricule">Heure Normal:</label>
        <input type="text" id="matricule" name="matricule" required><br>
        <label for="matricule">BRUT THEORIQUE STD:</label>
        <input type="text" id="matricule" name="matricule" required><br>
        <label for="matricule">Emploi occupé:</label>
        <input type="text" id="matricule" name="matricule" required><br>
        <label for="matricule">BRUT THEORIQUE STD JR:</label>
        <input type="text" id="matricule" name="matricule" required><br>

        
        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
