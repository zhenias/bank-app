# Backend Architecture & Design Patterns Guide

## Overview

Bank App Backend jest zbudowany na Laravel 13 z uwzględnieniem architektury opartej na warstwach i designowych wzorcach. Dokument ten opisuje główne wzorce i strukturę kodu, którą powinni trzymać się agenci AI (oraz programiści) podczas implementacji nowych funkcjonalności.

---

## 1. Struktura Katalogów

```
app/
├── Console/              # Komendy CLI
├── Enums/               # Enumeracje do wartości stałych
├── Exceptions/          # Custom wyjątki
├── Http/
│   ├── Controllers/     # Kontrolery REST API
│   ├── Resources/       # Data Transfer Objects (JSON)
│   ├── Requests/        # Form Requests (walidacja)
│   └── Concerns/        # Traits dla kontrolerów
├── Models/              # Modele Eloquent ORM
├── Notifications/       # Klasy notyfikacji
├── Providers/           # Service Providers
├── Rules/               # Custom validation rules
├── Services/            # Logika biznesowa
└── View/                # View models / Response builders
```

---

## 2. Wzorce Architektoniczne

### 2.1 Service Layer Pattern

Całą logikę biznesową umieszczamy w **Service klasach**, kontrolery powinny być lean (cienkie).

**Lokalizacja:** `app/Services/{Domain}/{Feature}/`

**Przykład - CardService:**

```php
<?php
namespace App\Services\Account\Card;

class CardService extends Service
{
    use CardNumberGeneratorService;

    public function createCard(
        string $accountId,
        CardNetwork $network = CardNetwork::VISA,
        CardType $type = CardType::DEBIT,
    ): Card {
        // Generowanie numeru karty
        $cardNumber = $this->generateCardNumber($network);
        
        // Tworzenie rekordu
        return Card::create([
            'account_id'  => $accountId,
            'card_number' => $cardNumber,
            'status'      => 'active',
            // ...
        ]);
    }

    public function blockCard(Card $card): Card
    {
        $card->update(['status' => 'blocked']);
        return $card->fresh();
    }
}
```

**Reguły:**
- ✅ Jedna funkcjonalność = jedna metoda w serwisie
- ✅ Serwisy są injektowane do kontrolerów poprzez DI
- ✅ Serwisy mogą używać innych serwisów
- ✅ Logika biznesowa nigdy w kontrolerze!

---

### 2.2 Enums Pattern

Dla stałych wartości używamy **PHP 8.1+ enums** zamiast stringów/intów.

**Lokalizacja:** `app/Enums/{Domain}/`

**Przykład - CardNetwork:**

```php
<?php
namespace App\Enums\Card;

enum CardNetwork: string
{
    case VISA       = 'visa';
    case MASTERCARD = 'mastercard';

    public function prefix(): array
    {
        return match ($this) {
            self::VISA       => ['4'],
            self::MASTERCARD => ['51', '52', '53', '54', '55'],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::VISA       => 'Visa',
            self::MASTERCARD => 'Mastercard',
        };
    }
}
```

**Reguły:**
- ✅ Enum dla każdego zdefinowanego zestawu wartości
- ✅ Metody w enum'ie do konwersji, labelów, logiki
- ✅ Bezpieczne typowanie - kompilacja IDE i IDE help

---

### 2.3 Controller Pattern

Kontrolery są **cienkie** - delegują pracę do serwisów.

**Lokalizacja:** `app/Http/Controllers/{Domain}/{Feature}/`

**Przykład - CardController:**

```php
<?php
namespace App\Http\Controllers\Account\Card;

class CardController extends Controller
{
    use WithPagination; // Trait do paginacji

    public function __construct(
        private readonly CardService $cardService,
    ) {
    }

    #[AuthorizeToken(['cards-view'], anyScope: true)]
    #[QueryParameter('per_page', description: 'Ilość na stronę', type: 'int')]
    public function allCards()
    {
        $userId = auth()->id();
        
        $cards = Card::whereHas('account', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->paginate($this->perPage(), page: $this->currentPage());

        return CardResource::collection($cards);
    }

    #[AuthorizeToken(['cards-manage'], anyScope: true)]
    public function store(Account $account): JsonResponse
    {
        $this->authorize($account); // Sprawdzenie dostępu
        
        $card = $this->cardService->createCard($account->id);

        return new CardResource($card)
            ->response()
            ->setStatusCode(201);
    }
}
```

**Reguły:**
- ✅ Dependency Injection do konstruktora
- ✅ Użyj Traits do wspólnych funkcjonalności
- ✅ Operacje CRUD -> createCard, updateCard, deleteCard w serwisie
- ✅ Autoryzacja w kontrolerze (`$this->authorize()`)
- ✅ Zwrot Resourceów, nie modeli surowych

