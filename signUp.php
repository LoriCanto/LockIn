<?php
// signUp.php
require 'config.php';
sleep(2);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupero dati dai 'name' del form HTML
    $username = $_POST['cf'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password2'];

    if ($password !== $password_confirm) {
        echo "<script>alert('Le password non coincidono!'); window.location.href='signUp.html';</script>";
        exit();
    }

    // Hash della Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user (cf, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);

        echo 
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta name="viewport" content="width=device-width,initial-scale=    1.0">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="LockInStyle.css">
            </head>
            <body id="schermataHome">
            <div id="schermata_registrazioneCompletata">
                <h1>Registrazione andata a buon fine!</h1>
                <button onclick="window.location.href='logIn.html'">Avanti e accedi</button>
            </div>
            </body>
            <script> 
            HTML;
        
    } catch (PDOException $e) {
        // Gestione errori (es. duplicati)
        if ($e->getCode() == 23000) { 
            echo "<script>alert('Errore: Codice Fiscale già in uso.');window.location.href='logIn.html';</script>";
        } else {
            echo "<script>alert('Si è verificato un errore durante la registrazione: " . $e->getMessage() . "');window.location.href='signUp.html';</script>";
        }
    }
}
?>