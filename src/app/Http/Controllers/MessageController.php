<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageValidation;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Store a new message
     *
     * @param  Illuminate\Http\Request $request
     * @return array
     */
    public function store(MessageValidation $request)
    {
        $message = Message::create(
            array_merge($request->validated(), [
                'user_id' => Auth::id(),
            ])
        );

        return [
            'message_id' => $message->id,
        ];
    }

    /**
     * List a user's messages
     *
     * @return array
     */
    public function list()
    {
        $messages = Message::where('user_id', Auth::id())->get();

        return [
            'messages' => MessageResource::collection($messages),
        ];
    }

    /**
     * List all messages
     *
     * @return array
     */
    public function listAll()
    {
        $messages = Message::all();

        return [
            'messages' => MessageResource::collection($messages),
        ];
    }

    /**
     * Show a user's message
     *
     * @param  int $id
     * @return array
     */
    public function show($id)
    {
        $message = Message::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        
        if ( ! $message) {
            abort(404, 'Message not found');
        }

        return [
            'message' => new MessageResource($message),
        ];
    }

    /**
     * Update a user's message
     *
     * @param  Illuminate\Http\Request $request
     * @param int $id
     * @return array
     */
    public function update(MessageValidation $request, $id)
    {
        $message = Message::where('id', $id)
            ->where('user_id', Auth::id())
            ->update($request->validated());

        if ( ! $message) {
            abort(404, 'Message not found');
        }

        return [
            'message_updated' => $message,
        ];
    }

    /**
     * Archive a user's message
     *
     * @param  int $id
     * @return array
     */
    public function archive($id)
    {
        $message = Message::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();
        
        if ( ! $message) {
            abort(404, 'Message not found');
        }

        return [
            'message_deleted' => $message,
        ];
    }
}
