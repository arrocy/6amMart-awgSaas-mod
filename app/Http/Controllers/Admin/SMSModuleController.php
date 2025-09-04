<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class SMSModuleController extends Controller
{
    public function sms_index()
    {
        $published_status = addon_published_status('Gateways');

        $routes = config('addon_admin_routes');
        $desiredName = 'sms_setup';
        $payment_url = '';
        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $payment_url = $route['url'];
                    break 2;
                }
            }
        }
        $data_values=  Setting::where('settings_type','sms_config')->whereIn('key_name', ['twilio','nexmo','2factor','msg91','alphanet_sms'])->get() ?? [];
        return view('admin-views.business-settings.sms-index',compact('data_values','published_status','payment_url'));
    }

    public function sms_update(Request $request, $module)
    {
        // Arrocy mod
        $login_setup_status = 0;//Helpers::get_business_settings('otp_login_status')??0;
        $is_firebase_active=Helpers::get_business_settings('firebase_otp_verification') ?? 0;
        $phone_verification_status = Helpers::get_business_settings('phone_verification_status')??0;
        if(!$is_firebase_active && $login_setup_status && ($request['status']==0)){
            Toastr::warning(translate('otp_login_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));
            return redirect()->back();
        }
        if(!$is_firebase_active && $phone_verification_status && ($request['status']==0)){
            Toastr::warning(translate('phone_verification_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));
            return redirect()->back();
        }
        if ($module == 'twilio') {
                $additional_data = [
                    'status' => $request['status'],
                    'sid' => $request['sid'],
                    'messaging_service_sid' => $request['messaging_service_sid'],
                    'token' => $request['token'],
                    'from' => $request['from'],
                    'otp_template' => $request['otp_template'],
                ];

        } elseif ($module == 'nexmo') {
            $additional_data = [
                'status' =>$request['status'],
                'api_key' => $request['api_key'],
                'api_secret' => $request['api_secret'],
                'token' =>$request['token'] ?? null,
                'from' => $request['from'],
                'otp_template' => $request['otp_template'],
            ];

        } elseif ($module == '2factor') {
            $additional_data = [
                'status' => $request['status'],
                'api_key' => $request['api_key'],
                'otp_template' => $request['otp_template'] ?? 'Your OTP is: #OTP#',
            ];
        } elseif ($module == 'msg91') {
            $additional_data = [
                'status' => $request['status'],
                'template_id' => $request['template_id'],
                'auth_key' => $request['auth_key'],
            ];
        } elseif ($request['gateway'] == 'alphanet_sms') {
            $additional_data = [
                'status' => $request['status'],
                'api_key' =>$request['api_key'],
                'sender_id' =>$request['sender_id'] ?? null,
                'otp_template' =>$request['otp_template'],
            ];
        }

        $data= ['gateway' => $module ,
        'mode' =>  isset($request['status']) == 1  ?  'live': 'test'
        ];

    $credentials= json_encode(array_merge($data, $additional_data));
    DB::table('addon_settings')->updateOrInsert(['key_name' => $module, 'settings_type' => 'sms_config'], [
        'key_name' => $module,
        'live_values' => $credentials,
        'test_values' => $credentials,
        'settings_type' => 'sms_config',
        'mode' => isset($request['status']) == 1  ?  'live': 'test',
        'is_active' => isset($request['status']) == 1  ?  1: 0 ,
    ]);

    if ($request['status'] == 1) {
        foreach (['twilio','nexmo','2factor','msg91','alphanet_sms'] as $gateway) {
            if ($module != $gateway) {
                $keep = Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->first();
                if (isset($keep)) {
                    $hold = $keep->live_values;
                    $hold['status'] = 0;
                    Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->update([
                        'live_values' => $hold,
                        'test_values' => $hold,
                        'is_active' => 0,
                    ]);
                }
            }
        }
    }
        return back();
    }
}
