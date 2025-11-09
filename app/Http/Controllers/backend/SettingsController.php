<?php

namespace App\Http\Controllers\backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('generalSetting', Setting::class);
        return view('backend.common.settings');
    }

    public function store(Request $request,$type)
    {
        if($type == "general"){
            $this->authorize('generalSetting', Setting::class);
            $data = [
                'app_name' => $request->app_name,
                'mobile'   => $request->mobile,
                'address'  => $request->address,
            ];

            if ($request->hasFile('app_logo')) {
                $file = $request->file('app_logo');
                $image = Helper::uploadImage($file, 'logo');
                if ($image['status'] === true) {
                    $data['app_logo'] = $image['name']; 
                }
            }

            foreach ($data as $key => $value) {
                Setting::updateOrInsert(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            return back()->with('success', 'General settings saved successfully!');
        }
        if($type == 'maintenance'){
            $data = [
                'mode' => $request->mode,
            ];

            foreach ($data as $key => $value) {
                Setting::updateOrInsert(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            return back()->with(['success'=> 'General settings saved successfully!','type'=> $type]);
        }
    }
}
