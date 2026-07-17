<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification as InfoNotification;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
                      -> latest()
                      -> get();

        return view('notifications.index', compact('notifications'));
    }

    public function store(Request $request,$id)
    {
        $notification = Auth::user()
                     -> notifications()
                     -> findOrFail($id);
        //標準の既読処理メソッド
        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }
}
