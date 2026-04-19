<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Http\Resources\MyQrCodesResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\TimelineResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Link;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\Relation;
use App\Models\Timeline;
use App\Models\Tribute;
use App\Models\User;
use App\Support\TabVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Drivers\Imagick\Driver;
// use Intervention\Image\ImageManager;
use Intervention\Image\Image;

// FFMpeg imports removed - no longer needed

class ProfileController extends Controller
{
    public function addBio($uuid, Request $request)
    {
        $request->merge([
            'dark_theme' => filter_var($request->dark_theme, FILTER_VALIDATE_BOOLEAN)
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
            'title' => 'nullable|string|max:255',
            'dob' => 'required|date',
            'dod' => 'nullable|date',
            'profile_picture' => 'nullable|image|mimes:png,jpg|max:209715200', // 200MB limit
            'cover_picture' => 'nullable|image|mimes:png,jpg|max:209715200', // 200MB limit
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'spouse_facebook' => 'nullable|url',
            'spouse_instagram' => 'nullable|url',
            'spouse_twitter' => 'nullable|url',
            'spotify' => 'nullable|url',
            'youtube' => 'nullable|url',
            'bio' => 'nullable|string',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'badge' => 'nullable|string',
            'spouse_badge' => 'nullable|string',
            'dark_theme' => 'nullable|boolean',
            'remove_profile_picture' => 'nullable|boolean',
            'remove_cover_picture' => 'nullable|boolean',
            'stock_cover' => 'nullable|string|max:128',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => '422',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        }
        try {
            DB::beginTransaction();
            $link = Link::where('uuid', $uuid)->firstorfail();
            $profilePictureName = null;
            $coverPictureName = null;
            if (Auth::user()->localUser->id == $link->local_user_id) {

                if ($link->profile) {
                    $profilePictureName = $link->profile->profile_picture ?: '';
                    $coverPictureName = $link->profile->cover_picture ?: '';
                    $previousProfileFile = $profilePictureName;
                    $previousCoverFile = $coverPictureName;

                    if ($request->hasFile('profile_picture')) {
                        $this->deleteStoredProfileFile($previousProfileFile);
                        $profilePicture = $request->file('profile_picture');
                        $profilePictureName = time().'_'.$profilePicture->getClientOriginalName();
                        try {
                            $disk = config('filesystems.default');
                            $path = Storage::disk($disk)->putFileAs('images/profile/profile_pictures', $profilePicture, $profilePictureName);
                            \Log::info('Profile picture uploaded to '.$disk, ['path' => $path]);
                        } catch (\Exception $e) {
                            \Log::error('S3 upload failed, using local storage', [
                                'error' => $e->getMessage(),
                                'disk' => config('filesystems.default'),
                            ]);
                            $localDir = public_path('images/profile/profile_pictures');
                            if (! file_exists($localDir)) {
                                mkdir($localDir, 0777, true);
                            }
                            $profilePicture->move($localDir, $profilePictureName);
                        }
                    } elseif ($request->boolean('remove_profile_picture')) {
                        $this->deleteStoredProfileFile($previousProfileFile);
                        $profilePictureName = '';
                    }

                    if ($request->hasFile('cover_picture')) {
                        $this->deleteStoredCoverFile($previousCoverFile);
                        $coverPicture = $request->file('cover_picture');
                        $coverPictureName = time().'_'.$coverPicture->getClientOriginalName();
                        try {
                            $disk = config('filesystems.default');
                            $path = Storage::disk($disk)->putFileAs('images/profile/cover_pictures', $coverPicture, $coverPictureName);
                            \Log::info('Cover picture uploaded to '.$disk, ['path' => $path]);
                        } catch (\Exception $e) {
                            \Log::error('S3 upload failed, using local storage', [
                                'error' => $e->getMessage(),
                                'disk' => config('filesystems.default'),
                            ]);
                            $localDir = public_path('images/profile/cover_pictures');
                            if (! file_exists($localDir)) {
                                mkdir($localDir, 0777, true);
                            }
                            $coverPicture->move($localDir, $coverPictureName);
                        }
                    } elseif ($request->filled('stock_cover')) {
                        $pick = basename((string) $request->input('stock_cover'));
                        $allowed = config('living_legacy.stock_covers', []);
                        if (in_array($pick, $allowed, true)) {
                            $this->deleteStoredCoverFile($previousCoverFile);
                            $coverPictureName = 'stock/'.$pick;
                        }
                    } elseif ($request->boolean('remove_cover_picture')) {
                        $this->deleteStoredCoverFile($previousCoverFile);
                        $coverPictureName = '';
                    }

                    $profileUpdate = [
                        'name' => $request->name,
                        'title' => $request->title,
                        'dob' => $request->dob,
                        'dod' => $request->dod,
                        'profile_picture' => $profilePictureName,
                        'cover_picture' => $coverPictureName,
                        'facebook' => $request->facebook,
                        'instagram' => $request->instagram,
                        'twitter' => $request->twitter,
                        'spouse_facebook' => $request->spouse_facebook,
                        'spouse_instagram' => $request->spouse_instagram,
                        'spouse_twitter' => $request->spouse_twitter,
                        'youtube' => $request->youtube,
                        'spotify' => $request->spotify,
                        'bio' => $request->bio,
                        'link_id' => $link->id,
                        'badge' => $request->badge ?? '',
                        'spouse_badge' => $request->spouse_badge ?? '',
                        'dark_theme' => $request->dark_theme ?? $link->profile->dark_theme
                    ];
                    // Only touch map fields when the client sends them (full legacy form sends both;
                    // partial updates e.g. theme toggle must not wipe coordinates).
                    if ($request->has('latitude') || $request->has('longitude')) {
                        $profileUpdate['latitude'] = $request->filled('latitude') ? $request->latitude : null;
                        $profileUpdate['longitude'] = $request->filled('longitude') ? $request->longitude : null;
                    }

                    $link->profile->update($profileUpdate);

                    $relations = $request->input('relations');

                    if ($relations) {
                        $relations = json_decode($relations, true);
                        Relation::where('profile_id', $link->profile->id)->delete();
                        foreach ($relations as $relation) {
                            Relation::create([
                                'uuid' => $relation['uuid'],
                                'name' => $relation['name'],
                                'person_name' => $relation['person_name'],
                                'profile_id' => $link->profile->id,
                                'image' => $relation['image'] ?? '',
                                'image_name' => $relation['image_name'] ?? '',
                                'dob' => $relation['dob'] ?? null,
                                'dod' => $relation['dod'] ?? null,
                                'bio' => $relation['bio'] ?? null,
                                'relation_id' => $relation['relation_id'] ?? null,
                                'is_legacy' => $relation['is_legacy'] ?? false
                            ]);
                        }
                    }
                    DB::commit();
                    return response()->json([
                        'status' => 200,
                        'message' => 'Bio Updated Successfully',
                        'data' => new ProfileResource($link->profile),
                    ]);
                } else {

                    $profilePictureName = '';
                    $coverPictureName = '';

                    if ($request->hasFile('profile_picture')) {
                        $profilePicture = $request->file('profile_picture');
                        $profilePictureName = time().'_'.$profilePicture->getClientOriginalName();
                        try {
                            $disk = config('filesystems.default');
                            $path = Storage::disk($disk)->putFileAs('images/profile/profile_pictures', $profilePicture, $profilePictureName);
                            \Log::info('Profile picture uploaded to '.$disk, ['path' => $path]);
                        } catch (\Exception $e) {
                            \Log::error('S3 upload failed, using local storage', [
                                'error' => $e->getMessage(),
                                'disk' => config('filesystems.default'),
                            ]);
                            $localDir = public_path('images/profile/profile_pictures');
                            if (! file_exists($localDir)) {
                                mkdir($localDir, 0777, true);
                            }
                            $profilePicture->move($localDir, $profilePictureName);
                        }
                    } elseif ($request->boolean('remove_profile_picture')) {
                        $profilePictureName = '';
                    }

                    if ($request->hasFile('cover_picture')) {
                        $coverPicture = $request->file('cover_picture');
                        $coverPictureName = time().'_'.$coverPicture->getClientOriginalName();
                        try {
                            $disk = config('filesystems.default');
                            $path = Storage::disk($disk)->putFileAs('images/profile/cover_pictures', $coverPicture, $coverPictureName);
                            \Log::info('Cover picture uploaded to '.$disk, ['path' => $path]);
                        } catch (\Exception $e) {
                            \Log::error('S3 upload failed, using local storage', [
                                'error' => $e->getMessage(),
                                'disk' => config('filesystems.default'),
                            ]);
                            $localDir = public_path('images/profile/cover_pictures');
                            if (! file_exists($localDir)) {
                                mkdir($localDir, 0777, true);
                            }
                            $coverPicture->move($localDir, $coverPictureName);
                        }
                    } elseif ($request->filled('stock_cover')) {
                        $pick = basename((string) $request->input('stock_cover'));
                        if (in_array($pick, config('living_legacy.stock_covers', []), true)) {
                            $coverPictureName = 'stock/'.$pick;
                        }
                    } elseif ($request->boolean('remove_cover_picture')) {
                        $coverPictureName = '';
                    }

                    $profile = Profile::create([
                        'uuid' => Str::uuid(),
                        'name' => $request->name,
                        'title' => $request->title,
                        'dob' => $request->dob,
                        'dod' => $request->dod,
                        'profile_picture' => $profilePictureName,
                        'cover_picture' => $coverPictureName,
                        'facebook' => $request->facebook,
                        'instagram' => $request->instagram,
                        'twitter' => $request->twitter,
                        'spouse_facebook' => $request->spouse_facebook,
                        'spouse_instagram' => $request->spouse_instagram,
                        'spouse_twitter' => $request->spouse_twitter,
                        'youtube' => $request->youtube,
                        'bio' => $request->bio,
                        'longitude' => $request->filled('longitude') ? $request->longitude : null,
                        'latitude' => $request->filled('latitude') ? $request->latitude : null,
                        'link_id' => $link->id,
                        'spotify' => $request->spotify,
                        'badge' => $request->badge ?? '',
                        'spouse_badge' => $request->spouse_badge ?? '',
                        'dark_theme' => $request->dark_theme ?? true
                    ]);

                    $relations = $request->input('relations');

                    if ($relations) {
                        $relations = json_decode($relations, true);
                        foreach ($relations as $relation) {
                            Relation::create([
                                'uuid' => $relation['uuid'],
                                'name' => $relation['name'],
                                'person_name' => $relation['person_name'],
                                'profile_id' => $profile->id,
                                'image' => $relation['image'] ?? '',
                                'image_name' => $relation['image_name'] ?? '',
                                'dob' => $relation['dob'] ?? null,
                                'dod' => $relation['dod'] ?? null,
                                'bio' => $relation['bio'] ?? null,
                                'relation_id' => $relation['relation_id'] ?? null,
                                'is_legacy' => $relation['is_legacy'] ?? false
                            ]);
                        }
                    }
                    DB::commit();
                    return response()->json([
                        'status' => 200,
                        'message' => 'Bio Updated Successfully',
                        'data' => new ProfileResource($profile),
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorize for this action',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error('addBio error: ' . $th->getMessage(), [
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500)->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        }
    }

    public function uploadRelationPhoto(Request $request)
    {
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $imageName = time().'_'.Str::random(10).'.'.$extension;
                Storage::disk(config('filesystems.default'))->putFileAs('images/profile/relations', $image, $imageName);
                



                // if (strpos($image->getMimeType(), 'image') !== false) {
                //     // Compress and store the image
                //     $compressedImage = $this->compress_image($image, 'images/profile/relations/');
                //     if ($compressedImage === "not-an-image") {
                //         return response()->json([
                //             'status' => 422,
                //             'message' => 'Uploaded file is not an image.',
                //         ]);
                //     }
                //     $imageName = $compressedImage;
                // }
                // Storage::disk('public')->putFileAs('images/profile/relations', $image, $imageName);


                return response()->json([
                    'status' => 200,
                    'name' => $imageName,
                    'data' => asset('images/profile/relations/'.$imageName),
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'data' => 'Something went wrong',
            ]);
        }
    }
    public function deleteRelationPhoto(Request $request)
    {
        try {
            DB::beginTransaction();
            $relation = Relation::where('image_name', $request->name)->first();
            $filePathToDeleteLayer = public_path('images/profile/relations/'.$request->name);
            if (file_exists($filePathToDeleteLayer)) {
                unlink($filePathToDeleteLayer);
                if ($relation) {
                    $relation->update([
                        'id' => $relation->id,
                        'image' => null,
                        'image_name' => null,
                    ]);
                }
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Photo Deleted Successfully'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 500,
                    'message' => 'Photo Not found'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function addPhotos($uuid, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required_without:link|file|mimes:jpeg,jpg,png,gif,mp4,mov|max:314572800', // 300MB limit
                'link' => 'required_without:image|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => '422',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }

            // Check photo count limit (only for Christmas profiles - 20 photos max)
            $link = Link::where('uuid', $uuid)->firstOrFail();
            
            // Only apply limits for Christmas profiles
            if ($link->version_type === 'christmas') {
                // Only count actual photo files, not YouTube links
                $currentPhotoCount = Photo::where('link_id', $link->id)
                    ->whereNotNull('image')
                    ->where('image', '!=', 'youtube_placeholder')
                    ->count();
                
                if ($currentPhotoCount >= 20) {
                    return response()->json([
                        'status' => 422,
                        'message' => "Maximum 20 photos allowed per Christmas profile"
                    ]);
                }
            }

            // Video duration check removed - only file size limit applies (300MB max)

            DB::beginTransaction();
            $imageName = null;

            // if ($request->hasFile('image')) {
            //     $image = $request->file('image');
            //     $extension = $image->getClientOriginalExtension();
            //     $imageName = time() . '_' . Str::random(10) . '.' . $extension;

            //     if (strpos($image->getMimeType(), 'image') !== false) {
            //         // Compress and store the image
            //         $compressedImage = $this->compress_image($image, 'images/profile/photos/');
            //         if ($compressedImage === "not-an-image") {
            //             return response()->json([
            //                 'status' => 422,
            //                 'message' => 'Uploaded file is not an image.',
            //             ]);
            //         }
            //         $imageName = $compressedImage;
            //     } else {
            //         //                    Storage::disk('public')->putFileAs('images/profile/photos', $image, $imageName);
            //         $imageName = time() . '_' . Str::random(10) . '.mp4';
            //         $filePath = public_path('images/profile/photos/') . $imageName;
            //         $this->testvideo($image, $filePath);
            //     }
            // }
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $imageName = time().'_'.Str::random(10).'.'.$extension;

                if (strpos($image->getMimeType(), 'image') !== false) {
                    $path = Storage::disk(config('filesystems.default'))->putFileAs('images/profile/photos', $image, $imageName);
                    
                } else {
                    $imageName = time().'_'.Str::random(10).'.mp4';
                    $path = Storage::disk(config('filesystems.default'))->putFileAs('images/profile/photos', $image, $imageName);
                    
                }
            }


            Photo::create([
                'uuid' => Str::uuid(),
                'image' => $imageName ?? 'youtube_placeholder', // Use placeholder for YouTube links
                'caption' => $request->caption,
                'link_id' => $link->id,
                'link' => $request->link
            ]);

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Photo added successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ]);
        }
    }
    // testvideo method removed - no longer needed
    private function compress_image($image, $target)
    {
        try {
            // Set the maximum file size after compression (in bytes)
            $max_size = 500000; // Adjust as needed

            // Extract image properties
            // $filename = $image->getClientOriginalName();
            $extension = $image->getClientOriginalExtension();
            $filename = time().'_'.Str::random(10).'.'.$extension;
            $file_tmp = $image->getRealPath();
            $file2_img_size = $image->getSize();
            $extension = $image->getClientOriginalExtension();

            if (strpos($image->getMimeType(), 'image') !== false) {
                // Create a new image from the original file
                if ($extension === 'png') {
                    $original_image = imagecreatefrompng($file_tmp);
                } else {
                    $original_image = imagecreatefromjpeg($file_tmp);
                }

                // Save the original image dimensions
                $width = imagesx($original_image);
                $height = imagesy($original_image);

                // If the file size is already within the limit, no compression is needed
                if ($file2_img_size <= $max_size) {
                    // Save the original image as is
                    move_uploaded_file($file_tmp, public_path($target.$filename));
                    return $filename;
                }

                $compression_ratio = sqrt($file2_img_size / $max_size);

                $new_width = round($width / $compression_ratio);
                $new_height = round($height / $compression_ratio);

                $new_image = imagecreatetruecolor($new_width, $new_height);

                imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                $timestamp = time();
                $file = $timestamp.'-'.$filename;
                $compressed_image_path = public_path($target.$file);
                if ($extension === 'png') {
                    imagepng($new_image, $compressed_image_path, 6); // Save as PNG with compression level 6
                } else {
                    imagejpeg($new_image, $compressed_image_path, 75); // Save as JPEG with quality 75
                }

                imagedestroy($new_image);
                imagedestroy($original_image);

                return $file;
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Not an image',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function deletePhoto($uuid)
    {
        try {
            DB::beginTransaction();
            $photo = Photo::where('uuid', $uuid)->firstorfail();
            $link = $photo->qrCode;
            if (Auth::user()->localUser->id == $link->local_user_id) {
                if (isset($photo->image)) {
                    $filePathToDeleteLayer = public_path('images/profile/photos/'.$photo->image);

                    if (file_exists($filePathToDeleteLayer)) {
                        unlink($filePathToDeleteLayer);
                    }
                }
                $photo->delete();
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Photo Deleted Successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error'
            ]);
        }
    }
    public function addCaption($uuid, Request $request)
    {
        try {
            DB::beginTransaction();
            $photo = Photo::where('uuid', $uuid)->firstorfail();
            $link = $photo->qrCode;
            if (Auth::user()->localUser->id == $link->local_user_id) {
                $photo->update([
                    'id' => $photo->id,
                    'caption' => $request->caption,
                ]);
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Caption Added Successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function addTimeline($uuid, Request $request)
    {
        try {
            DB::beginTransaction();
            // $profile = Profile::where('uuid', $uuid)->firstorfail();
            $link = Link::where('uuid', $uuid)->firstorfail();

            Timeline::create([
                'uuid' => Str::uuid(),
                'title' => $request->title,
                'date' => $request->date,
                'description' => $request->description,
                'link_id' => $link->id,
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'messsage' => 'Timeline added successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'messsage' => 'Something went wrong'
            ]);
        }
    }
    public function addTribute($uuid, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2',
                'image' => 'nullable|image|mimes:png,jpg,jpeg'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => '422',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]);
            }
            DB::beginTransaction();
            // $profile = Profile::where('uuid', $uuid)->firstorfail();
            $link = Link::where('uuid', $uuid)->firstorfail();
            $imageName = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'_'.$image->getClientOriginalName();
                Storage::disk(config('filesystems.default'))->putFileAs('images/profile/tributes', $image, $imageName);
                
            }

            Tribute::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imageName,
                'link_id' => $link->id
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'messsage' => 'Tribute added successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'messsage' => 'Something went wrong'
            ]);
        }
    }
    public function scanCode($uuid)
    {
        try {
            $link = Link::where('uuid', $uuid)->firstorfail();

            if ($link->local_user_id) {
                if ($link->profile || $link->photos || $link->tributes || $link->timelines) { //is linked and has data
                    // Check if the authenticated user owns this QR code
                    $isOwner = false;
                    if (Auth::guard('api')->user()) {
                        $currentUser = Auth::guard('api')->user();
                        $isOwner = $currentUser->localUser->id == $link->local_user_id;
                    }
                    
                    return response()->json([
                        'status' => 200,
                        'Details' => new LinkResource($link),
                        'is_owner' => $isOwner,
                    ]);
                } else { //linked but no data
                    return response()->json([
                        'status' => 203,
                        'messasge' => 'sada-login',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 201,
                    'message' => 'Link-login',
                    'version_type' => $link->version_type ?? 'full'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function linkCode($uuid)
    {
        try {
            $link = Link::where('uuid', $uuid)->firstorfail();

            if (Auth::guard('api')->user()) {
                $user = Auth::guard('api')->user();
                DB::beginTransaction();
                $link->update([
                    'id' => $link->id,
                    'local_user_id' => $user->localUser->id,
                ]);
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Please Add Profile'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function myQrCodes()
    {
        try {
            $user = Auth::user();
            $local_user = $user->localUser;
            $links = $local_user->links;
            return response()->json([
                'status' => 200,
                'data' => MyQrCodesResource::collection($links),
            ]);
        } catch (\Throwable $th) {
            \Log::error('myQrCodes error: ' . $th->getMessage(), [
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ], 500);
        }
    }
    public function updateTimeline($uuid, Request $request)
    {
        try {
            DB::beginTransaction();
            $timeline = Timeline::where('uuid', $uuid)->firstorfail();
            $link = $timeline->link;
            if (Auth::user()->localUser->id == $link->local_user_id) {
                $timeline->update([
                    'id' => $timeline->id,
                    'title' => $request->title,
                    'date' => $request->date,
                    'description' => $request->description,
                ]);
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Updated Successfully',
                    'data' => new TimelineResource($timeline),
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function deleteTimeline($uuid)
    {
        try {
            DB::beginTransaction();
            $timeline = Timeline::where('uuid', $uuid)->firstorfail();
            $link = $timeline->link;
            if (Auth::user()->localUser->id == $link->local_user_id) {
                $timeline->delete();
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Deleted Successfully',
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function editTribute($uuid, Request $request)
    {
        try {
            DB::beginTransaction();
            $tribute = Tribute::where('uuid', $uuid)->firstorfail();
            $link = $tribute->link;
            if (Auth::user()->localUser->id == $link->local_user_id) {
                $imageName = $tribute->image;

                if ($request->hasFile('image')) {
                    if (isset($imageName)) {
                        $filePathToDeleteLayer = public_path('images/profile/tributes/'.$imageName);

                        if (file_exists($filePathToDeleteLayer)) {
                            unlink($filePathToDeleteLayer);
                        }
                    }
                    $image = $request->file('image');
                    $imageName = time().'_'.$image->getClientOriginalName();
                    Storage::disk(config('filesystems.default'))->putFileAs('images/profile/tributes', $image, $imageName);
                
                }
                $tribute->update([
                    'id' => $tribute->id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'image' => $imageName,
                    'check_in' => $request->check_in
                ]);

                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Updated Successfully',
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function deleteTribute($uuid)
    {
        try {
            DB::beginTransaction();
            $tribute = Tribute::where('uuid', $uuid)->firstorfail();
            $link = $tribute->link;

            if (Auth::user()->localUser->id == $link->local_user_id) {
                $tribute->delete();
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Deleted Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorized for this action',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function myProfile()
    {
        try {
            $user = Auth::user();
            return response()->json([
                'status' => 200,
                'data' => new UserProfileResource($user),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function updateMyProfile(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $user->update([
                'id' => $user->id,
                'name' => $request->name,
            ]);
            $local = $user->localUser;
            $local->update([
                'id' => $local->id,
                'phone' => $request->phone,
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Profile updated successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function shareProfile($link)
    {
        try {
            $link = env('APP_URL').'/'.$link;
            return response()->json([
                'status' => 200,
                'data' => $link,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal server error',
            ]);
        }
    }
    public function changePassword(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (! is_null($request->old_password) && Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Password Changed Successfully.',
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'status' => 500,
                    'message' => 'Old password is incorrect.',
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function removeRelation($uuid)
    {
        try {
            DB::beginTransaction();
            $relation = Relation::where('uuid', $uuid)->firstorfail();
            if ($relation->profile->link->local_user_id == Auth::user()->localUser->id) {
                if ($relation->image_name) {
                    $filePathToDelete = public_path('images/profile/relations/'.$relation->image_name);
                    $this->deletePicture($filePathToDelete);
                }
                $relation->delete();
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Relation Deleted Successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorize dor this action'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Internal Server Error'
            ]);
        }
    }

    /**
     * Toggle which tribute tabs (except Legacy) appear in navigation.
     */
    public function updateTabVisibility(string $uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'family_tree' => 'nullable|boolean',
            'gallery' => 'nullable|boolean',
            'timeline' => 'nullable|boolean',
            'tribute' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $link = Link::where('uuid', $uuid)->firstOrFail();
            if (! $link->profile) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Profile not found',
                ], 404);
            }
            if (Auth::user()->localUser->id != $link->local_user_id) {
                return response()->json([
                    'status' => 401,
                    'message' => 'You are not authorize for this action',
                ], 401);
            }

            $incoming = array_filter(
                $request->only(array_keys(TabVisibility::DEFAULTS)),
                fn ($v) => $v !== null
            );
            $merged = TabVisibility::merge($link->profile->tab_visibility ?? null);
            foreach ($incoming as $key => $value) {
                $merged[$key] = (bool) $value;
            }

            $link->profile->update(['tab_visibility' => $merged]);

            return response()->json([
                'status' => 200,
                'message' => 'Tab visibility updated',
                'data' => [
                    'tab_visibility' => TabVisibility::merge($link->profile->fresh()->tab_visibility),
                ],
            ]);
        } catch (\Throwable $th) {
            \Log::error('updateTabVisibility: '.$th->getMessage(), [
                'exception' => $th,
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    private function deletePicture($pictureWithCompletePath)
    {
        if (file_exists($pictureWithCompletePath)) {
            unlink($pictureWithCompletePath);
        }
    }

    /**
     * @param  string  $stored  Filename stored in DB, or empty, or e.g. "stock/foo.svg" (no-op for stock)
     */
    private function deleteStoredProfileFile(?string $stored): void
    {
        if ($stored === null || $stored === '') {
            return;
        }
        if (str_starts_with($stored, 'stock/')) {
            return;
        }
        $relative = 'images/profile/profile_pictures/'.$stored;
        try {
            $disk = config('filesystems.default');
            if (Storage::disk($disk)->exists($relative)) {
                Storage::disk($disk)->delete($relative);
            }
        } catch (\Throwable $e) {
            \Log::warning('deleteStoredProfileFile: disk delete failed', ['e' => $e->getMessage()]);
        }
        $this->deletePicture(public_path('images/profile/profile_pictures/'.$stored));
    }

    /**
     * @param  string  $stored  Filename stored in DB, or empty, or stock/* (no-op for stock)
     */
    private function deleteStoredCoverFile(?string $stored): void
    {
        if ($stored === null || $stored === '') {
            return;
        }
        if (str_starts_with($stored, 'stock/')) {
            return;
        }
        $relative = 'images/profile/cover_pictures/'.$stored;
        try {
            $disk = config('filesystems.default');
            if (Storage::disk($disk)->exists($relative)) {
                Storage::disk($disk)->delete($relative);
            }
        } catch (\Throwable $e) {
            \Log::warning('deleteStoredCoverFile: disk delete failed', ['e' => $e->getMessage()]);
        }
        $this->deletePicture(public_path('images/profile/cover_pictures/'.$stored));
    }
}
