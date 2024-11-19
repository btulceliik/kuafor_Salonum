<?php
session_start();
include("db.php"); // Veritabanı bağlantısını dahil et

// Eğer form gönderildiyse
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    // SQL sorgusuyla veritabanında kullanıcıyı kontrol et
    $sql = "SELECT * FROM kullanicilar WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Kullanıcı bulundu, veriyi al
        $user = $result->fetch_assoc();

        // Şifreyi kontrol et
        if ($sifre == $user['sifre']) { // Güvenlik için password_hash kullanmanız gerekebilir
            $_SESSION['email'] = $email; // Oturumda email saklanır
            $_SESSION['kullanici_id'] = $user['id']; // Kullanıcı ID'si

            // Admin kontrolü
            if ($user['admin'] == 1) {
                // Admin ise admin paneline yönlendir
                header("Location: admin.php");
            } else {
                // Kullanıcıysa anasayfaya yönlendir
                header("Location: index.php");
            }
            exit();
        } else {
            $error_message = "Geçersiz şifre.";
        }
    } else {
        $error_message = "Bu email ile bir kullanıcı bulunamadı.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">


    <style>
    body { 
        background-color: #f4f4f4; 
    }
    .login-container { 
        max-width: 600px; 
        margin: 150px auto; 
        padding: 50px; 
        background-color: #fff; 
        border-radius: 16px; 
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); 
    }
    .welcome-text { 
        font-size: 36px; /* Büyük yazı boyutu */
        font-weight: bold; /* Kalın yazı */
        margin-bottom: 30px; /* Alt boşluk */
        color: #333; /* Yazı rengi */
    }
    .form-control { 
        border-radius: 35px; 
        height: 60px; 
        font-size: 20px; 
        padding: 15px 25px; 
    }
    .btn-primary { 
        width: 100%; 
        border-radius: 35px; 
        height: 60px; 
        font-size: 20px; 
        padding: 15px 25px; 
    }
    .alert { 
        border-radius: 30px; 
        font-size: 18px; 
        padding: 15px; 
    }
    h3 {
        font-size: 28px; 
        margin-bottom: 20px; 
    }
</style>



</head>
<body>
<div class="container">
    <div class="login-container">
        <h1 class="welcome-text text-center">Kuaför Salonuma Hoşgeldiniz</h1> <!-- Büyük başlık -->
        <h3 class="text-center mb-4">Giriş Yap</h3> <!-- Giriş yap başlığı -->

        <!-- Hata mesajı -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Giriş formu -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Giriş Yap</button>
        </form>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
