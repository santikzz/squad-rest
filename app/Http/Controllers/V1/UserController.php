<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserDetailResource;
use App\Http\Resources\V1\GroupResource;
use App\Http\Resources\V1\JoinRequestResource;
use App\Http\Resources\V1\NotificationResource;
use App\Models\User;
use App\Models\Group;
use App\Models\Carrera;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserGroupJoinRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Laravel\Facades\Image;

class UserController extends Controller
{
    public function index()
    {
        // disable all user listing
        // return new UserCollection(User::paginate());
    }

    public function self(Request $request){
        return response()->json(new UserDetailResource($request->user()));
    }

    // get user data with ulid
    public function show(Request $request, $ulid)
    {
        $user = User::with('carrera.facultad')->where('ulid', $ulid)->first();
        if ($user) {
            return response()->json(new UserDetailResource($user));
            // return response()->json($user);
        } else {
            return response()->json(['error' => ['code' => 'user_not_found', 'message' => 'User not found']], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request)
    {
        // $user = User::where('id', $request->user()->id)->first();
        $user = $request->user();

        try {

            $validatedData = $request->validate([
                'name' => 'string|min:2|max:50',
                'surname' => 'string|min:2|max:50',
                'about' => 'string|max:255',
                // 'password' => 'string|min:8|confirmed',
                'idCarrera' => 'integer|min:1',
            ]);

            if ($request->has('name')) {
                $user->name = $validatedData['name'];
            }
            if ($request->has('surname')) {
                $user->surname = $validatedData['surname'];
            }
            if ($request->has('about')) {
                $user->about = $validatedData['about'];
            }
            if ($request->has('idCarrera')) {
                $idCarrera = $validatedData['idCarrera'];
                $carreraExists = Carrera::where('id', $idCarrera)->exists();
                if (!$carreraExists) {
                    return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'invalid idCarrera.']], Response::HTTP_BAD_REQUEST);
                }
                $user->id_carrera = $idCarrera;
            }

            if ($request->has('old_password', 'password', 'password_confirmation')) {
                if (Hash::check($request->input('old_password'), $user->password)) {
                    $user->password = Hash::make($request->input('password'));
                }
            }

            $user->save();
            return response()->json(new UserResource($user), Response::HTTP_OK);
            // return response()->json(['message' => 'User data modified successfully.'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            // return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'Invalid or malformed parameters for user modification.']], Response::HTTP_BAD_REQUEST);
            return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateAvatar(Request $request)
    {

        $user = $request->user();

        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_image']], Response::HTTP_BAD_REQUEST);
        }

        // $user = $request->user();
        $image = $request->file('avatar');

        /* ==== useful documentation ====
            https://www.itsolutionstuff.com/post/laravel-11-resize-image-before-upload-exampleexample.html
            https://image.intervention.io/v2
            https://medium.com/@laravelprotips/storing-public-and-private-files-images-in-laravel-a-comprehensive-guide-6620789fad3b
        */

        // $filename = $image->hashName();
        // $img = Image::read($image->path());
        // $img->resize(150, 150, function($constraint){
        //     $constraint->aspectRatio();
        // })->save('/avatar/'.$filename);

        // $filename = $request->user()->ulid . "_" . $file->hashName() . "." . $file->extension();

        $userData = User::where('ulid', $user->ulid)->first();
        // if($userData->profile_image != null){
        // remove
        // }

        $filename = $userData->ulid . '.' . $image->getClientOriginalExtension();

        $path = $image->storeAs(
            'public/avatar',
            $filename
            // $image->hashName()
        );

        if ($path) {
            $user = $request->user();
            // $user->profile_image = $path;
            $user->profile_image = 'storage/avatar/' . $filename;
            $user->save();
        }

        return response()->json(['path' => $path], Response::HTTP_OK);
    }

    // only group owner can delete
    public function delete(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully.'], Response::HTTP_OK);
    }

    public function getOwnedGroups(Request $request)
    {
        $user = $request->user();
        $groups = $user->ownedGroups;
        return response()->json(GroupResource::collection($groups));
    }

