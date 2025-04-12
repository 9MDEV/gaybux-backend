<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use App\Models\TopupTransaction;
use Exception;
class TopupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // เฉพาะ index ดูได้เฉพาะแอดมิน
            new Middleware('role:แอดมิน', only: ['index','total']),

            // ฟังก์ชัน show กับ buy อนุญาตทั้งแอดมินและลูกค้า (OR)
            new Middleware('role:แอดมิน|ลูกค้า', only: ['show', 'topup','getSetting']),
        ];
    }


    private const TOPUP_ENDPOINT = 'http://127.0.0.1:5555/topup';
    private const KEY = 'kKIk9R5Ns0Ky2Wj1aKBcKWA3yxRmqEm8';

    public function getSetting(){
        return response()->json([
            'status' => 'success',
            'message' => "สำเร็จ",
            'data' => [
                'settings' => Setting::select(['topup_fee'])->first(),
            ]
        ]);
    }

    public function index(Request $request){
        $perPage = $request->input('per_page', 10);
        $TopupTransaction = TopupTransaction::simplePaginate($perPage);
        return response()->json($TopupTransaction);
    }
    public function total(Request $request)
    {
        $filter = $request->query('filter'); // รับค่าจาก query เช่น ?filter=week

        $query = TopupTransaction::query();

        // กรองตามช่วงเวลา
        if ($filter === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($filter === 'year') {
            $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
        }

        $totalAmount = $query->sum('amount');

        return response()->json([
            'filter' => $filter ?? 'all',
            'total' => $totalAmount,
        ]);
    }

    public function show(Request $request){
        $perPage = $request->input('per_page', 10);
        $TopupTransaction = TopupTransaction::where('user_id', $request->user()->id)->simplePaginate($perPage);
        return response()->json($TopupTransaction);
    }


    public function topup(Request $request){
        DB::beginTransaction();
        try{
            $setting = Setting::first();
            $user = $request->user();
            $phone = $setting->topup_phone;

            $field = $request->validate([
                'link' => 'required|string',
            ]);

            $link = $field['link'];
            $key = self::KEY;
            $url = self::TOPUP_ENDPOINT . "?key=$key&" . "voucher=$link&" . "phone=$phone"  ;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception("cURL error: " . curl_error($ch));
            }

            curl_close($ch);
            $response = json_decode($response);

            if($response?->status == 'success'){
                $fee = (($setting->topup_fee * $response->amount) / 100);
                $amount = $response->amount - $fee;
                $user->depositFloat($amount);

                TopupTransaction::create([
                    'ref' => $response->name,
                    'user_id' => $request->user()->id,
                    'amount' => $amount,
                    'method' => 'tw',
                    'status' => 'success',
                ]);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => "เติมเงินสำเร็จ " . $amount . ' บาท',
                    'balance' => $user->balanceFloatNum,
                ]);
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => $response->message ?? 'เกิดข้อผิดพลาดที่ไม่รู้จัก',
                ]);
            }
        }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }


    }
}
