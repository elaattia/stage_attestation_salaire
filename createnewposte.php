<!DOCTYPE html>
                <html lang="fr">
                <head>
                <meta charset="UTF-8">
                <title>Créer un nouveau poste</title>
                <link rel="stylesheet" href="cssview.css">
                <script>
                    function calculatePrimeAnciennete() {
                        var salaireBase = parseFloat(document.getElementById('salaire_base').value);
                        var primeAnciennetePercent = parseFloat(document.getElementById('prime_anciennete').value);
                        var resultElement = document.getElementById('prime_anciennete_result');
                        if (!isNaN(salaireBase) && !isNaN(primeAnciennetePercent)) {
                            var result = salaireBase * (primeAnciennetePercent / 100);
                            resultElement.textContent = 'Prime d\'Ancienneté : ' + result.toFixed(2) + ' €';
                        } else {
                            resultElement.textContent = '';
                        }
                    }

                    function addRubrique() {
                        var container = document.getElementById('new_rubriques');
                        var count = container.children.length;
                        var newRubriqueHtml = `
                            <div class="field">
                                <label for="new_rubrique_name_${count}">Nom de la rubrique :</label>
                                <input type="text" id="new_rubrique_name_${count}" name="new_rubrique_name[]">
                                <label for="new_rubrique_value_${count}">Valeur de la rubrique :</label>
                                <input type="text" id="new_rubrique_value_${count}" name="new_rubrique_value[]">
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', newRubriqueHtml);
                    }

                    function displaySelectedRubriques() {
                        var select = document.getElementById('rubriques');
                        var selectedRubriquesContainer = document.getElementById('selected_rubriques');
                        selectedRubriquesContainer.innerHTML = '';

                        for (var option of select.options) {
                            if (option.selected) {
                                var rubriqueHtml = `
                                    <div class="field">
                                        <label>${option.text} :</label>
                                        <input type="text" name="rubrique_values[${option.value}]" placeholder="Valeur pour ${option.text}">
                                    </div>
                                `;
                                selectedRubriquesContainer.insertAdjacentHTML('beforeend', rubriqueHtml);
                            }
                        }
                    }
                </script>
                </head>
                <body>
                <h1>Créer un nouveau poste</h1>
                <form method="post" action="create_post.php">
                    <div class="field input">
                        <label for="type_poste">Type de poste :</label>
                        <select id="type_poste" name="type_poste" required>
                            <option value="1">Horaire</option>
                            <option value="2">Mensuel</option>
                        </select>
                    </div>

                    <div class="field input">
                        <label for="nom_poste">Nom du poste :</label>
                        <input type="text" id="nom_poste" name="nom_poste" required>
                    </div>

                    <div class="field">
                        <label for="salaire_base">Salaire de base :</label>
                        <input type="text" id="salaire_base" name="salaire_base" required value="<?php echo isset($_POST['salaire_base']) ? htmlspecialchars($_POST['salaire_base']) : ''; ?>">
                    </div>

                    <div class="field">
                        <label for="differentiel">Différentiel :</label>
                        <input type="text" id="differentiel" name="differentiel" required value="<?php echo isset($_POST['differentiel']) ? htmlspecialchars($_POST['differentiel']) : ''; ?>">
                    </div>

                    <div class="field">
                        <label for="prime_anciennete">Prime d'ancienneté :</label>
                        <select id="prime_anciennete" name="prime_anciennete" onchange="calculatePrimeAnciennete()">
                            <option value="0">0%</option>
                            <option value="3">3%</option>
                            <option value="6">6%</option>
                            <option value="9">9%</option>
                            <option value="12">12%</option>
                            <option value="15">15%</option>
                        </select>
                        <span id="prime_anciennete_result"></span>
                    </div>

                    <div class="field">
                        <h3>Augmentations Spécifiques</h3>
                        <label for="augment_salariale">Augmentations Salariales 1996/2016 :</label>
                        <input type="text" id="augment_salariale" name="augment_salariale" value="<?php echo isset($_POST['augment_salariale']) ? htmlspecialchars($_POST['augment_salariale']) : ''; ?>">
                        <br>
                        <label for="augment_16_18">Augmentations Spécifiques 16/18 :</label>
                        <input type="text" id="augment_16_18" name="augment_16_18" value="<?php echo isset($_POST['augment_16_18']) ? htmlspecialchars($_POST['augment_16_18']) : ''; ?>">
                        <br>
                        <label for="augment_2021">Augmentation 2021 :</label>
                        <input type="text" id="augment_2021" name="augment_2021" value="<?php echo isset($_POST['augment_2021']) ? htmlspecialchars($_POST['augment_2021']) : ''; ?>">
                        <br>
                        <label for="augment_2022">Augmentation 2022 :</label>
                        <input type="text" id="augment_2022" name="augment_2022" value="<?php echo isset($_POST['augment_2022']) ? htmlspecialchars($_POST['augment_2022']) : ''; ?>">
                    </div>

                    <div class="field">
                        <label for="rubriques">Autres rubriques :</label>
                        <select id="rubriques" name="rubriques[]" multiple onchange="displaySelectedRubriques()">
                            <?php
                            // Récupérer et afficher toutes les rubriques disponibles
                            $query_rubriques = "SELECT code, nom FROM rubrique WHERE nom NOT IN ('Prime de Présence', 'Augmentations Spécifiques 16/18', 'Augmentations Salariales 1996/2016', 'Augmentation 2021', 'Augmentation 2022')";
                            $result_rubriques = $conn->query($query_rubriques);
                            
                            while ($rubrique = $result_rubriques->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($rubrique['code']) . '">' . htmlspecialchars($rubrique['nom']) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="button" onclick="addRubrique()">Ajouter une nouvelle rubrique</button>
                        <div id="selected_rubriques"></div>
                    </div>

                    <div class="field">
                        <div id="new_rubriques"></div>
                    </div>

                    <div class="field">
                        <button class="btn" type="submit">Enregistrer le poste</button>
                    </div>
                </form>
                </body>
                </html>