    public function getJoinedGroups(Request $request)
    {
        $user = $request->user();
        $groups = $user->joinedGroups;
        return response()->json(GroupResource::collection($groups));
    }

    public function getJoinRequests(Request $request)
    {
        $user = $request->user();
        $ownedGroups = $user->ownedGroups()->get();
        $joinRequests = collect(); // empty collection

        // iterate over each owned group and retrieve its join requests
        foreach ($ownedGroups as $group) {
            $joinRequests = $joinRequests->merge($group->joinRequests);
        }
        return response()->json(JoinRequestResource::collection($joinRequests));
    }

    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $ownedGroups = $user->ownedGroups()->get();
        $joinRequests = collect();

        foreach ($ownedGroups as $group) {
            $joinRequests = $joinRequests->merge($group->joinRequests);
        }

        $notifications = Notification::where('user_id', $user->id)->get();

        $totalBadges = 0;
        $totalBadges += $joinRequests->count() + $notifications->count();



        return response()->json([
            "notifications" => NotificationResource::collection($notifications),
            "badges" => $totalBadges,
        ]);
    }

    public function dismissNotification(Request $request, $notifId)
    {
        $user = $request->user();

        $notification = Notification::where('id', $notifId)->first();

        if (!$notification) {
            return response()->json(['error' => 'notification_not_found'], Response::HTTP_NOT_FOUND);
        }
        if ($user->id != $notification->user_id) {
            return response()->json(['error' => 'notification_not_found'], Response::HTTP_NOT_FOUND);
        }
        $notification->delete();
        return response()->json(['status' => 'OK'], Response::HTTP_OK);
    }

    public function submitFeedback(Request $request)
    {
        try {
            $user = $request->user();


            $valid = $request->validate([
                'description' => 'required|string|min:12'
            ]);

            $feedback = new Feedback();
            $feedback->user_id = $user->id;
            $feedback->description = $valid['description'];

            $feedback->save();

            return response()->json(['status' => 'OK'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'One or more parameters are invalid.']], Response::HTTP_BAD_REQUEST);
        }
    }

    public function submitReport(Request $request)
    {
        try {
            $user = $request->user();

            $valid = $request->validate([
                'reportedId' => 'required|string|min:1',
                'description' => 'required|string|min:12'
            ]);

            $reportedUser = User::where('ulid', $valid['reportedId'])->first();
            if (!$reportedUser) {
                return response()->json(['error' => ['code' => 'invalid_user', 'message' => 'Invalid user ID.']], Response::HTTP_BAD_REQUEST);
            }

            $report = new Report();
            $report->reporter_user_id = $user->id;
            $report->reported_user_id = $reportedUser->id;
            $report->description = $valid['description'];

            $report->save();

            return response()->json(['status' => 'OK'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'One or more parameters are invalid.']], Response::HTTP_BAD_REQUEST);
        }
    }


    public function environment(Request $request)
    {

        $user = $request->user();

        // count notification badges
        $ownedGroups = $user->ownedGroups()->get();
        $joinRequests = collect();
        foreach ($ownedGroups as $group) {
            $joinRequests = $joinRequests->merge($group->joinRequests);
        }
        $notifications = Notification::where('user_id', $user->id)->get();
        $totalNotifications = 0;
        $totalNotifications += $joinRequests->count() + $notifications->count();

        // return response()->json([
        //     "notifications" => NotificationResource::collection($notifications),
        //     "badges" => $totalNotifications,
        // ]);

        return response()->json([
            'user' => [
                'ulid' => $user->ulid,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'avatar' => $user->profile_image,
                'avatarFallback' => strtoupper($user->name[0]) . strtoupper($user->surname[0]),
                'carrera' => $user->carrera->name,
                'facultad' => $user->carrera->facultad->name,
            ],
            'notifications' => [
                'badges' => $totalNotifications
            ]
        ], Response::HTTP_OK);
    }
}
