<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSellerRequest;
use App\Http\Requests\ResellerRequest;
use App\Mail\AccountCreatedMail;
use App\Mail\ResellerInvitationMail;
use App\Mail\ThankyouMail;
use App\Models\Contact;
use App\Models\ReSeller;
use App\Models\ResellerApplication;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    public function list(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = User::role('re-sellers')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at
                            ? '<span data-order="'.$row->created_at->timestamp.'">'.$row->created_at->timezone('America/Chicago')->format('m/d/Y h:i:s A').'</span>'
                            : '-';
                    })
                    ->addColumn('action', function ($row) {
                        if ($row->isBanned == 0) {
                            $btn = '<i class="blockUserButton uil-user-times text-danger fs-3" style="cursor:pointer;" name = "deleteManagerButton" id="'.$row->id.'" title="Ban user"></i>';
                        } else {
                            $btn = '<i class="uil-user-check blockUserButton text-primary fs-3" style="cursor:pointer;" name = "deleteManagerButton" id="'.$row->id.'" title="Unban user"></i>';
                        }
                        if ($row->reSeller) {
                            if ($row->reSeller->orders->count() > 0) {
                                $view = route('order.resellars', $row->reSeller->uuid);
                                $btn .= ' <a class="ms-1" href="'.$view.'" title="View orders"><i class="mdi mdi-eye fs-3"></i></a>';
                            }
                            $btn .= ' <i class="deleteUserButton uil-trash-alt text-danger fs-3 ms-1" style="cursor:pointer;" id="'.$row->id.'" title="Delete reseller"></i>';
                            return $btn;
                        }
                        return $btn;
                    })
                    ->addColumn('status', function ($row) {
                        if ($row->isBanned == 0) {
                            $btn = '<span class="badge bg-success">Active</span>';
                        } else {
                            $btn = '<span class="badge bg-danger">Banned</span>';
                        }

                        return $btn;
                    })
                    ->addColumn('role', function ($row) {
                        if ($row->hasRole('local_user')) {
                            return "Local User";
                        }
                        return "Re-Sellar";
                    })

                    ->rawColumns(['action', 'role', 'status', 'created_at'])
                    ->make(true);
            }
            return view('admin.pages.userList');
        } catch (\Throwable $th) {
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'something went wrong']);
        }
    }
    public function localList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = User::role('local_user')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at
                            ? '<span data-order="'.$row->created_at->timestamp.'">'.$row->created_at->timezone('America/Chicago')->format('m/d/Y h:i:s A').'</span>'
                            : '-';
                    })
                    ->addColumn('action', function ($row) {
                        if ($row->isBanned == 0) {
                            $btn = '<i title="Ban User" class="blockUserButton uil-user-times text-danger fs-3" style="cursor:pointer;" name="deleteManagerButton" id="'.$row->id.'"></i>';
                        } else {
                            $btn = '<i title="Unban User" class="uil-user-check blockUserButton text-primary fs-3" style="cursor:pointer;" name="deleteManagerButton" id="'.$row->id.'"></i>';
                        }
                        if ($row->localUser) {
                            // dd($row->localUser);
                            $qrCodes = route('link.by.user', $row->localUser->uuid);
                            $btn .= ' <a title="View QR Codes" class="ms-1" href="'.$qrCodes.'"><i class="mdi mdi-eye fs-3"></i></a>';
                            $btn .= ' <i title="Delete user" class="uil-trash-alt deleteUserButton text-primary fs-3" style="cursor:pointer;"  id="'.$row->id.'"></i>';
                        }
                        return $btn;
                    })
                    ->addColumn('status', function ($row) {
                        if ($row->isBanned == 0) {
                            return '<span class="badge bg-success">Active</span>';
                        } else {
                            return '<span class="badge bg-danger">Banned</span>';
                        }
                    })
                    ->addColumn('role', function ($row) {
                        if ($row->hasRole('local_user')) {
                            return "Local User";
                        }
                        return "Re-Sellar";
                    })
                    ->rawColumns(['action', 'role', 'status', 'created_at'])
                    ->make(true);
            }
            return view('admin.pages.localUserList');
        } catch (\Throwable $th) {
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'something went wrong']);
        }
    }
    public function banUser($id)
    {
        try {
            DB::beginTransaction();
            $user = User::findorfail($id);

            $user->update([
                'id' => $user->id,
                'isBanned' => $user->isBanned ? 0 : 1,
            ]);
            DB::commit();
            return response()->json(['status' => true, 'message' => 'User ban status toggled successfully']);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
        }
    }
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findorfail($id);
            $localUser = $user->localUser;
            $reSeller = $user->reSeller;

            if ($reSeller) {
                $user->removeRole('re-sellers');
                $reSeller->delete();
                $user->delete();
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Reseller deleted successfully']);
            }

            if ($localUser) {

                $links = $localUser->links;

                if ($links->count() > 0) {
                    foreach ($links as $link) {
                        if ($link->profile) {
                            $profile = $link->profile;
                            if ($profile->profile_picture) {
                                $filePathToDelete = public_path('images/profile/profile_pictures/'.$profile->profile_picture);
                                $this->deletePicture($filePathToDelete);
                            }
                            if ($profile->cover_picture) {
                                $filePathToDelete = public_path('images/profile/cover_pictures/'.$profile->cover_picture);
                                $this->deletePicture($filePathToDelete);
                            }
                            if ($profile->relations) {
                                foreach ($profile->relations as $relation) {
                                    if ($relation->image_name) {
                                        $filePathToDelete = public_path('images/profile/relations/'.$relation->image_name);
                                        $this->deletePicture($filePathToDelete);
                                    }
                                }
                                $profile->relations()->delete();
                            }
                        }
                        $link->profile()->delete();

                        $link->update(['local_user_id' => null]);

                        if ($link->photos) {
                            foreach ($link->photos as $photo) {
                                if ($photo->image) {
                                    $filePathToDelete = public_path('images/profile/photos/'.$photo->image);
                                    $this->deletePicture($filePathToDelete);
                                }
                            }
                            $link->photos()->delete();
                        }
                        if ($link->timelines) {
                            $link->timelines()->delete();
                        }

                        if ($link->tributes) {
                            foreach ($link->tributes as $tribute) {
                                if ($tribute->image) {
                                    $filePathToDelete = public_path('images/profile/tributes/'.$tribute->image);
                                    $this->deletePicture($filePathToDelete);
                                }
                            }
                            $link->tributes()->delete();
                        }
                    }
                }
                $localUser->delete();
            }
            $user->delete();
            DB::commit();
            return response()->json(['status' => true, 'message' => 'User Deleted Successfully']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function create_reSeller(CreateSellerRequest $request)
    {
        try {
            DB::beginTransaction();
            $password = Str::random(10);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $resellerRequest = Contact::where('email', $user->email)->first();
            if ($resellerRequest) {
                $resellerRequest->delete();
            }
            $user->assignRole('re-sellers');
            ReSeller::create([
                'uuid' => Str::uuid(),
                'phone' => $request->phone,
                'website' => $request->website,
                'shipping_address' => $request->address,
                'user_id' => $user->id,
            ]);
            DB::commit();
            $data = [
                'name' => $user->name,
                'email' => $request->email,
                'password' => $password,
            ];

            Mail::to($user->email)->send(new AccountCreatedMail($data));
            return response()->json([
                'status' => true,
                'message' => 'Re-Seller Created Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                dd($th->getMessage()),
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function re_seller_request(ResellerRequest $request)
    {
        try {
            DB::beginTransaction();
            $contact = Contact::where('email', $request->email)->first();
            if ($contact) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are already applied for reseller account',
                ]);
            } else {


                Contact::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'website' => $request->website,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'message' => $request->business,
                    'address' => $request->address,

                ]);
                DB::commit();
                $data = [
                    'userName' => $request->first_name,
                ];
                Mail::to($request->email)->send(new ThankyouMail($data));
                return response()->json([
                    'status' => true,
                    'message' => 'Thank you for your interested in Living
                                 Legacy, we will review your request and be in touch shortly',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function resellerApplications(Request $request)
    {
        try {
            $applications = ResellerApplication::orderBy('created_at', 'desc')->get();
            $pendingCount = ResellerApplication::where('status', ResellerApplication::STATUS_PENDING)->count();
            return view('admin.pages.resellerApplications', compact('applications', 'pendingCount'));
        } catch (\Throwable $th) {
            return redirect(route('admin.batches'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }

    public function resellerApplicationDetail($id)
    {
        try {
            $app = ResellerApplication::findOrFail($id);
            return response()->json($app);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Not found'], 404);
        }
    }

    public function approveResellerApplication($id)
    {
        try {
            DB::beginTransaction();
            $app = ResellerApplication::findOrFail($id);

            if ($app->status !== ResellerApplication::STATUS_PENDING) {
                return response()->json([
                    'status' => false,
                    'message' => 'This application has already been processed.',
                ]);
            }

            $existingUser = User::where('email', $app->email)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'A user with this email already exists.',
                ]);
            }

            $password = Str::random(10);
            $user = User::create([
                'name' => $app->full_name,
                'email' => $app->email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('re-sellers');

            $shippingAddress = trim(implode(', ', array_filter([
                $app->street_address,
                $app->city,
                trim(implode(' ', array_filter([$app->state, $app->zip_code]))),
            ])));

            ReSeller::create([
                'uuid' => Str::uuid(),
                'phone' => $app->phone ?? $app->business_phone ?? '',
                'website' => $app->website ?? '',
                'shipping_address' => $shippingAddress ?: 'N/A',
                'street_address' => $app->street_address ?: null,
                'city' => $app->city ?: null,
                'state' => $app->state ?: null,
                'postal_code' => $app->zip_code ?: null,
                'user_id' => $user->id,
            ]);

            $activationToken = Str::random(64);
            $app->update([
                'status' => ResellerApplication::STATUS_APPROVED,
                'activation_token' => $activationToken,
                'activation_token_expires_at' => now()->addDays(7),
            ]);

            $loginLink = url('/reseller-login?token=' . $activationToken);
            try {
                Mail::to($user->email)->send(new ResellerInvitationMail([
                    'name' => $user->name,
                    'business_name' => $app->business_name,
                    'loginLink' => $loginLink,
                ]));
                \Log::info('Reseller invitation email sent', ['email' => $user->email]);
            } catch (\Throwable $mailError) {
                \Log::error('Reseller invitation email failed', [
                    'email' => $user->email,
                    'error' => $mailError->getMessage(),
                    'trace' => $mailError->getTraceAsString(),
                ]);
                // Account is created; user can use password reset if email fails
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Reseller account created successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Approve reseller application error: ' . $th->getMessage(), [
                'exception' => $th,
                'trace' => $th->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
            ]);
        }
    }

    public function rejectResellerApplication($id)
    {
        try {
            $app = ResellerApplication::findOrFail($id);

            if ($app->status !== ResellerApplication::STATUS_PENDING) {
                return response()->json([
                    'status' => false,
                    'message' => 'This application has already been processed.',
                ]);
            }

            $app->update(['status' => ResellerApplication::STATUS_REJECTED]);

            return response()->json([
                'status' => true,
                'message' => 'Application rejected.',
            ]);
        } catch (\Throwable $th) {
            \Log::error('Reject reseller application error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
            ]);
        }
    }

    public function deleteResellerApplication($id)
    {
        try {
            $app = ResellerApplication::findOrFail($id);
            $app->delete();

            return response()->json([
                'status' => true,
                'message' => 'Application deleted successfully.',
            ]);
        } catch (\Throwable $th) {
            \Log::error('Delete reseller application error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
            ]);
        }
    }

    private function deletePicture($pictureWithCompletePath)
    {
        if (file_exists($pictureWithCompletePath)) {
            unlink($pictureWithCompletePath);
        }
    }
}
