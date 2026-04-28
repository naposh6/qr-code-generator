<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація - GenerQR</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Реєстрація</h1>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Зареєструватися</button>
        </form>
        <p>Вже є акаунт? <a href="login">Увійти</a></p>
    </div>
</div>
</body>
</html>