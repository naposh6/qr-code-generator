<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>GenerQR</title>
    <link rel="stylesheet" href="/QR-code generator/public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>GenerQR</h1>
            <form action="/QR-code generator/public/generate" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Тип контенту</label>
                    <select name="type" id="typeSelect" onchange="toggleInputs()">
                        <option value="url">Посилання</option>
                        <option value="text">Текст</option>
                        <option value="image">Фото</option>
                        <option value="video">Відео</option>
                    </select>
                </div>

                <div id="textContentDiv" class="form-group">
                    <label>Введіть дані</label>
                    <input type="text" name="content" placeholder="https://...">
                </div>

                <div id="fileContentDiv" class="form-group" style="display: none;">
                    <label>Оберіть файл</label>
                    <input type="file" name="qr_file">
                </div>

                <button type="submit">Згенерувати QR</button>
            </form>
        </div>
    </div>

<script>
    function toggleInputs() {
        const type = document.getElementById('typeSelect').value;
        const isFile = (type === 'image' || type === 'video');
        document.getElementById('textContentDiv').style.display = isFile ? 'none' : 'block';
        document.getElementById('fileContentDiv').style.display = isFile ? 'block' : 'none';
    }
</script>
</body>
</html>
