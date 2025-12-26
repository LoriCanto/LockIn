<?php
session_start();
require 'config.php';
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai 'name' del form HTML
        $action = $_POST['action'];
        if ($action == 'remove') {
            $userID = $_POST['userID'];
            $stmt = $pdo->prepare("DELETE FROM queue WHERE utente = ?");
            $stmt->execute([$userID]);
            echo "<script>alert('User removed from queue.');</script>";
            header("Location: lockerRoom.php");
        } elseif ($action == 'insert') {
            echo "Queue inserting.";
            $userID = $_POST['userID'];
            $stmt = $pdo->prepare("INSERT INTO queue (utente, tipo, data_prenotazione) VALUES (?, 'CODA', NOW());");
            $stmt->execute([$userID]);
            echo "<script>alert('User inserted in queue.');</script>";
            header("Location: myLocker.php");
        }
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
