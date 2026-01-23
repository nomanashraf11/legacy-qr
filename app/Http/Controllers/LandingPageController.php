<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMailRequest;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\Contact;
use App\Models\Inquiry;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class LandingPageController extends Controller
{
    public function about()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            return view('landing.pages.about', compact('admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function pricing()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            return view('landing.pages.pricing', compact('admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function terms()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            return view('landing.pages.terms', compact('admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function contact()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            return view('landing.pages.contact', compact('admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function sendContactMail(ContactMailRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::role('admin')->first();
            Inquiry::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,

            ]);
            DB::commit();
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
            ];
            Mail::to($user->email)->send(new ContactMail($data));
            return response()->json([
                'status' => true,
                'message' => 'Message Sent Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function howItWorks()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            return view('landing.pages.how-it-works', compact('admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function landing()
    {
        try {
            $admin = User::role('admin')->first()->admin;
            $reviews = Review::latest()->take(8)->get();
            return view('landing.pages.index', compact('reviews', 'admin'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
