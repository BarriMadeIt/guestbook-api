<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireMessage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ( ! $request->has('message_id')) {
            abort(417, 'Specifying the message is required for this endpoint.');
        }

        $hasMessage = null;
        
        if (Auth::user()->is_admin) {
            $hasMessage = Message::query()
            ->where('id', $request->input('message_id'))
            ->exists();
        } else {
            $hasMessage = Message::query()
            ->where('id', $request->input('message_id'))
            ->where('user_id', Auth::id())
            ->exists();
        }
        
        if ( ! $hasMessage) {
            abort(404, 'Message not found.');
        }

        return $next($request);
    }
}
