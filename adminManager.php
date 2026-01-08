<?php
session_start();

// Controllo se l'utente è loggato e se ha il ruolo di admin
if ($_SESSION['role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    header("Location: logIn.html");
    exit();
}

require 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai 'name' del form HTML
        $action = $_POST['action'];

        if ($action == 'get') {
            $stmt = $pdo->prepare("SELECT locker.id, codice, posizione, gruppo, tipo, cf as utente, anno FROM locker LEFT JOIN user ON locker.utente = user.id ORDER BY gruppo;");
            $stmt->execute();

            // Recuperiamo tutti i risultati
            $lockers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($lockers) {
                echo "<h2>Stato dei Locker</h2>";
                ?> <button onclick="location.href='admin.html'">Torna alle azioni admin</button><?php
                echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background-color: #f2f2f2;'>
                <th>Codice</th>
                <th>Posizione</th>
                <th>Gruppo</th>
                <th>Tipo</th>
                <th>Utente</th>
                <th>Anno</th>
                <th>Azioni</th>
              </tr>";

                foreach ($lockers as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['codice']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['posizione']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['gruppo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
                    echo "<td>" . ($row['utente'] ? htmlspecialchars($row['utente']) : '<i>Libero</i>') . "</td>";
                    echo "<td>" . htmlspecialchars($row['anno']) . "</td>"; ?>
                    <td>
                        <div>
                            <form action="lockerManager.php" method="POST">
                                <input type="hidden" name="lockerID" value='<?php echo htmlspecialchars($row['id']); ?>'>
                                <button name=" lockerRelease" type="submit">UNLOCK </button>
                                <input type="hidden" name="action" value="unlock">
                            </form>
                            <form action="lockerManager.php" method="POST">
                                <input type="hidden" name="lockerID" value='<?php echo htmlspecialchars($row['id']); ?>'>
                                <input type="text" name="userID" placeholder="userID" required>
                                <button name="lockerLock" type="submit">LOCK </button>
                                <input type="hidden" name="action" value="lock">
                            </form>
                            <form action="adminManager.php" method="POST">
                                <input type="hidden" name="lockerID" value='<?php echo htmlspecialchars($row['id']); ?>'>
                                <button name="lockerRemove" type="submit">REMOVE </button>
                                <input type="hidden" name="action" value="remove">
                            </form>
                        </div>
                    </td>
            <?php
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Nessun locker trovato nel database.";
                ?> <button onclick="location.href='admin.html'">Torna alle azioni admin</button><?php
            }
        } elseif ($action == 'unlock') {
            $lockerID = $_POST['lockerID'];
            $stmt = $pdo->prepare("UPDATE locker SET utente = null, data_prenotazione = null WHERE id = ?");
            $stmt->execute([$lockerID]);
            echo "Locker $lockerID unlocked.";
        } elseif ($action == 'lock') {
            $lockerID = $_POST['lockerID'];
            $userID = $_POST['userID'];
            $stmt = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ?");
            $stmt->execute([$userID, $lockerID]);
            echo "Locker $lockerID locked by user $userID.";
        } elseif ($action == 'remove') {
            $lockerID = $_POST['lockerID'];
            $stmt = $pdo->prepare("DELETE FROM locker WHERE id = ?");
            $stmt->execute([$lockerID]);
            echo "Locker $lockerID removed.";
            ?>
            <form action="adminManager.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="action" value="get">
                <button type="submit">Aggiorna e torna alla lista</button>
            </form>
<?php
         } elseif ($action == 'insert') {
            $lockerCount = (int)$_POST['lockerCount'];
            $lockerGroup = $_POST['lockerGroup'];
            $lockerPosition = $_POST['lockerPosition'];
            $lockerType = $_POST['lockerType'];
            $lockerYear = $_POST['lockerYear'];

            // --- INIZIO PARTE MODIFICATA ---
            // Conto quanti armadietti esistono già per questo gruppo
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM locker WHERE gruppo = ?");
            $stmtCheck->execute([$lockerGroup]);
            $existingCount = (int)$stmtCheck->fetchColumn();

            // Controllo se il totale (esistenti + nuovi) supera 25
            if (($existingCount + $lockerCount) > 25) {
                echo "<h2>Errore di inserimento</h2>";
                echo "Hai inserito troppi armadietti a blocco.<br>";
                echo "Il gruppo <strong>$lockerGroup</strong> ha già $existingCount armadietti. ";
                echo "Non puoi aggiungerne altri $lockerCount perché supereresti il limite di 25.";
                ?>
                <br><br>
                <button onclick="location.href='admin.html'">Torna indietro</button>
                <?php
            } else {
                // Se il controllo passa, procedo con l'inserimento
                echo "Lockers inserting...";

                // Trovo l'ultimo numero utilizzato per questo gruppo
                $stmtMax = $pdo->prepare("SELECT codice FROM locker WHERE gruppo = ? ORDER BY id DESC LIMIT 1");
                $stmtMax->execute([$lockerGroup]);
                $lastLocker = $stmtMax->fetch();

                $startNumber = 0;
                if ($lastLocker) {
                    $startNumber = (int)str_replace($lockerGroup, '', $lastLocker['codice']);
                }

                for ($i = 1; $i <= $lockerCount; $i++) {
                    $currentNumber = $startNumber + $i;
                    $codiceFinal = $lockerGroup . $currentNumber;

                    $stmt = $pdo->prepare("INSERT INTO locker (anno, codice, gruppo, tipo, posizione) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$lockerYear, $codiceFinal, $lockerGroup, $lockerType, $lockerPosition]);
                    
                    $newLockerID = $pdo->lastInsertId();

                    $stmtQueue = $pdo->query("SELECT utente FROM queue ORDER BY data_prenotazione ASC LIMIT 1");
                    $nextUser = $stmtQueue->fetch();

                    if ($nextUser) {
                        $nextUserID = $nextUser['utente'];
                        $stmtAssign = $pdo->prepare("UPDATE locker SET utente = ?, data_prenotazione = NOW() WHERE id = ?");
                        $stmtAssign->execute([$nextUserID, $newLockerID]);

                        $stmtDelete = $pdo->prepare("DELETE FROM queue WHERE utente = ?");
                        $stmtDelete->execute([$nextUserID]);
                    }
                }
                echo "<br>Inseriti $lockerCount armadietti del gruppo $lockerGroup (partendo da " . ($startNumber + 1) . ").";
                ?> <br><button onclick="location.href='admin.html'">Torna alle azioni admin</button> <?php
            }
            // --- FINE PARTE MODIFICATA ---
        }
    }
} catch (PDOException $e) {
    echo "Errore durante l'operazione: " . $e->getMessage();
}
?>