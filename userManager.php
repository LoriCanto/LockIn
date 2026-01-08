<?php
require 'config.php';
//sleep(3); per mostrare anim caricamento
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];

        if ($action == 'logOut') {
            session_start();
            $_SESSION = array(); 
            session_destroy();
            header("Location: index.html");
            exit();

        } elseif ($action == 'logIn') {
            $cf = $_POST['cf'];
            $password_inserita = $_POST['password'];

            //CONDIZIONE ADMIN
            if ($cf === 'AADMIN00A00D000M' && $password_inserita === 'Locker_admin') {
                session_start();
                $_SESSION['user_id'] = 0; 
                $_SESSION['user_code'] = 'ADMIN';
                $_SESSION['role'] = 'admin';
                
                
                header("Location: admin.html"); 
                exit();
            }
            

            try {
                // Procedura normale per gli utenti studenti
                $stmt = $pdo->prepare("SELECT * FROM user WHERE cf = ?");
                $stmt->execute([$cf]);
                $user = $stmt->fetch();

                if ($user != null && password_verify($password_inserita, $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_code'] = $user['cf'];

                    // CONTROLLO POSSESSO ARMADIETTO O PRESENZA IN CODA
                    $prenotazione = $pdo->prepare("SELECT count(*) FROM (SELECT utente FROM locker UNION SELECT utente FROM queue) Presenza WHERE utente = ?");
                    $prenotazione->execute([$user['id']]);
                    $res = $prenotazione->fetchColumn();
                    
                    if ($res > 0) {
                        header("Location: myLocker.php");
                        exit();
                    } else {
                        header("Location: lockerRoom.php");
                        exit();
                    }
                } else {
                    // Credenziali errate
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    session_destroy();
                    echo "<script>alert('Codice Fiscale o Password errati!'); window.location.href='logIn.html';</script>";
                }
            } catch (PDOException $e) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                session_destroy();
                echo "Errore durante il login: " . $e->getMessage();
            }

        } elseif ($action == 'signUp') {
           
            $username = $_POST['cf'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $password_confirm = $_POST['password2'];

            if ($password !== $password_confirm) {
                echo "<script>alert('Le password non coincidono!'); window.location.href='signUp.html';</script>";
                exit();
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO user (cf, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash]);
                header("Location: signUpSuccess.html");
            } catch (PDOException $e) {
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
    echo "Errore durante l'operazione: " . $e->getMessage();
}