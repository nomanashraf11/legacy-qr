<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\LoginResource;
use App\Http\Resources\RegisterResource;
use App\Mail\LinkQrCodeMail;
use App\Models\Link;
use App\Models\LocalUser;
use Illuminate\Support\Facades\Mail;
use Google_Client;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users'),
                ],
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }

            $link = Link::where('uuid', $request->qr_code)->first();

            if (! $link) {
                return response()->json([
                    'status' => 404,
                    'message' => 'QR Code not found',
                ]);
            }

            if ($link->local_user_id) {
                return response()->json([
                    'status' => 409,
                    'message' => 'QR Code is already linked. Use another one.',
                ]);
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            $localUser = LocalUser::create([
                'uuid' => Str::uuid(),
                'phone' => $request->input('phone'),
                'user_id' => $user->id,
            ]);

            $link->update(['local_user_id' => $localUser->id]);
            $user->assignRole('local_user');

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Registered successfully',
                'data' => new RegisterResource($user),
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => $th->getMessage(), // Optional: Include error details for debugging
            ], 500);
        }
    }


    public function loginUser(Request $request, User $user)
    {
        try {

            $user = User::where('email', $request->input('email'))->first();

            if (! $user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Email or password incorrect',
                ]);
            }

            if ($user->google_id && $user->password === null) {
                return response()->json([
                    'status' => 401,
                    'message' => 'This email is linked to a Google account. Please log in with your Google Account.',
                ]);
            }

            if (! Hash::check($request->input('password'), $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Email or password incorrect',
                ]);
            }

            // Check if user has the required role
            if (! $user->hasRole('local_user')) {
                return response()->json([
                    'status' => 204,
                    'message' => 'You cannot login as admin user',
                ]);
            }

            // Check if user is banned
            if ($user->isBanned) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Your account is banned. Contact your admin.',
                ]);
            }

            // Check if email is verified
            if ($user->email_verified_at === null) {
                return response()->json([
                    'status' => 201,
                    'message' => 'Verify your email first.',
                ]);
            }

            // Process QR code if provided
            if ($request->qr_code) {
                $qrCodeResponse = $this->handleQrCode($request->qr_code, $user);
                if ($qrCodeResponse) {
                    return $qrCodeResponse;
                }
            }

            // Generate and return JWT token
            $user['jwt'] = $user->createToken('Api Token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'message' => 'Logged in successfully.',
                'data' => new LoginResource($user),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Please try again later.',
            ]);
        }
    }

    private function handleQrCode(string $qrCode, User $user)
    {
        $link = Link::where('uuid', $qrCode)->first();

        if (! $link) {
            return null;
        }

        $localUser = $user->localUser;

        if ($link->local_user_id) {

            if ($link->local_user_id != $localUser->id) {
                return response()->json([
                    'status' => 400,
                    'message' => 'QR already linked. Use another one.',
                ]);
            }

            $user['jwt'] = $user->createToken('Api Token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'message' => 'Logged in successfully.',
                'data' => new LoginResource($user),
            ]);
        }

        $link->update(['local_user_id' => $localUser->id]);

        Mail::to($user->email)->send(new LinkQrCodeMail([
            'userName' => $user->name,
            'qr_code' => $qrCode,
        ]));

        return null;
    }

    public function handleGoogleLogin(Request $request)
    {
        $idToken = $request->idToken;
        $qrCode = $request->qrCode;

        if (! $idToken) {
            return response()->json(['message' => 'idToken is required', 'status' => 400]);
        }

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        $payload = $client->verifyIdToken($idToken);

        if (! $payload) {
            return response()->json(['message' => 'Invalid payload', 'status' => 400]);
        }

        DB::beginTransaction();
        try {
            $user = User::firstOrNew(['email' => $payload['email']]);
            $isNewUser = ! $user->exists;

            if ($isNewUser) {
                if (! $qrCode) {
                    return response()->json(['message' => 'QR Code is required.', 'status' => 400]);
                }

                $link = Link::where('uuid', $qrCode)->first();

                if (! $link) {
                    return response()->json(['status' => 404, 'message' => 'QR Code not found.']);
                }

                if ($link->local_user_id) {
                    return response()->json(['status' => 409, 'message' => 'QR Code is already linked.']);
                }

                $user->fill([
                    'name' => $payload['name'],
                    'google_id' => $payload['sub'],
                    'email_verified_at' => now(),
                ])->save();

                LocalUser::create([
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                ]);

                $user->assignRole('local_user');
                $link->update(['local_user_id' => $user->localUser->id]);
            } else {
                if ($user->email_verified_at === null) {
                    $user->update(['email_verified_at' => now()]);
                }

                $user->update(['google_id' => $payload['sub']]);

                if ($qrCode) {
                    $this->handleQrCode($qrCode, $user);
                }
            }

            DB::commit();

            // Generate and return JWT token
            $user['jwt'] = $user->createToken('Api Token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'message' => 'Logged in successfully.',
                'data' => new LoginResource($user),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Unable to authenticate with google.', 'status' => 400]);
        }
    }

    public function logout()
    {
        try {
            if (Auth::user()) {
                $user = Auth::user();
                $user->currentAccessToken()->delete();
                return response()->json(['status' => 204, 'message' => 'Logged out Successfully.']);
            } else {
                return response()->json(['status' => 401, 'message' => 'Un-authorized']);
            }
        } catch (\Throwable $e) {
            return response()->json(['status' => 500, 'message' => 'something went wrong']);
        }
    }
    public function changePassword(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'id' => $user->id,
                    'password' => Hash::make($request->newPassword),
                ]);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Incorrect old password'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error'
            ]);
        }
    }
}


