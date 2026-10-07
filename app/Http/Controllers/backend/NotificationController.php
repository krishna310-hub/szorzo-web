<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function getUnread(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->startOfDay();
        $notifications = $user->notifications()->latest()->take(30)->get()->filter(function ($notif) use ($today) {
            $data = $notif->data ?? [];
            if (($data['contractable_type'] ?? '') === 'employee') {
                $empId = $data['contractable_id'] ?? null;
                if ($empId) {
                    $emp = \App\Models\Employee::find($empId);
                    if (! $emp || ! $emp->status || ($emp->relieving_date && \Carbon\Carbon::parse($emp->relieving_date)->lte($today))) {
                        return false;
                    }
                }
            }
            return true;
        })->values()->take(15);

        return response()->json([
            'unread_count' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications,
        ]);
    }
}
