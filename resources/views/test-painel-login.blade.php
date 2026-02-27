<!DOCTYPE html>
<html>
<head>
    <title>Test Painel Login (Sem Livewire)</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Painel Login - {{ tenant('name') ?? 'Central' }}</h1>
    <p>Login direto SEM Livewire (redirect para /painel)</p>

    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="/test-painel-login">
        @csrf
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="admin@parkerpizzaria.com" required>
        </div>
        <div>
            <label>Senha:</label>
            <input type="password" name="password" value="parker123" required>
        </div>
        <button type="submit">Login e Redirecionar para /painel</button>
    </form>

    <hr>
    <p>Session ID: {{ session()->getId() }}</p>
    <p>CSRF Token: {{ csrf_token() }}</p>
</body>
</html>
