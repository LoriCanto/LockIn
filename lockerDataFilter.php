<?php
require 'auth.php';




$_SESSION['LockerBookingError'] = null;
$posizione = $_POST['posizione'];
$tipo = $_POST['tipo'];
$_SESSION['posizione'] = $posizione;
$_SESSION['tipo'] = $tipo;
header('Location: chooseBigLocker.php');


exit();
