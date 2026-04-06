<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naya</title>
    @vite('resources/js/app.ts')
</head>
<body>
    <div id="app"></div>
</body>
</html>
