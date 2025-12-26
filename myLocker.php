<?php
require 'auth.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" ;>
    <title>LockIn: prenota il tuo amradietto!</title>
    <meta name="viewport" content="width=device-width,initial-scale=    1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="LockInStyle.css">
</head>

<body>
    <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?></h5>
    <h1>Personl Page</h1>
    <div id="queueing_section">
        <form action="queueManager.php" method="POST">
            <h2>Tutti gli armadietti sono stati presi, mettiti in coda!</h2>
            <input type="hidden" name="userID" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="action" value="remove">
            <button type="submit">Toglimi dalla coda</button>
        </form>
    </div>
    <div id="auth_buttons">
        <form action="userManager.php" method="POST">
            <input type="hidden" name="action" value="logOut">
            <button type="submit">Log Out</button>
        </form>
</body>

</html>