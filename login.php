<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventaire_bd";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM administrateur WHERE username='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user;
    
            header("Location: login.html");
            exit(); // Assurez-vous de sortir après la redirection
        } else {
            echo "Mot de passe incorrect. <a href='index.html'>Réessayez</a>";
        }
    } else {
        echo "Utilisateur non trouvé. <a href='index.html'>Réessayez</a>";
    }
}    

$conn->close();
?>
