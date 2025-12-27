<?php
session_start();
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
            $stmt = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ? and utente is null");
            $stmt->execute([$userID, $lockerID]);
            header("Location: myLocker.php");
        }
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
