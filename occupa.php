<?php
// Inizio la sessione per recuperare l'ID dell'utente loggato
session_start();
require 'config.php';

// Controllo se l'utente è effettivamente loggato
// Se la sessione è vuota, lo rimando alla pagina di login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Verifico che i dati siano stati inviati tramite il form (metodo POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_armadietto'])) {

    $id_locker = $_POST['id_armadietto'];
    $id_utente = $_SESSION['user_id'];

    try {
        /* Query di UPDATE: 
           Assegno l'id dell'utente al locker selezionato.
           Aggiungo "AND utente IS NULL" per sicurezza: così se due persone 
           cliccano insieme, solo la prima riesce a scrivere nel DB.
        */
        $sql = "UPDATE locker SET utente = :id_utente WHERE id = :id_locker AND utente IS NULL";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_utente' => $id_utente,
            ':id_locker' => $id_locker
        ]);

        // Verifico se la query ha effettivamente modificato una riga
        if ($stmt->rowCount() > 0) {
            // Se rowCount > 0, l'armadietto è stato assegnato correttamente
            echo "<script>
                    alert('Prenotazione confermata!'); 
                    window.location.href='myLocker.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Spiacenti, l\'armadietto è stato appena occupato da un altro utente.'); 
                    window.location.href='chooseBigLocker.php';
                  </script>";
        }
    } catch (PDOException $e) {
        die("Errore nel database: " . $e->getMessage());
    }
} else {
    // Se qualcuno prova a chiamare la pagina senza passare dal form, lo rispedisco indietro
    header("Location: myLocker.php");
    exit();
}
