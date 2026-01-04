<?php
require 'auth.php';
require 'config.php';
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai 'name' del form HTML
        $action = $_POST['action'];

        if ($action == 'unlock') {
            $lockerID = $_POST['lockerID'];
            $stmt = $pdo->prepare("UPDATE locker SET utente = null, data_prenotazione = null WHERE id = ? and utente is not null");
            $stmt->execute([$lockerID]);
            echo "Locker $lockerID unlocked.";
            header("Location: lockerRoom.php");
        } elseif ($action == 'lock') {
            $lockerID = $_POST['lockerID'];
            $userID = $_POST['userID'];
            // CONTROLLO UTENTE SENZA ARMADIETTO ASSEGNATO
            $stmt = $pdo->prepare("SELECT * from Locker WHERE utente = ?");
            $stmt->execute([$userID]);
            if ($stmt->rowCount() > 0) {
                header("Location: myLocker.php");
            }
            // CONTROLLO ARMADIETTO LIBERO E ASSEGNAZIONE  
            $stmt = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ? and utente is null");
            $stmt->execute([$userID, $lockerID]);
            if ($stmt->rowCount() == 0) {
                $_SESSION['LockerBookingError'] = "Errore: L'armadietto non è più disponibile.";
                header("Location: choosingLocker.php");
            } else {
                header("Location: lockerTakensuccess.html");
            }
        }
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
