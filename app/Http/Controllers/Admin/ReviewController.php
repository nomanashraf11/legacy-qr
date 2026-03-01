<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Models\Review;
use Yajra\DataTables\DataTables;
use App\Models\Contact;
use App\Models\Inquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ReviewController extends Controller
{
    public function list(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Review::all();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $btn = '<i class="uil-trash-alt deleteReviewButton text-primary fs-3" style="cursor:pointer;" name = "deleteReviewButton" id="' . $row->id . '"></i>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('admin.pages.reviewList');
        } catch (\Throwable $th) {
            return redirect(route('admin.batches'))->with(['status' => false, 'message' => 'something went wrong']);
        }
    }
    public function inquries(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Inquiry::orderBy('created_at', 'desc');
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->make(true);
            }
            return view('admin.pages.inquries');
        } catch (\Throwable $th) {
            return redirect(route('admin.batches'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }

    public function contact_mails(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Contact::whereNotIn('email', function ($query) {
                    $query->select('email')->from('users');
                })->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $btn = '<i class="uil uil-plus-square registerReseller text-primary fs-3" style="cursor:pointer;"  name="' . $row->first_name . '" email="' . $row->email . '"website="' . $row->website . '"phone="' . $row->phone . '"address="' . $row->address . '"></i>';
                        return $btn;
                    })
                    ->rawColumns(['action'])

                    ->make(true);
            }
            return view('admin.pages.contactList');
        } catch (\Throwable $th) {
            return redirect(route('admin.batches'))->with(['status' => false, 'message' => 'something went wrong']);
        }
    }
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $review = Review::findorfail($id);

            if (isset($review->image)) {
                $filePathToDeleteLayer = public_path('images/reviews/' . $review->image);

                if (file_exists($filePathToDeleteLayer)) {
                    unlink($filePathToDeleteLayer);
                }
            }

            $review->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => true,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function store(ReviewRequest $request)
    {
        try {
            DB::beginTransaction();
            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('images/reviews/', $image, $imageName);
            }
            Review::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'title' => $request->title,
                'description' => $request->description,
                'image' => $imageName,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Saved Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function reply(Request $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'subject' => $request->subject,
                'email' => $request->email,
                'message' => $request->message,
            ];
            Mail::to($request->email)->send(new ContactMail($data));
            return response()->json([
                'status' => true,
                'message' => 'Reply Sent Successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}
