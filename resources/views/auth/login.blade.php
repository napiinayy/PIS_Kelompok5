<!DOCTYPE html>
<html lang="id" class="h-full bg-gradient-to-br from-primary-50 to-indigo-100">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Split Bill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"/>
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center px-4" style="background:linear-gradient(135deg,#EEF2FF 0%,#E0E7FF 100%)">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Split<span class="text-indigo-600">Bill</span></h1>
        <p class="text-gray-500 mt-1 text-sm">Kelompok 5 SI4803 — Telkom University</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Masuk ke akun</h2>
        @if($errors->any())
        <div class="bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg mb-4 border border-red-100">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="rounded text-indigo-600">
                <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition text-sm">Masuk</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">Daftar sekarang</a></p>
    </div>
</div>
</body></html>
