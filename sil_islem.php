<?php
include 'db.php';

if (isset($_GET['islem_id'])) {
    $islem_id = $_GET['islem_id'];

    $query = $conn->prepare("DELETE FROM islemler WHERE id = ?");
    $query->execute([$islem_id]);
}

header("Location: admin.php");
exit;
?>
