<?php
session_start();
include("db.php"); // Veritabanı bağlantısını dahil et

// Eğer kullanıcı oturum açmamışsa login.php'ye yönlendir
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcının ID'sini al
$user_id = $_SESSION['kullanici_id'];  // Bu doğru oturum ID'si olmalı

// Kullanıcının admin olup olmadığını kontrol et
$sql = "SELECT admin FROM kullanicilar WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Eğer kullanıcı admin değilse, randevu alabilmesi için işlemi devam ettir
if ($user['admin'] == 0) {
    // İşlem ID'si URL'den alınıyor
    if (isset($_GET['islem_id'])) {
        $islem_id = $_GET['islem_id'];
    } else {
        echo "Geçersiz işlem!";
        exit();
    }

    // İşlem bilgilerini veritabanından çek
    $sql = "SELECT * FROM islemler WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $islem_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "İşlem bulunamadı!";
        exit();
    }

    $islem = $result->fetch_assoc();
    $sure = $islem['sure']; // işlem süresi (dakika cinsinden)

    // Randevu formu gönderildiğinde işlemi kaydet
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $randevu_tarihi = $_POST['randevu_tarihi']; // Tarih (Y-m-d)
        $randevu_saati = $_POST['randevu_saati'];   // Saat (H:i)

        // Tarih ve saat bilgilerini birleştir
        $randevu_datetime = $randevu_tarihi . ' ' . $randevu_saati . ':00'; // Y-m-d H:i:s formatı

        // İşlem süresine göre randevu bitiş zamanını hesapla
        $randevu_bitis = date('Y-m-d H:i:s', strtotime($randevu_datetime) + ($sure * 60));

        // Kullanıcının aynı tarihte ve saatte randevusu olup olmadığını kontrol et
        $check_sql = "SELECT * FROM randevular r
                      JOIN islemler i ON r.islem_id = i.id
                      WHERE r.islem_id = ? 
                      AND (
                          (r.randevu_tarihi BETWEEN ? AND ?) OR
                          (? BETWEEN r.randevu_tarihi AND DATE_ADD(r.randevu_tarihi, INTERVAL i.sure MINUTE))
                      )";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("isss", $islem_id, $randevu_datetime, $randevu_bitis, $randevu_datetime);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<div class='alert alert-danger'>Bu tarihte ve saatte zaten bir randevunuz var. Lütfen farklı bir tarih ve saat seçin.</div>";
        } else {
            // Randevu oluşturuluyor
            $insert_sql = "INSERT INTO randevular (kullanici_id, islem_id, randevu_tarihi, durum) 
                           VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $durum = 'Bekliyor';  // Randevu durumu
            $stmt->bind_param("iiss", $user_id, $islem_id, $randevu_datetime, $durum);

            if ($stmt->execute()) {
                // Randevu başarılıysa, aynı sayfaya yönlendir
                header("Location: randevu_al.php?islem_id=$islem_id&success=1");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Bir hata oluştu, lütfen tekrar deneyin.</div>";
            }
        }
    }

    // Diğer kullanıcıların randevularını gösterme
    $randevular_sql = "SELECT r.randevu_tarihi FROM randevular r
                       WHERE r.islem_id = ? AND r.durum != 'İptal'
                       ORDER BY r.randevu_tarihi";
    $randevular_stmt = $conn->prepare($randevular_sql);
    $randevular_stmt->bind_param("i", $islem_id);
    $randevular_stmt->execute();
    $randevular_result = $randevular_stmt->get_result();
} else {
    // Eğer kullanıcı admin ise admin paneline yönlendir
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Al</title>
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
                        <a class="nav-link" href="islemler.php">İşlemlerim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Randevu Al</h2>
        <p>Seçtiğiniz işlem için randevu almak üzere tarih ve saatinizi belirleyin.</p>

        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo "<div class='alert alert-success'>Randevunuz başarıyla alındı!</div>";
        }
        ?>

        <form action="randevu_al.php?islem_id=<?php echo $islem_id; ?>" method="POST">
            <div class="mb-3">
                <label for="randevu_tarihi" class="form-label">Randevu Tarihi</label>
                <input type="date" class="form-control" id="randevu_tarihi" name="randevu_tarihi" required>
            </div>
            <div class="mb-3">
                <label for="randevu_saati" class="form-label">Randevu Saati</label>
                <input type="time" class="form-control" id="randevu_saati" name="randevu_saati" required>
            </div>
            <button type="submit" class="btn btn-primary">Randevu Oluştur</button>
        </form>

        <h3 class="mt-5">Randevu Bulunan Zamanlar</h3>
        <ul class="list-group">
            <?php
            while ($randevu = $randevular_result->fetch_assoc()) {
                $randevu_start = strtotime($randevu['randevu_tarihi']);
                $randevu_end = $randevu_start + ($sure * 60);
                $randevu_end = date('H:i', $randevu_end);

                echo "<li class='list-group-item'>Randevu Zamanı: " . date('Y-m-d H:i', $randevu_start) . " - $randevu_end</li>";
            }
            ?>
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
