<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventaire_bd";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $localisation = $_POST["localisation"];
    $utilisateur = $_POST["utilisateur"];
    $statut = $_POST["statut"];

    $sql = "INSERT INTO ORDINATEURS (nom, localisation, utilisateur, statut)
            VALUES ('$nom', '$localisation', '$utilisateur', '$statut')";

    if ($conn->query($sql) === TRUE) {
        echo "Nouvel enregistrement créé avec succès";
        echo "<br><a href='login.html'>Retour à l'accueil</a>";
    } else {
        echo "Erreur : " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
