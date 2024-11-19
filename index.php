<?php
session_start();
include("db.php"); // Veritabanı bağlantısını dahil et

// Eğer kullanıcı oturum açmamışsa, login.php'ye yönlendir
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Veritabanından işlemleri çek
$sql = "SELECT * FROM islemler";
$result = $conn->query($sql);

// İşlemleri bir diziye kaydet
$islemler = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $islemler[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anasayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            object-fit: cover;
            height: 200px;
            width: 100%;
        }

        .card-body {
            background-color: #ffffff;
            padding: 20px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .card-text {
            font-size: 1rem;
            color: #555;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 1200px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: bold;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .col-md-4 {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" > 
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

    <!-- İşlem Kartları -->
    <div class="container mt-5">
        <h2>Kuaför İşlemleri</h2>
        <div class="row">
            <?php foreach ($islemler as $islem): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= htmlspecialchars($islem['resim']) ?>" class="card-img-top" alt="<?= htmlspecialchars($islem['isim']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($islem['isim']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($islem['aciklama']) ?></p>
                            <p><strong>Fiyat:</strong> <?= htmlspecialchars($islem['fiyat']) ?> TL</p>
                            <p><strong>Süre:</strong> <?= htmlspecialchars($islem['sure']) ?> dakika</p>
                            <a href="randevu_al.php?islem_id=<?= htmlspecialchars($islem['id']) ?>" class="btn btn-primary">Randevu Al</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
