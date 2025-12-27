<?php
require 'config.php';
session_start();

// Prendo i gruppi dal database per generare le diverse sezioni
$stmtGruppi = $pdo->query("SELECT DISTINCT gruppo FROM locker ORDER BY gruppo");
$gruppi = $stmtGruppi->fetchAll(PDO::FETCH_COLUMN);

// Prendo i tipi dal database per generare le diverse sezioni
$stmtTipi = $pdo->query("SELECT DISTINCT gruppo FROM locker ORDER BY tipo");
$tipi = $stmtTipi->fetchAll(PDO::FETCH_COLUMN);

// Prendo le posizioni dal database per generare le diverse sezioni
$stmtPos = $pdo->query("SELECT DISTINCT posizione FROM locker ORDER BY posizione");
$pos = $stmtPos->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>LockIn - Prenotazione</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="choosingLocker.css">
    <link rel="stylesheet" href="LockInStyle.css">
</head>

<body>
    <div id="main-layout">
        <aside id="menu">
            <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?></h5>
            <h3>Piani</h3>
            <div class="menu-option">
                <label for="scelta">Scegli un'opzione:</label>
                <?php
                echo "<select name=\"piano\" id=\"pianoLocker\">";
                foreach ($pos as $posizione)
                    echo "<option value=\"$posizione\">$posizione</option>";
                echo "</select>";

                ?>
            </div>
        </aside>
        <main id="locker-content">
            <h1>Scegli il tuo armadietto</h1>
            <?php foreach ($gruppi as $nomeGruppo): ?>
                <div class="gruppo-container">
                    <h2>Gruppo <?php echo $nomeGruppo; ?></h2>
                    <div class="grid-5x5">
                        <?php
                        // Query per estrarre i locker di questo specifico gruppo
                        $stmtLocker = $pdo->prepare("SELECT id, codice, utente FROM locker WHERE gruppo = ? LIMIT 25");
                        $stmtLocker->execute([$nomeGruppo]);
                        $lockers = $stmtLocker->fetchAll();
                        foreach ($lockers as $locker):
                            // Controllo se l'armadietto è libero o occupato (se 'utente' è pieno, allora è occupato)
                            $isOccupato = !empty($locker['utente']);
                            $classeStato = $isOccupato ? 'occupato' : 'libero';
                        ?>
                            <?php if (!$isOccupato): ?>
                                <form action="lockerManager.php" method="post">
                                    <input type="hidden" name="lockerID" value="<?php echo $locker['id']; ?>">
                                    <input type="hidden" name="action" value="lock">
                                    <input type="hidden" name="userID" value="<?php echo $_SESSION['user_id']; ?>">
                                    <div class="locker-box libero" onclick="this.parentNode.submit();">
                                        <img src="images/armadiettoGrandeSingolo.png" alt="Armadietto" class="locker-img">
                                        <span class="locker-code"><?php echo $locker['codice']; ?></span>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="locker-box occupato">
                                    <img src="images/grande.png" alt="Armadietto" class="locker-img">
                                    <span class="locker-code"><?php echo $locker['codice']; ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
</body>

</html>