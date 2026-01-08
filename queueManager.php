<?php
session_start();
require 'config.php';
sleep(3);
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai name del form HTML
        $action = $_POST['action'];
        if ($action == 'remove') {
            $userID = $_POST['userID'];
            $stmt = $pdo->prepare("DELETE FROM queue WHERE utente = ?");
            $stmt->execute([$userID]);
            echo "<script>alert('User removed from queue.');</script>";
            header("Location: lockerRoom.php");
        } elseif ($action == 'insert') {
            $userID = $_POST['userID'];
            // CONTROLLO UTENTE GIA' IN CODA
            $stmt = $pdo->prepare("SELECT * from queue WHERE utente = ?");
            $stmt->execute([$userID]);
            if ($stmt->rowCount() > 0) {
                header("Location: myLocker.php");
            }
            // INSERIMENTO IN CODA
            else {
                echo "Queue inserting.";
                $stmt = $pdo->prepare("INSERT INTO queue (utente, tipo, data_prenotazione) VALUES (?, 'CODA', NOW());");
                $stmt->execute([$userID]);
                echo "<script>alert('User inserted in queue.');</script>";
                header("Location: lockerTakensuccess.html");
            }
        }
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
