<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autoryzacja - Bank Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Bank Online System</h1>
        <p class="text-gray-600 mt-2">Autoryzacja aplikacji</p>
    </div>

    <div class="border-t border-b border-gray-200 py-4 mb-6">
        <p class="text-gray-700">
            <strong>{{ $client->name }}</strong> chce uzyskać dostęp do Twojego konta.
        </p>

        @if(count($scopes) > 0)
            <div class="mt-3">
                <p class="text-sm font-medium text-gray-700">Uprawnienia:</p>
                <ul class="list-disc list-inside text-sm text-gray-600 mt-1">
                    @foreach($scopes as $scope)
                        <li>{{ $scope->description }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="flex space-x-4">
        <!-- Przycisk Autoryzuj -->
        <form method="post" action="/oauth/authorize" class="flex-1">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                Autoryzuj
            </button>
        </form>

        <!-- Przycisk Anuluj -->
        <form method="post" action="/oauth/authorize" class="flex-1">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="w-full bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition">
                Anuluj
            </button>
        </form>
    </div>
</div>
</body>
</html>
