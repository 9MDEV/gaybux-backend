<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RobuxTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Jobs\ProcessRobux;
use Illuminate\Support\Str;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use App\Models\Setting;
class RobuxController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // เฉพาะ index ดูได้เฉพาะแอดมิน
            new Middleware('role:แอดมิน', only: ['index', 'total']),

            // ฟังก์ชัน show กับ buy อนุญาตทั้งแอดมินและลูกค้า (OR)
            new Middleware('role:แอดมิน|ลูกค้า', only: ['show', 'buy','getSetting']),
        ];
    }

    public function getSetting(){
        return response()->json([
            'status' => 'success',
            'message' => "สำเร็จ",
            'data' => [
                'settings' => Setting::select(['robux_min','robux_rate'])->first(),
            ]
        ]);
    }

    public function index(Request $request){
        $perPage = $request->input('per_page', 10);
        $RobuxTransaction = RobuxTransaction::simplePaginate($perPage);
        return response()->json($RobuxTransaction);
    }

    public function total(Request $request)
    {
        $filter = $request->query('filter'); // รับค่าจาก query เช่น ?filter=week

        $query = RobuxTransaction::query();

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
        $RobuxTransaction = RobuxTransaction::where('user_id', $request->user()->id)->simplePaginate($perPage);
        return response()->json($RobuxTransaction);
    }


    public function buy(Request $request){
        DB::beginTransaction();
        try{
            $field = $request->validate([
                'username' => 'required|string',
                'group_id'    => 'required|integer|exists:groups,group_id',
                'amount' => 'required|integer',
            ]);

            $username = $field['username'];
            $group_id = $field['group_id'];
            $amount = $field['amount'];
            $setting = Setting::first();
            $rate = $setting['robux_rate'];
            $min = $setting['robux_min'];
            if($amount < $min) return response()->json([
                'status' => 'error',
                'message' => "เติมขั้นต่ำ " . $min . ' โรบัค',
            ]);
            $user = $request->user();

            $price = round($amount/$rate,2);
            $tran = $user->withdrawFloat($price);
            $rbxtran = RobuxTransaction::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'username' => $username,
                'transaction_id' => $tran->id,
                'amount' => $amount,

            ]);
            // // //หักเงินสำเร็จแล้ว
            $group = Group::where('group_id', $group_id)->firstOrFail();
            DB::commit();
            DB::afterCommit(function () use ($group, $user, $username, $amount, $tran, $rbxtran) {
                ProcessRobux::dispatch($group->group_id, $user->id, $username, $amount, $tran->id, $rbxtran->id);
            });

            return response()->json([
                'status' => 'success',
                'message' => "ระบบจะดำเนินการเติมโรบัคให้เร็วที่สุด",
                'balance' => $user->balanceFloatNum,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }

    }
}
