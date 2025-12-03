<?php
// registrazione.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['cf'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Validazione e Hash della Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // 3. Inserimento Utente (non verificato)
        $stmt = $pdo->prepare("INSERT INTO user (cf, email, password ) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);

        echo "Registrazione avvenuta con successo!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Codice errore per duplicato (es. username o email)
            echo "Errore: Username o Email già in uso.";
        } else {
            echo "Si è verificato un errore durante la registrazione: " . $e->getMessage();
        }
    }
}
?>