<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Group;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\MessageCollection;

class MessageController extends Controller
{

    public function getMessages(Request $request, $ulid)
    {
        // $group = Group::with('messages')->where('ulid', $ulid)->first();

        // if (!$group) {
        //     return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        // }

        // $isMember = DB::table('user_group')->where('user_id', $user->id)->where('group_id', $group->id)->exists();
        // if (!$isMember) {
        //     return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        // }

        // return response()->json($group);

        $user = $request->user();
        $group = Group::where('ulid', $ulid)->first();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        $isMember = DB::table('user_group')->where('user_id', $user->id)->where('group_id', $group->id)->exists();
        if (!$isMember) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        $messages = Message::where('group_id', $group->id)->get();

        return response()->json(new MessageCollection($messages));
    }

    public function sendMessage(Request $request, $ulid)
    {
        try {

            $user = $request->user();
            $validated = $request->validate([
                'message' => 'required|string',
            ]);

            $group = Group::where('ulid', $ulid)->first();

            if (!$group) {
                return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
            }

            $isMember = DB::table('user_group')->where('user_id', $user->id)->where('group_id', $group->id)->exists();
            if (!$isMember) {
                return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
            }

            $message = new Message();
            $message->group_id = $group->id;
            $message->user_id = $user->id;
            $message->message = $validated['message'];
            $message->save();

            return response(new MessageResource($message), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
        }
    }
}