---

### 2.4 Model & Eloquent Pattern

Modele reprezentują tabele w bazie danych z logiką relacji.

**Lokalizacja:** `app/Models/{Domain}/`

**Przykład - Card Model:**

```php
<?php
namespace App\Models\Account\Card;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable(['account_id', 'card_number', 'exp_month', 'exp_year', 'cvv', 'status', 'type', 'network'])]
class Card extends Model
{
    use HasFactory;
    use HasUuids;

    protected function casts(): array
    {
        return [
            'exp_month' => 'integer',
            'exp_year'  => 'integer',
            'cvv'       => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_card_id');
    }
}
```

**Reguły:**
- ✅ Krótkie, czytelne nazwy metod relacji
- ✅ Relacje w modelu, nie w kontrolerze
- ✅ Attribute Casting do automatycznej konwersji
- ✅ Fillable lub Guarded do bezpieczeństwa
- ✅ UUID zamiast auto-increment IDs

---

### 2.5 Resource Pattern (DTO)

Resources transformują modele w JSON.

**Lokalizacja:** `app/Http/Resources/{Domain}/`

**Przykład - CardResource:**

```php
<?php
namespace App\Http\Resources\Account\Card;

class CardResource extends JsonResource
{
    use CardNumberGeneratorService;

    public function toArray(Request $request): array
    {
        return [
            /* @example "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d" */
            'id' => $this->id,
            /* @example "**** **** **** 1234" */
            'card_number' => $this->when(
                $this->card_number,
                $this->maskCardNumber($this->card_number),
            ),
            /* @example "1234" */
            'card_last_four' => $this->when(
                $this->card_number,
                substr($this->card_number, -4),
            ),
            'network' => $this->network,
            'type' => $this->type,
            'status' => $this->status,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

**Reguły:**
- ✅ Nigdy nie ekspozuj całego modelu surowo
- ✅ Maskuj dane wrażliwe (np. numery kart, CVV)
- ✅ Zwróć `$this->when()` dla pól warunkowych
- ✅ Zawsze ISO 8601 dla dat
- ✅ Dokumentuj każde pole za pomocą `@example`

---

### 2.6 Authorization & Authentication

**Passport OAuth2:** Używamy Laravel Passport do OAuth2 authorization.

```php
#[AuthorizeToken(['cards-view'], anyScope: true)]
public function index(Account $account)
{
    // Middleware sprawdzi token i scopes
    if ($account->user_id !== auth()->id()) {
        throw new AccessDeniedHttpException('Access denied');
    }
}
```

**Reguły:**
- ✅ Użyj `#[AuthorizeToken]` atrybutu do sprawdzenia scopes
- ✅ Zawsze sprawdzaj ownership w metodzie
- ✅ Zwróć `AccessDeniedHttpException` dla błędów dostępu

---

### 2.7 Traits Pattern

Wspólna funkcjonalność w Traits, aby uniknąć duplikacji.

**Przykład - WithPagination:**

```php
<?php
namespace App\Http\Concerns;

trait WithPagination
{
    protected function perPage(): int
    {
        return (int) request()->get('per_page', 20);
    }

    protected function currentPage(): int
    {
        return (int) request()->get('page', 1);
    }
}
```

**Reguły:**
- ✅ Traits dla krzyżowych problemów (pagination, timestamps, etc.)
- ✅ Umieszczaj w `app/Http/Concerns/` lub `app/Services/Concerns/`

---

### 2.8 API Documentation

Używamy **Dedoc Scramble** z atrybutami PHP do dokumentacji API.

```php
/**
 * Zarządzanie kartami płatniczymi.
 *
 * @tags Karty
 * @description API dla zarządzania kartami
 */
class CardController extends Controller
{
    /**
     * Wyświetla listę wszystkich kart użytkownika.
     */
    #[AuthorizeToken(['cards-view'], anyScope: true)]
    #[QueryParameter('per_page', description: 'Ilość na stronę', type: 'int', default: 20)]
    #[QueryParameter('page', description: 'Numer strony', type: 'int', default: 1)]
    #[PathParameter('account', description: 'ID konta', type: 'string', format: 'uuid')]
    public function index(Account $account) { }
}
```

**Reguły:**
- ✅ Dokumentuj każdy endpoint w zasobie
- ✅ Dodaj `@tags` dla grupowania
- ✅ Dokumentuj parametry z `#[QueryParameter]`, `#[PathParameter]`
- ✅ Dokumentuj w phpdoc parametry, response, exceptiony

---

## 3. API Routing Pattern

**Lokalizacja:** `routes/api.php`

