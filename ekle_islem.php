<?php
session_start();
include 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri al
    $isim = $_POST['isim'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $sure = intval($_POST['sure'] ?? 0); // Sayısal değer
    $fiyat = floatval($_POST['fiyat'] ?? 0); // Ondalık değer
    $resim = $_POST['resim'] ?? '';

    // Yeni işlem ekleme sorgusu
    $insert_query = $conn->prepare("INSERT INTO islemler (isim, aciklama, sure, fiyat, resim) VALUES (?, ?, ?, ?, ?)");
    $insert_query->bind_param("sssds", $isim, $aciklama, $sure, $fiyat, $resim);

    if ($insert_query->execute()) {
        // Başarı mesajı ve yönlendirme
        header("Location: admin.php?ekleme=basarili");
        exit;
    } else {
        // Hata mesajı
        echo "Ekleme sırasında bir hata oluştu: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İşlem Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Yeni İşlem Ekle</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="isim" class="form-label">İşlem Adı</label>
                <input type="text" name="isim" id="isim" class="form-control" placeholder="İşlem adı girin" required>
            </div>
            <div class="mb-3">
                <label for="aciklama" class="form-label">Açıklama</label>
                <textarea name="aciklama" id="aciklama" class="form-control" placeholder="Açıklama girin" required></textarea>
            </div>
            <div class="mb-3">
                <label for="sure" class="form-label">Süre (dakika)</label>
                <input type="number" name="sure" id="sure" class="form-control" placeholder="Süre girin" required>
            </div>
            <div class="mb-3">
                <label for="fiyat" class="form-label">Fiyat</label>
                <input type="number" step="0.01" name="fiyat" id="fiyat" class="form-control" placeholder="Fiyat girin" required>
            </div>
            <div class="mb-3">
                <label for="resim" class="form-label">Resim URL</label>
                <input type="text" name="resim" id="resim" class="form-control" placeholder="Resim URL girin">
            </div>
            <button type="submit" class="btn btn-primary">Ekle</button>
        </form>
    </div>
</body>
</html>
