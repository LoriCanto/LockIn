<?php
require 'config.php';
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai 'name' del form HTML
        $action = $_POST['action'];
        if ($action == 'logOut') {
            session_start();
            $_SESSION = array(); // Svuota le variabili in memoria
            session_destroy();
            header("Location: home.html");
            exit();
        } elseif ($action == 'logIn') {
            $cf = $_POST['cf'];
            $password_inserita = $_POST['password'];

            try {
                // 2. Prepariamo la query per cercare l'utente tramite email
                // Supponiamo che la tua tabella si chiami 'utenti'
                $user = null;
                $stmt = $pdo->prepare("SELECT * FROM user WHERE cf = ?");
                $stmt->execute([$cf]);
                $user = $stmt->fetch();

                // 3. Verifichiamo se l'utente esiste e se la password è corretta
                if ($user != null && password_verify($password_inserita, $user['password'])) {
                    // Avviamo la sessione per ricordare che l'utente è loggato
                    session_start();
                    // Credenziali corrette! Salviamo i dati in sessione
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_code'] = $user['cf'];

                    // CONTROLLO POSSESSO ARMADIETTO O PRESENZA IN CODA
                    $prenotazione = $pdo->prepare("SELECT count(*) FROM (SELECT utente FROM locker UNION SELECT utente FROM queue) Presenza WHERE utente = ?");
                    $prenotazione->execute([$user['id']]);
                    $res = $prenotazione->fetchColumn();
                    if ($res > 0) {
                        // L'utente ha già un armadietto assegnato
                        header("Location: myLocker.php");
                        exit();
                    } else {
                        // INVIO ALLA PRENOTAZIONE
                        header("Location: lockerRoom.php");
                        exit();
                    }
                } else {
                    // Credenziali errate
                    // ANNULLO LA SESSIONE PER SICUREZZA
                    session_destroy();
                    echo "<script>alert('Codice Fiscale o Password errati!'); window.location.href='logIn.html';</script>";
                }
            } catch (PDOException $e) {
                $_SESSION['user_id'] = null;
                session_destroy();
                // Gestione errori del database
                echo "Errore durante il login: " . $e->getMessage();
            }
        } elseif ($action == 'signUp') {
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
    } else {
        echo "Azione non valida.";
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
