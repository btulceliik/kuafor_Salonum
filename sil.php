<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['randevu_id'])) {
    $randevu_id = $_GET['randevu_id'];

    $query = $conn->prepare("DELETE FROM randevular WHERE id = ? AND kullanici_id = ?");
    $query->execute([$randevu_id, $_SESSION['user_id']]);
}

header("Location: islemler.php");
exit;
?>
