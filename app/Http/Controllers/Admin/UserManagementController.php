<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSellerRequest;
use App\Http\Requests\ResellerRequest;
use App\Mail\AccountCreatedMail;
use App\Mail\ThankyouMail;
use App\Models\Contact;
use App\Models\ReSeller;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
                            $btn = '<i class="blockUserButton uil-user-times text-danger fs-3" style="cursor:pointer;" name = "deleteManagerButton" id="'.$row->id.'"></i>';
                        } else {
                            $btn = '<i class="uil-user-check blockUserButton text-primary fs-3" style="cursor:pointer;" name = "deleteManagerButton" id="'.$row->id.'"></i>';
                        }
                        if ($row->reSeller) {
                            if ($row->reSeller->orders->count() > 0) {

                                $view = route('order.resellars', $row->reSeller->uuid);
                                $btn .= ' <a class="ms-1" href="'.$view.'"><i class="mdi mdi-eye fs-3"></i></a>';
                            }
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

            if ($localUser) {

                $links = $localUser->links;

                if ($links->count() > 0) {
                    foreach ($links as $link) {
                        if ($link->profile) {
                            $profile = $link->profile;
                            if ($profile->profile_picture) {
                                $filePathToDelete = public_path('images/profile/profile_pictures/'.$profile->profile_picture);
                                $this->deletePicture($filePathToDelete, 'images/profile/profile_pictures/'.$profile->profile_picture);
                            }
                            if ($profile->cover_picture) {
                                $filePathToDelete = public_path('images/profile/cover_pictures/'.$profile->cover_picture);
                                $this->deletePicture($filePathToDelete, 'images/profile/cover_pictures/'.$profile->cover_picture);
                            }
                            if ($profile->relations) {
                                foreach ($profile->relations as $relation) {
                                    if ($relation->image_name) {
                                        $filePathToDelete = public_path('images/profile/relations/'.$relation->image_name);
                                        $this->deletePicture($filePathToDelete, 'images/profile/relations/'.$relation->image_name);
                                    }
                                }
                                $profile->relations()->delete();
                            }
                        }
                        $link->profile()->delete();

                        $link->update(['local_user_id' => null]);

                        if ($link->photos) {
                            foreach ($link->photos as $photo) {
                                if ($photo->image && $photo->image !== 'youtube_placeholder') {
                                    $filePathToDelete = public_path('images/profile/photos/'.$photo->image);
                                    $this->deletePicture($filePathToDelete, 'images/profile/photos/'.$photo->image);
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
                                    $this->deletePicture($filePathToDelete, 'images/profile/tributes/'.$tribute->image);
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
    private function deletePicture($pictureWithCompletePath, $relativePath = null)
    {
        // Use S3 if configured
        $disk = env('FILESYSTEM_DISK', 'local') === 's3' ? 's3' : 'local';
        
        if ($disk === 's3' && $relativePath) {
            Storage::disk('s3')->delete($relativePath);
        } else {
            if (file_exists($pictureWithCompletePath)) {
                unlink($pictureWithCompletePath);
            }
        }
    }
}
