<?php
session_start();
session_unset(); // Oturum verilerini temizle
session_destroy(); // Oturumu sonlandır

// Çıkış yaptıktan sonra login.php'ye yönlendir
header("Location: login.php");
exit();
?>
