<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #fff; padding: 24px; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    @yield('content')
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>