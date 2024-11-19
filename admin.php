<?php 
session_start();
include 'db.php';

// Kullanıcının admin olup olmadığını kontrol etmek için admin sütunu ekliyoruz
if (!isset($_SESSION['kullanici_id'])) {
    header('Location: login.php');
    exit;
}

// Kullanıcı bilgilerini almak için session üzerinden kullanıcı id'yi kullanıyoruz
$kullanici_id = $_SESSION['kullanici_id'];

// Veritabanından kullanıcının admin olup olmadığını kontrol ediyoruz
$query = $conn->prepare("SELECT admin FROM kullanicilar WHERE id = ?");
$query->bind_param("i", $kullanici_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Eğer kullanıcı admin değilse, normal sayfaya yönlendir
if ($user['admin'] != 1) {
    header('Location: index.php'); // Admin olmayan kullanıcıyı ana sayfaya yönlendir
    exit;
}
// Randevuları kontrol et ve süresi geçmiş olanları tamamlandı olarak güncelle
$query_update_randevular = $conn->prepare("UPDATE randevular SET durum = 'Tamamlandı' WHERE randevu_tarihi < NOW() AND durum = 'Bekliyor'");
$query_update_randevular->execute();


// İşlemleri veritabanından alıyoruz
$query_islemler = $conn->prepare("SELECT * FROM islemler");
$query_islemler->execute();
$islemler_result = $query_islemler->get_result();
$islemler = $islemler_result->fetch_all(MYSQLI_ASSOC);

// Randevuları almak için sorgu
$query_randevular = $conn->prepare("SELECT randevular.*, islemler.isim AS islem_adi, kullanicilar.ad, kullanicilar.soyad, kullanicilar.telefon 
                                     FROM randevular
                                     JOIN islemler ON randevular.islem_id = islemler.id
                                     JOIN kullanicilar ON randevular.kullanici_id = kullanicilar.id");
$query_randevular->execute();
$randevular_result = $query_randevular->get_result();
$randevular = $randevular_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .container {
            margin-top: 30px;
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .btn-sm {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Kuaför Salonum</a>
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

    <div class="container">
        <!-- İşlemleri Yönet -->
        <div class="table-container mb-5">
            <h3>Mevcut İşlemler</h3>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Açıklama</th>
                        <th>Süre</th>
                        <th>Fiyat</th>
                        <th>Güncelle</th>
                        <th>Sil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($islemler as $islem): ?>
                        <tr>
                            <td><?= $islem['isim'] ?></td>
                            <td><?= $islem['aciklama'] ?></td>
                            <td><?= $islem['sure'] ?> dakika</td>
                            <td><?= $islem['fiyat'] ?> TL</td>
                            <td>
                                <a href="guncelle_islem.php?islem_id=<?= $islem['id'] ?>" class="btn btn-warning btn-sm">Güncelle</a>
                            </td>
                            <td>
                                <a href="sil_islem.php?islem_id=<?= $islem['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Yeni İşlem Ekle Butonu -->
            <div class="text-end mt-3">
                <a href="ekle_islem.php" class="btn btn-primary btn-sm">Yeni İşlem Ekle</a>
            </div>
        </div>

        <!-- Randevuları Yönet -->
        <div class="table-container">
            <h3>Mevcut Randevular</h3>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                        <th>Müşteri</th>
                        <th>Telefon</th>
                        <th>Sil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($randevular as $randevu): ?>
                        <tr>
                            <td><?= $randevu['randevu_tarihi'] ?></td>
                            <td><?= $randevu['durum'] ?></td>
                            <td><?= $randevu['islem_adi'] ?></td>
                            <td><?= $randevu['ad'] . ' ' . $randevu['soyad'] ?></td>
                            <td><?= $randevu['telefon'] ?></td>
                            <td>
                                <a href="admin_paneli.php?sil_randevu_id=<?= $randevu['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
