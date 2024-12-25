<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Tester le Script PHP</title>
    <script>
        function toggleIndemniteField() {
            var indemniteField = document.getElementById("indemnite-value");
            var hasIndemnite = document.getElementById("has-indemnite").value;
            indemniteField.style.display = (hasIndemnite === "oui") ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Tester le Calcul du Brut Théorique</header>
            <form action="view.php" method="post">
                <div class="field input">
                    <label for="matricule">Matricule :</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>
                <div class="field input">
                    <label for="ref">Référence :</label>
                    <input type="text" id="ref" name="ref">
                </div>
                <div class="field">
                    <label for="has-indemnite">L'employé a-t-il une INDEMNITE FORFAITAIRE ? :</label>
                    <select id="has-indemnite" name="has_indemnite" onchange="toggleIndemniteField()">
                        <option value="non">Non</option>
                        <option value="oui">Oui</option>
                    </select>
                </div>
                <div class="field" id="indemnite-value" style="display: none;">
                    <label for="indemnite">Valeur de l'INDEMNITE FORFAITAIRE :</label>
                    <input type="text" id="indemnite" name="indemnite">
                </div>
                <div class="field">
                    <input type="submit" class="btn" value="Tester">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
