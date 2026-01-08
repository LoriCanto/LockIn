<?php
require 'auth.php';
require 'config.php';
//sleep(3); per mostrare anim caricamento
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];

        if ($action == 'unlock') {
            $lockerID = $_POST['lockerID'];

            //IBERA L'ARMADIETTO
            $stmt = $pdo->prepare("UPDATE locker SET utente = null, data_prenotazione = null WHERE id = ?");
            $stmt->execute([$lockerID]);

            //CONTROLLA SE C'È QUALCUNO IN CODA (prendiamo il primo inserito)
            $stmtQueue = $pdo->query("SELECT utente FROM queue ORDER BY data_prenotazione ASC LIMIT 1");
            $nextUser = $stmtQueue->fetch();

            if ($nextUser) {
                $nextUserID = $nextUser['utente'];

                
                $stmtAssign = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ?");
                $stmtAssign->execute([$nextUserID, $lockerID]);

                
                $stmtDelete = $pdo->prepare("DELETE FROM queue WHERE utente = ?");
                $stmtDelete->execute([$nextUserID]);

            }

            header("Location: lockerRoom.php");
            exit();

        } elseif ($action == 'lock') {
            $lockerID = $_POST['lockerID'];
            $userID = $_POST['userID'];

            // CONTROLLO SE L'UTENTE HA GIÀ UN ARMADIETTO
            $stmt = $pdo->prepare("SELECT id FROM locker WHERE utente = ?");
            $stmt->execute([$userID]);
            if ($stmt->rowCount() > 0) {
                header("Location: myLocker.php");
                exit();
            }

            // TENTATIVO DI ASSEGNAZIONE
            $stmt = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ? AND utente IS NULL");
            $stmt->execute([$userID, $lockerID]);

            if ($stmt->rowCount() == 0) {
                $_SESSION['LockerBookingError'] = "Errore: L'armadietto non è più disponibile.";
                header("Location: choosingLocker.php");
                exit();
            } else {
                header("Location: lockerTakensuccess.html");
                exit();
            }
        }
    }
} catch (PDOException $e) {
    echo "Errore Database: " . $e->getMessage();
}