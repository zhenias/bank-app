<?php

namespace App\Http\Controllers\Notification;

use App\Http\Concerns\WithPagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Powiadomienia.
 *
 * @tags Powiadomienia użytkownika
 */
class NotificationController extends Controller
{
    use WithPagination;

    /**
     * Wyświetlanie listy powiadomień użytkownika.
     *
     * Wyświetla powiadomienia użytkownika, wszystkie, które ma na koncie.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(
                perPage: $this->perPage(),
                page: $this->currentPage(),
            );

        return NotificationResource::collection(
            $notifications,
        );
    }

    /**
     * Nie przeczytane powiadomienia.
     *
     * Zwraca liczbę nieprzeczytanych powiadomień użytkownika.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Odczytywanie powiadomienia.
     *
     * Odczytuje pojedyncze powiadomienie, oznaczając je jako przeczytane. Użytkownik musi być właścicielem powiadomienia.
     */
    #[PathParameter('notificationId', description: 'Identyfikator powiadomienia', type: 'int', example: '123e4567-e89b-12d3-a456-426614174000')]
    public function markAsRead(string $notificationId, Request $request): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Odczytać wszystkie powiadomienia.
     *
     * Odczytuje wszystkie powiadomienia użytkownika, oznaczając je jako przeczytane. Użytkownik musi być właścicielem powiadomień.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
