<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
</head>
<body>
    <h1>Test Upload</h1>
    <form action="/upload-test" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Test Upload</button>
    </form>
</body>
</html>