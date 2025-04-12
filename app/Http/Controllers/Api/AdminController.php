<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OTPHP\TOTP;
use App\Models\Setting;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // เฉพาะ index ดูได้เฉพาะแอดมิน
            new Middleware('role:แอดมิน', only: ['genOTP', 'getSetting', 'updateSetting']),
        ];
    }


    public function genOTP(Request $request){
        $request->validate([
            'secret_key' => 'required|string',
        ], [
            'required' => 'กรุณาป้อนค่าในช่อง :attribute',
            'numeric'  => 'ค่า :attribute รองรับเฉพาะตัวเลข',
            'exists'   => 'ไม่พบค่า :attribute ในฐานข้อมูล',
        ]);

        $totp = TOTP::create($request->secret_key);
        return response()->json([
            'status' => 'success',
            'message' => "สำเร็จ",
            'data' => [
                'otp' => $totp->now(),
            ]
        ]);
    }

    public function getSetting(){
        return response()->json([
            'status' => 'success',
            'message' => "สำเร็จ",
            'data' => [
                'settings' => Setting::first(),
            ]
        ]);
    }

    public function updateSetting(Request $request){
        $request->validate([
            'topup_fee' => 'sometimes|numeric',
            'topup_phone'  => 'sometimes|string|max:11',
            'robux_min' => 'sometimes|integer',
            'robux_rate' => 'sometimes|numeric',
        ]);
        $updateData = array_filter([
            'topup_fee' => $request->topup_fee,
            'topup_phone' => $request->topup_phone,
            'robux_min' => $request->robux_min,
            'robux_rate' => $request->robux_rate,
        ]);
        $Setting = Setting::first();
        $Setting->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => "สำเร็จ",
            'data' => [
                'settings' => $Setting,
            ]
        ]);
    }

}
