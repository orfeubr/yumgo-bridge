<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Login - {{ tenant('name') ?? 'Central' }}</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(Auth::check())
        <p>✅ Logado como: {{ Auth::user()->name }}</p>
        <form method="POST" action="/test-logout">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @else
        <form method="POST" action="/test-login">
            @csrf
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="admin@parkerpizzaria.com" required>
            </div>
            <div>
                <label>Senha:</label>
                <input type="password" name="password" value="parker123" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <p>CSRF Token: {{ csrf_token() }}</p>
    @endif
</body>
</html>
