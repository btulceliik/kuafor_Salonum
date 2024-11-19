<?php
session_start();
include("db.php"); // Veritabanı bağlantısını dahil et

// Eğer kullanıcı oturum açmamışsa login.php'ye yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcının ID'sini al
$user_id = $_SESSION['kullanici_id'];

// Kullanıcının randevularını çekmek için sorgu
$query = $conn->prepare("
    SELECT i.id, i.isim, i.sure, i.fiyat, r.id AS randevu_id, r.randevu_tarihi
    FROM islemler i
    JOIN randevular r ON i.id = r.islem_id
    WHERE r.kullanici_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();

// Sorgudan dönen sonuçları al
$result = $query->get_result();

// Verileri bir diziye aktar
$randevular = [];
while ($row = $result->fetch_assoc()) {
    $randevular[] = $row;
}

// Randevu silme işlemi
if (isset($_GET['sil_id'])) {
    $randevu_id = $_GET['sil_id'];
    $delete_query = $conn->prepare("DELETE FROM randevular WHERE id = ?");
    $delete_query->bind_param("i", $randevu_id);
    if ($delete_query->execute()) {
        // Silme işlemi başarılıysa sayfayı yenile
        header("Location: islemler.php");
        exit();
    } else {
        $error_message = "Randevu silinirken bir hata oluştu.";
    }
}

// Toplam fiyat hesapla
$totalFiyat = array_reduce($randevular, fn($carry, $item) => $carry + $item['fiyat'], 0);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İşlemlerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Kuaför Salonum</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="islemler.php">İşlemlerim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>İşlemlerim</h2>
        <p>Yapmış olduğunuz randevular aşağıda listelenmiştir.</p>
        
        <!-- Hata mesajı -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>İşlem Adı</th>
                    <th>Randevu Saati</th>
                    <th>İşlem Süresi</th>
                    <th>Fiyat</th>
                    <th>Sil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($randevular as $randevu): ?>
                    <tr>
                        <td><?= htmlspecialchars($randevu['isim']) ?></td>
                        <td><?= $randevu['randevu_tarihi'] ?></td>
                        <td><?= $randevu['sure'] ?> dakika</td>
                        <td><?= $randevu['fiyat'] ?> TL</td>
                        <td>
                            <a href="islemler.php?sil_id=<?= $randevu['randevu_id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h3>Toplam Ücret: <?= $totalFiyat ?> TL</h3>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