```php
Route::middleware(['auth:api', 'adult'])->group(function () {
    // Cards
    Route::get('cards', [CardController::class, 'allCards']);
    Route::get('cards/{card}', [CardController::class, 'show']);
    Route::apiResource('accounts.cards', CardController::class)->only(['index', 'store']);
    
    // Block/Unblock are custom actions
    Route::patch('cards/{card}/block', [CardController::class, 'block']);
    Route::patch('cards/{card}/unblock', [CardController::class, 'unblock']);
    Route::delete('cards/{card}', [CardController::class, 'destroy']);
});
```

**Reguły:**
- ✅ Używaj `apiResource()` do RESTful CRUD
- ✅ Custom akcje jak `.../block` jako dodatkowe routes
- ✅ Middleware na grupach, nie indywidualne
- ✅ Hierarchia: `/accounts/{id}/cards` dla zagnieżdżonych zasobów

---

## 4. Guidelines dla Nowych Features

### Dodawanie nowej funkcjonalności do domeny:

1. **Model** (`app/Models/{Domain}/NewFeature.php`)
   - Relacje do innych modeli
   - Fillable/Guarded
   - Casts

2. **Service** (`app/Services/{Domain}/NewFeatureService.php`)
   - Biznesowa logika
   - Metody dla CRUD operacji

3. **Controller** (`app/Http/Controllers/{Domain}/NewFeatureController.php`)
   - Lean - delegacja do serwisu
   - Authorization checks
   - Resource responses

4. **Resource** (`app/Http/Resources/{Domain}/NewFeatureResource.php`)
   - Transformacja do JSON
   - Dokumentacja pól

5. **Routes** (routes/api.php)
   - RESTful CRUD paths
   - Custom action routes
   - Middleware/Authorization

6. **Testy** (tests/Feature/)
   - Test każdy endpoint
   - Test authorization
   - Test data masking

7. **Migracje** (database/migrations/)
   - Tabel, foreign keys, indexes
   - Seeders do demo danych

---

## 5. Database Patterns

### UUID Primary Keys

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Card extends Model
{
    use HasUuids;
    // ...
}
```

### Polymorphic Relations (Future)

```php
// Dla features z wiele typami
class Notification extends Model
{
    public function notifiable()
    {
        return $this->morphTo();
    }
}
```

---

## 6. Security Patterns

### Password Hashing
```php
use Illuminate\Support\Facades\Hash;

$user->password = Hash::make('password');
```

### Data Masking
```php
// W Resource
'card_number' => $this->maskCardNumber($this->card_number),
```

### CORS Configuration
```php
// config/cors.php - już skonfigurowany
```

---

## 7. Common Mistakes to Avoid

❌ Logika biznesowa w kontrolerach  
✅ Umieść w Service klasach

❌ Zwracanie surowych modeli w API  
✅ Zawsze użyj Resources

❌ String enumeracje `'visa'` zamiast enums  
✅ Użyj PHP Enums

❌ Duplicated validation logic  
✅ Form Requests + Custom Rules

❌ N+1 queries  
✅ Eager load relacje `with()`

❌ Brak error handling  
✅ Custom Exceptions, proper HTTP codes

---

## 8. Dependencies & Frameworks

- **Laravel 13** - Full-stack framework
- **Laravel Passport** - OAuth2 authentication
- **Dedoc Scramble** - API documentation
- **Laravel Tinker** - REPL
- **PHPUnit** - Testing
- **PHP 8.4+** - Language features (enums, attributes, etc.)

---

## 9. Testing Patterns

```php
namespace Tests\Feature;

class CardControllerTest extends TestCase
{
    #[Test]
    public function it_returns_all_cards_for_authenticated_user()
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $cards = Card::factory(3)->for($account)->create();

        $response = $this->actingAs($user)
            ->getJson('/api/cards');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
```

---

## 10. Useful Commands

```bash
# Generate new service
php artisan make:service Services/Account/Card/CardService

# Generate resource
php artisan make:resource Account/Card/CardResource

# Generate model with migration
php artisan make:model Models/Account/Card/Card -m

# Generate controller
php artisan make:controller Account/Card/CardController --api

# Run migrations
php artisan migrate

# Run tests
php artisan test

# API docs
php artisan scramble:generate
```

---

## AI Agent Instructions

Kiedy dodajesz nowe funkcjonalności:

1. **ZAWSZE** rozpocznij od Service klasach
2. **NIGDY** nie umieszczaj logiki w kontrolerach
3. **ZAWSZE** używaj Resources do API responses
4. **ZAWSZE** dokumentuj poprzez atrybuty PHP
5. **ZAWSZE** sprawdzaj authorization
6. **ZAWSZE** maskuj dane wrażliwe
7. **HINTED TYPES** - używaj strict type hints
8. Read-only properties gdzie to możliwe
9. Dependency Injection do konstruktora
10. Keep methods small and focused

Powodzenia! 🚀

