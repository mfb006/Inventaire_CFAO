<?php
// PHP: Connection et suppression de ligne si demandé
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventaire_bd";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if (isset($_POST['delete'])) {
    $nom = $_POST['nom'];
    $sql = "DELETE FROM ordinateurs WHERE nom='$nom'";
    $conn->query($sql);
}

// Mettre à jour les enregistrements
if (isset($_POST['edit'])) {
    $originalNom = $_POST['original_nom'];
    $nomPart = $_POST['nom_part']; // Partie restante du nom
    $nom = 'SN3644' . $nomPart; // Ajouter le préfixe SN3644
    $localisation = $_POST['localisation'];
    $utilisateur = $_POST['utilisateur'];
    $statut = $_POST['statut'];

    $sql = "UPDATE ordinateurs SET nom='$nom', localisation='$localisation', utilisateur='$utilisateur', statut='$statut' WHERE nom='$originalNom'";
    $conn->query($sql);
}

// Déterminer le nombre total d'enregistrements
$sql = "SELECT COUNT(*) as total FROM ordinateurs";
$result = $conn->query($sql);
$recordCount = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $recordCount = $row['total'];
}

// Pagination
$recordsPerPage = 10;
$totalPages = ceil($recordCount / $recordsPerPage);

if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int)$_GET['page'];
} else {
    $currentPage = 1;
}

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

if ($currentPage < 1) {
    $currentPage = 1;
}

$startFrom = ($currentPage - 1) * $recordsPerPage;

// Modifier la requête pour trier les enregistrements par ordre décroissant de l'ID ou de la date d'ajout
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = $search ? "WHERE nom LIKE '%$search%'" : '';

// Construire la condition WHERE en fonction des filtres
$filterLocalisation = isset($_GET['filter_localisation']) ? $_GET['filter_localisation'] : '';
$filterStatut = isset($_GET['filter_statut']) ? $_GET['filter_statut'] : '';

