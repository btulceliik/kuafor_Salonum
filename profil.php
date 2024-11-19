<?php
session_start();
include("db.php"); // Veritabanı bağlantısını dahil et

// Eğer kullanıcı oturum açmamışsa login.php'ye yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı ID'sini al
$user_id = $_SESSION['kullanici_id'];

// Kullanıcı bilgilerini al
$query = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

// Eğer kullanıcı mevcutsa, verilerini çek
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Kullanıcı bulunamadı.";
    exit();
}

// Form gönderildiğinde güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];

    // Kullanıcı bilgilerini güncelle
    $update_query = $conn->prepare("
        UPDATE kullanicilar
        SET ad = ?, soyad = ?, email = ?, telefon = ?
        WHERE id = ?
    ");
    $update_query->bind_param("ssssi", $ad, $soyad, $email, $telefon, $user_id);

    if ($update_query->execute()) {
        $_SESSION['success_message'] = "Bilgiler başarıyla güncellendi."; // Mesajı oturumda sakla
        // Veritabanında güncelleme başarılı olduysa, sayfayı yeniden yükle
        header("Location: profil.php");
        exit();
    } else {
        $error_message = "Bilgiler güncellenirken bir hata oluştu.";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim</title>
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
        <h2>Profil Bilgilerim</h2>

        <!-- Başarı ve Hata Mesajları -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?> <!-- Mesajı bir kez gösterdikten sonra temizle -->
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Profil Güncelleme Formu -->
        <form action="profil.php" method="POST">
            <div class="mb-3">
                <label for="ad" class="form-label">Ad</label>
                <input type="text" class="form-control" id="ad" name="ad" value="<?= htmlspecialchars($user['ad']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="soyad" class="form-label">Soyad</label>
                <input type="text" class="form-control" id="soyad" name="soyad" value="<?= htmlspecialchars($user['soyad']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-posta</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefon" class="form-label">Telefon</label>
                <input type="text" class="form-control" id="telefon" name="telefon" value="<?= htmlspecialchars($user['telefon']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Bilgilerimi Güncelle</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
