<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyValidation;
use App\Http\Resources\ReplyResource;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    /**
     * Store a new message reply
     *
     * @param  Illuminate\Http\Request $request
     * @return array
     */
    public function store(ReplyValidation $request)
    {
        $reply = Reply::create(
            array_merge($request->validated(), [
                'user_id' => Auth::id(),
            ])
        );

        return [
            'reply_id' => $reply->id,
        ];
    }

    /**
     * Fetch a list of replies
     *
     * @param  Illuminate\Http\Request $request
     * @return array
     */
    public function list(Request $request)
    {   
        $replies = Reply::where('message_id', $request->input('message_id'))->get();

        return [
            'replies' => ReplyResource::collection($replies),
        ];
    }

    /**
     * Update message reply
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $replyId
     * @return array
     */
    public function update(ReplyValidation $request, int $replyId)
    {
        $messageId = $request->input('message_id');

        $reply = Reply::where('message_id', $messageId)
            ->where('id', $replyId)
            ->update($request->validated());
        
        if ( ! $reply) {
            abort(404, 'Message reply not found');
        }

        return [
            'reply_updated' => $reply,
        ];
    }

    /**
     * Archive a message reply
     *
     * @param  int $replyId
     * @return array
     */
    public function archive(Request $request, int $replyId)
    {
        $reply = Reply::where('message_id', $request->input('message_id'))
            ->where('id', $replyId)
            ->delete();
        
        if ( ! $reply) {
            abort(404, 'Message reply not found');
        }

        return [
            'reply_deleted' => $reply,
        ];
    }
}
