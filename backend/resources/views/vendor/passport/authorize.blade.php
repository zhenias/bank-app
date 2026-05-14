<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autoryzacja - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1), 0 4px 16px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }
        .header {
            background: #1976d2;
            color: #fff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header-icon .material-icons { font-size: 24px; }
        .header-text p:first-child { font-size: 12px; opacity: 0.85; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-text p:last-child { font-size: 20px; font-weight: 500; }

        .content { padding: 24px; }
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff8e1;
            border: 1px solid #ffcc02;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .alert .material-icons { color: #f57f17; font-size: 22px; }
        .alert p { font-size: 14px; color: #5d4037; line-height: 1.5; }

        .section-title {
            font-size: 11px;
            font-weight: 600;
            color: #1976d2;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .scope-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 24px;
            max-height: 320px;
            overflow-y: auto;
        }
        .scope-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: #fafafa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.15s;
        }
        .scope-item:hover { background: #f0f0f0; border-color: #bdbdbd; }
        .scope-item.disabled {
            opacity: 0.5;
            background: #f5f5f5;
        }
        .scope-item.disabled .scope-text { text-decoration: line-through; color: #9e9e9e; }
        .scope-item.disabled .material-icons { color: #bdbdbd; }
        .scope-checkbox {
            width: 20px;
            height: 20px;
            accent-color: #1976d2;
            cursor: pointer;
            flex-shrink: 0;
        }
        .scope-item .material-icons { color: #1976d2; font-size: 20px; flex-shrink: 0; }
        .scope-text { flex: 1; min-width: 0; }
        .scope-text p { font-size: 14px; color: #212121; font-weight: 500; }
        .scope-text span { font-size: 12px; color: #757575; display: block; margin-top: 1px; }

        .full-access-banner {
            background: #fff3e0;
            border: 1px solid #ffb74d;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .full-access-banner .material-icons { color: #ef6c00; font-size: 22px; }
        .full-access-banner p { font-size: 13px; color: #bf360c; line-height: 1.5; }
        .full-access-banner strong { font-weight: 600; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .user-avatar {
            width: 44px;
            height: 44px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .user-avatar .material-icons { color: #757575; font-size: 22px; }
        .user-text p:first-child { font-size: 15px; font-weight: 500; color: #212121; }
        .user-text p:last-child { font-size: 13px; color: #757575; margin-top: 2px; }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            min-width: 160px;
        }
        .btn-primary {
            background: #1976d2;
            color: #fff;
            box-shadow: 0 2px 4px rgba(25,118,210,0.3);
        }
        .btn-primary:hover { background: #1565c0; box-shadow: 0 4px 12px rgba(25,118,210,0.4); transform: translateY(-1px); }
        .btn-secondary {
            background: #fff;
            color: #616161;
            border: 1.5px solid #e0e0e0;
        }
        .btn-secondary:hover { background: #fafafa; border-color: #bdbdbd; }

        .footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #eeeeee;
            text-align: center;
        }
        .footer p { font-size: 12px; color: #9e9e9e; }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="header-icon">
            <span class="material-icons">security</span>
        </div>
        <div class="header-text">
            <p>Aplikacja żądająca dostępu</p>
            <p>{{ $client->name }}</p>
        </div>
    </div>

    <div class="content">
        <div class="alert">
            <span class="material-icons">warning_amber</span>
            <p>Ta aplikacja chce uzyskać dostęp do Twojego konta bankowego. Przejrzyj i wybierz uprawnienia, które chcesz przyznać.</p>
        </div>

        <p class="section-title">Wybierz uprawnienia</p>

        @if(count($scopes) === 0 || (count($scopes) === 1 && $scopes[0]->id === '*'))
            <div class="full-access-banner">
                <span class="material-icons">admin_panel_settings</span>
                <p><strong>Pełny dostęp</strong> — Aplikacja żąda dostępu do wszystkich funkcji. Możesz ograniczyć uprawnienia odznaczając wybrane pozycje.</p>
            </div>

            @php
                $allScopes = \Laravel\Passport\Passport::scopes();
            @endphp
            <div class="scope-list">
                @foreach($allScopes as $scope)
                    <label class="scope-item">
                        <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" class="scope-checkbox" checked>
                        <span class="material-icons">check_circle</span>
                        <div class="scope-text">
                            <p>{{ $scope->description ?? $scope->id }}</p>
                            <span>{{ $scope->id }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        @else
            <div class="scope-list">
                @foreach($scopes as $scope)
                    <label class="scope-item">
                        <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" class="scope-checkbox" checked>
                        <span class="material-icons">check_circle</span>
                        <div class="scope-text">
                            <p>{{ $scope->description ?? $scope->id }}</p>
                            <span>{{ $scope->id }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        <div class="user-info">
            <div class="user-avatar">
                <span class="material-icons">person</span>
            </div>
            <div class="user-text">
                <p>{{ $user->name }}</p>
                <p>{{ $user->email }}</p>
            </div>
        </div>

        <div class="actions">
            <form method="post" action="/oauth/authorize" id="auth-form">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <input type="hidden" name="scopes" id="scopes-input" value="">
                <button type="submit" id="authorize" class="btn btn-primary">
                    <span class="material-icons" style="font-size: 18px;">login</span>
                    Autoryzuj
                </button>
            </form>

            <form method="post" action="/oauth/authorize">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-secondary">
                    <span class="material-icons" style="font-size: 18px;">block</span>
                    Odmów
                </button>
            </form>
        </div>

        <div class="footer">
            <p>Możesz wycofać dostęp w ustawieniach konta w dowolnym momencie.</p>
        </div>
    </div>
</div>

<script>
    const authForm = document.getElementById('auth-form');
    const scopesInput = document.getElementById('scopes-input');

    function syncScopes() {
        const checked = Array.from(document.querySelectorAll('.scope-checkbox:checked')).map(cb => cb.value);
        scopesInput.value = checked.join(' ');

        const btn = document.getElementById('authorize');
        btn.disabled = checked.length === 0;
    }

    document.querySelectorAll('.scope-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            this.closest('.scope-item').classList.toggle('disabled', !this.checked);
            syncScopes();
        });
    });

    syncScopes();

    authForm.addEventListener('submit', function(e) {
        syncScopes();
    });
</script>
</body>
</html>
