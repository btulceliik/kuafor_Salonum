<?php
session_start();
include 'db.php';

// Admin kontrolü eklenmeli (admin oturumu için ayrı bir yapı düşünülmeli).

if (isset($_GET['islem_id'])) {
    $islem_id = $_GET['islem_id'];

    // İşlem verisini almak için sorgu
    $query = $conn->prepare("SELECT * FROM islemler WHERE id = ?");
    $query->bind_param("i", $islem_id);
    $query->execute();
    $result = $query->get_result();
    
    // Eğer işlem bulunduysa
    if ($row = $result->fetch_assoc()) {
        $islem = $row;
    } else {
        // Eğer işlem bulunamazsa
        echo "İşlem bulunamadı!";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Güncelleme işlemi
    $isim = $_POST['isim'];
    $aciklama = $_POST['aciklama'];
    $sure = $_POST['sure'];
    $fiyat = $_POST['fiyat'];
    $resim = $_POST['resim'];

    // Veritabanını güncelleme işlemi
    $update_query = $conn->prepare("UPDATE islemler SET isim = ?, aciklama = ?, sure = ?, fiyat = ?, resim = ? WHERE id = ?");
    $update_query->execute([$isim, $aciklama, $sure, $fiyat, $resim, $islem_id]);

    // Başarı mesajı
    header("Location: admin.php?guncelleme=basarili");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İşlem Güncelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>İşlem Güncelle</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="isim" class="form-label">İşlem Adı</label>
                <input type="text" name="isim" id="isim" class="form-control" value="<?= htmlspecialchars($islem['isim']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="aciklama" class="form-label">Açıklama</label>
                <textarea name="aciklama" id="aciklama" class="form-control" required><?= htmlspecialchars($islem['aciklama']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="sure" class="form-label">Süre (dakika)</label>
                <input type="number" name="sure" id="sure" class="form-control" value="<?= htmlspecialchars($islem['sure']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="fiyat" class="form-label">Fiyat</label>
                <input type="number" step="0.01" name="fiyat" id="fiyat" class="form-control" value="<?= htmlspecialchars($islem['fiyat']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="resim" class="form-label">Resim URL</label>
                <input type="text" name="resim" id="resim" class="form-control" value="<?= htmlspecialchars($islem['resim']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </div>
</body>
</html>