$whereConditions = [];
if ($filterLocalisation !== '') {
    if ($filterLocalisation === 'Tous') {
        // Laisser vide pour inclure tous les enregistrements
    } else {
        $whereConditions[] = "localisation = '$filterLocalisation'";
    }
}
if ($filterStatut !== '') {
    if ($filterStatut === 'Tous') {
        // Laisser vide pour inclure tous les enregistrements
    } else {
        $whereConditions[] = "statut = '$filterStatut'";
    }
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Modifier la requête principale pour appliquer les filtres
$sql = "SELECT nom, localisation, utilisateur, statut FROM ordinateurs $searchCondition $whereClause ORDER BY id DESC LIMIT $startFrom, $recordsPerPage";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaire</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background: linear-gradient(120deg, #1e3c72, #2a5298); /* Bleu nuit dynamique */
}

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 10px;
            background-color: #FFA500;
            color: #fff;
        }

        .logo {
            max-width: 130px;
            height: auto;
        }

        .close {
            color: #fff;
            font-size: 24px;
            text-decoration: none;
        }

        .search-bar {
            background-color: #f2f2f2;
            padding: 10px 20px;
        }

        .search-bar form {
            display: flex;
            align-items: center;
        }

        .search-bar input[type="text"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        .search-bar button {
            padding: 8px 12px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        .filter {
            margin-bottom: 20px;
        }

        .filter label {
            margin-right: 10px;
        }

        .filter select {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff; /* Fond blanc pour le tableau */
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

table th {
    background-color: #f2f2f2;
}

/* Style pour la survol de ligne */
table tbody tr:hover {
    background-color: #FFA500;
}


        .pagination {
            margin-top: 20px;
            overflow: hidden;
        }

        .pagination a {
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }

        #editFormContainer {
            background-color: #f9f9f9;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #ddd;
            position: fixed;
            bottom: 150px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            z-index: 999;
        }

        #editFormContainer label {
            margin-bottom: 10px;
            display: block;
        }

        #editFormContainer input[type="text"], #editFormContainer select {
            padding: 8px;
            font-size: 16px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        #editFormContainer button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #editFormContainer button:hover {
            background-color: #45a049;
        }

        .edit-btn {
            background-color: #1db662;
        }

        .delete-btn {
            background-color: #b61d1d;
        }

        .next, .prev {
            background-color: white;
        }

        .FPL, .FPS {
            color : white;
        }

        .record-count {
            color: white;
            justify-content: center;
            display: flex;
            font-size: 21px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="cfao logo2.png" alt="Logo CFAO" class="logo">
            <h1>Inventaire des Ordinateurs</h1>
            <a href="login.html" class="close">&times;</a>
        </div>
    </header>
  
    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Rechercher une machine..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Rechercher</button>
        </form>
    </div>
    <p class="record-count">Le nombre d'enregistrements total dans la base de données est : <?php echo $recordCount; ?></p>
    <div class="container">
        <div class="filter">
            <form method="GET" action="">
                <label for="localisation" class = "FPL">Filtrer par localisation :</label>
                <select id="localisation" name="filter_localisation" onchange="this.form.submit()">
                    <option value="Tous" <?php echo ($filterLocalisation === '' || $filterLocalisation === 'Tous') ? 'selected' : ''; ?>>Tous</option>
                    <option value="ACHAT" <?php echo ($filterLocalisation === 'ACHAT') ? 'selected' : ''; ?>>ACHAT</option>
                    <option value="COMPTABILITE" <?php echo ($filterLocalisation === 'COMPTABILITE') ? 'selected' : ''; ?>>COMPTABILITE</option>
                    <option value="CONTROLE FINANCE" <?php echo ($filterLocalisation === 'DIRECTION') ? 'selected' : ''; ?>>DIRECTION</option>
                    <option value="EXPANSION" <?php echo ($filterLocalisation === 'EXPANSION') ? 'selected' : ''; ?>>EXPANSION</option>
                    <option value="FINANCE" <?php echo ($filterLocalisation === 'FINANCE') ? 'selected' : ''; ?>>FINANCE</option>
                    <option value="RH" <?php echo ($filterLocalisation === 'RH') ? 'selected' : ''; ?>>RH</option>
                    <option value="INFORMATIQUE" <?php echo ($filterLocalisation === 'INFORMATIQUE') ? 'selected' : ''; ?>>INFORMATIQUE</option>
                    <option value="JURISTE" <?php echo ($filterLocalisation === 'JURISTE') ? 'selected' : ''; ?>>JURISTE</option>
                    <option value="MAINTENANCE" <?php echo ($filterLocalisation === 'MAINTENANCE') ? 'selected' : ''; ?>>MAINTENANCE</option>
                    <option value="MARKETING&COM" <?php echo ($filterLocalisation === 'MARKETING&COM') ? 'selected' : ''; ?>>MARKETING&COM</option>
                    <option value="REFERENTIEL" <?php echo ($filterLocalisation === 'REFERENTIEL') ? 'selected' : ''; ?>>REFERENTIEL</option>
                    <option value="SECURITE&QUALITE" <?php echo ($filterLocalisation === 'SECURITE&QUALITE') ? 'selected' : ''; ?>>SECURITE&QUALITE</option>
                    <option value="SUPPLY" <?php echo ($filterLocalisation === 'SUPPLY') ? 'selected' : ''; ?>>SUPPLY</option>
                    <option value="TRANSIT" <?php echo ($filterLocalisation === 'TRANSIT') ? 'selected' : ''; ?>>TRANSIT</option>
                    <option value="TRESORERIE" <?php echo ($filterLocalisation === 'TRESORERIE') ? 'selected' : ''; ?>>TRESORERIE</option>
                </select>
                
                <label for="statut" class = "FPS">Filtrer par statut :</label>
                <select id="statut" name="filter_statut" onchange="this.form.submit()">
                    <option value="Tous" <?php echo ($filterStatut === '' || $filterStatut === 'Tous') ? 'selected' : ''; ?>>Tous</option>
                    <option value="fonctionne" <?php echo ($filterStatut === 'fonctionne') ? 'selected' : ''; ?>>Fonctionne</option>
                    <option value="ne fonctionne pas" <?php echo ($filterStatut === 'ne fonctionne pas') ? 'selected' : ''; ?>>Ne fonctionne pas</option>
                </select>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Localisation</th>
                    <th>Utilisateur</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["nom"] . "</td>";
                        echo "<td>" . $row["localisation"] . "</td>";
                        echo "<td>" . $row["utilisateur"] . "</td>";
                        echo "<td>" . $row["statut"] . "</td>";
                        echo "<td>
                                <button class='edit-btn' onclick=\"editRow(this)\">&#9998;</button>
                                <button class='delete-btn' onclick=\"deleteRow('". $row['nom'] ."')\">X</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Aucun résultat</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- Pagination controls -->
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter_localisation=<?php echo htmlspecialchars($filterLocalisation); ?>&filter_statut=<?php echo htmlspecialchars($filterStatut); ?>" class="prev">Précédent</a>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter_localisation=<?php echo htmlspecialchars($filterLocalisation); ?>&filter_statut=<?php echo htmlspecialchars($filterStatut); ?>" class="next">Suivant</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulaire d'édition -->
    <div id="editFormContainer">
        <form id="editForm" method="POST" action="">
            <input type="hidden" name="original_nom" id="originalNom">
            <label for="nom_part">Nom de l'ordinateur (partie après SN3644) :</label>
            <input type="text" name="nom_part" id="editNomPart" required>

            <label for="localisation">Localisation :</label>
            <select id="editLocalisation" name="localisation" required>
                <option value="ACHAT">ACHAT</option>
                <option value="COMPTABILITE">COMPTABILITE</option>
                <option value="DIRECTION">DIRECTION</option>
                <option value="EXPANSION">EXPANSION</option>
                <option value="FINANCE">FINANCE</option>
                <option value="RH">RH</option>
                <option value="INFORMATIQUE">INFORMATIQUE</option>
                <option value="JURISTE">JURISTE</option>
                <option value="MAINTENANCE">MAINTENANCE</option>
                <option value="MARKETING&COM">MARKETING&COM</option>
                <option value="REFERENTIEL">REFERENTIEL</option>
                <option value="SECURITE&QUALITE">SECURITE&QUALITE</option>
                <option value="SUPPLY">SUPPLY</option>
                <option value="TRANSIT">TRANSIT</option>
                <option value="TRESORERIE">TRESORERIE</option>
            </select>

            <label for="utilisateur">Nom de l'utilisateur:</label>
            <input type="text" name="utilisateur" id="editUtilisateur" required>
            
            <label for="statut">Statut :</label>
            <select id="editStatut" name="statut" required>
                <option value="fonctionne">Fonctionne</option>
                <option value="ne fonctionne pas">Ne fonctionne pas</option>
            </select>

            <button type="submit" name="edit">Enregistrer les modifications</button>
        </form>
    </div>
    
    <script>
        function editRow(button) {
            // Obtenez la ligne à partir du bouton
            var row = button.parentNode.parentNode;
            
            // Obtenez les valeurs de la ligne
            var nom = row.cells[0].innerText;
            var localisation = row.cells[1].innerText;
            var utilisateur = row.cells[2].innerText;
            var statut = row.cells[3].innerText;
            
            // Remplissez le formulaire avec les valeurs de la ligne
            document.getElementById('originalNom').value = nom;
            document.getElementById('editNomPart').value = nom.replace('SN3644', '');
            document.getElementById('editLocalisation').value = localisation;
            document.getElementById('editUtilisateur').value = utilisateur;
            document.getElementById('editStatut').value = statut;
            
            // Affichez le formulaire
            document.getElementById('editFormContainer').style.display = 'block';
            
            // Faites défiler jusqu'au formulaire d'édition
            window.scrollTo(0, document.body.scrollHeight);
        }

        function deleteRow(nom) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet enregistrement ?")) {
                // Créez un formulaire et soumettez-le pour supprimer la ligne
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "";
                
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "nom";
                input.value = nom;
                
                var deleteInput = document.createElement("input");
                deleteInput.type = "hidden";
                deleteInput.name = "delete";
                deleteInput.value = "true";
                
                form.appendChild(input);
                form.appendChild(deleteInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

