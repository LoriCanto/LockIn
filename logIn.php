<?php
// Include la configurazione del database che mi hai passato
require 'config.php'; 
sleep(2);

// Avviamo la sessione per ricordare che l'utente è loggato
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recuperiamo i dati dal form (assicurati che i "name" nel form HTML siano corretti)
    $cf = $_POST['cf'];
    $password_inserita = $_POST['password'];

    try {
        // 2. Prepariamo la query per cercare l'utente tramite email
        // Supponiamo che la tua tabella si chiami 'utenti'
        $stmt = $pdo->prepare("SELECT * FROM user WHERE cf = ?");
        $stmt->execute([$cf]);
        $user = $stmt->fetch();

        // 3. Verifichiamo se l'utente esiste e se la password è corretta
        if ($user && password_verify($password_inserita, $user['password'])) {
            
            // Credenziali corrette! Salviamo i dati in sessione
            $_SESSION['user_id'] = $user['id'];

            // Reindirizziamo alla pagina principale (es. la griglia degli armadietti)
            header("Location: lockerRoom.php"); 
            exit();

        } else {
            // Credenziali errate
            echo "<script>alert('Codice Fiscale o Password errati!'); window.location.href='logIn.html';</script>";
        }

    } catch (PDOException $e) {
        // Gestione errori del database
        echo "Errore durante il login: " . $e->getMessage();
    }
}
?>