<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\General;
use App\Models\User;
use Flasher\Laravel\Facade\Flasher;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use AuthorizesRequests;
    public function index(){
        $this->authorize('dashboard', General::class);
        return view('backend.index');
    }

    public function profile()
    {
        $this->authorize('profileRead', General::class);
        return view('backend.common.profile');
    }

    public function settingStore(Request $request){

    }

    public function uploadProfile(Request $request)
    {
        $this->authorize('profileEdit', General::class);
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');

            $user = User::where('id', Auth::user()->id)->first();
            if ($user) {
                if ($user->profile_picture) {
                    $oldImagePath = public_path($user->profile_picture);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $uploadPath = public_path('uploads/profile_images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/profile_images/' . $fileName;

                $file->move($uploadPath, $fileName);

                $user->profile_picture = $filePath;
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Profile uploaded successfully',
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'No image was uploaded',
        ]);
    }

    public function lock()
    {
        session(['locked' => true]);
        $sliders = [];
        return view('errors.lock', compact('sliders'));
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            session()->forget('locked');
            return redirect()->intended(route('admin.dashboard'))->with('info', 'Welcome back!');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }
}
