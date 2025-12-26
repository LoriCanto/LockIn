<?php
session_start();
echo "Login effettuato con successo!";
echo "<br>Benvenuto, " . htmlspecialchars($_SESSION['user_id']) . "!";

require 'config.php';
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recupero dati dai 'name' del form HTML
        $action = $_POST['action'];

        if ($action == 'get') {
            echo "Locker got.";
            $stmt = $pdo->prepare("SELECT locker.id, codice, gruppo, tipo, email as utente, anno FROM locker  LEFT JOIN user ON locker.utente = user.id ORDER BY gruppo;");
            $stmt->execute();

            // Recuperiamo tutti i risultati
            $lockers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($lockers) {
                echo "<h2>Stato dei Locker</h2>";
                echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background-color: #f2f2f2;'>
                <th>Codice</th>
                <th>Gruppo</th>
                <th>Tipo</th>
                <th>Utente</th>
                <th>Anno</th>
                <th>Azoni</th>
              </tr>";

                foreach ($lockers as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['codice']) . "</td>";
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
                            <form action="lockerManager.php" method="POST">
                                <input type="hidden" name="lockerID" value='<?php echo htmlspecialchars($row['id']); ?>'>
                                <button name="lockerLock" type="submit">REMOVE </button>
                                <input type="hidden" name="action" value="remove">
                            </form>
                            <form action="lockerManager.php" method="POST">
                                <input type="hidden" name="lockerID" value='<?php echo htmlspecialchars($row['id']); ?>'>
                                <input type="hidden" name="userID" placeholder="userID" value='<?php echo htmlspecialchars($_SESSION['user_id']); ?>'>
                                <button name="lockerLock" type="submit">EASYLOCK </button>
                                <input type="hidden" name="action" value="lock">
                            </form>
                        </div>
                    </td>
<?php
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Nessun locker trovato nel database.";
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
        } elseif ($action == 'insert') {
            echo "Lockers inserting.";
            $lockerCount = $_POST['lockerCount'];
            $lockerGroup = $_POST['lockerGroup'];
            $lockerType = $_POST['lockerType'];
            $lockerYear = $_POST['lockerYear'];
            for ($i = 0; $i < $lockerCount; $i++) {
                $stmt = $pdo->prepare("INSERT INTO locker (anno, codice, gruppo, tipo) VALUES (?, CONCAT(?, ?), ?, ? );");
                $stmt->execute([$lockerYear, $lockerGroup, $i + 1, $lockerGroup, $lockerType]);
            }
            echo "$lockerCount lockers inserted.";
        }
    }
} catch (PDOException $e) {
    // Gestisci l'errore (es. loggalo o mostra un messaggio pulito)
    echo "Errore durante l'operazione: " . $e->getMessage();
}
?>