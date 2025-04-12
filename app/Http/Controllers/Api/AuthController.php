<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;
class AuthController extends Controller
{

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {


            $field = $request->validate([
                'username' => 'required|string|min:4|max:10|unique:users,username',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|max:20|confirmed',
                'cf_token' => 'required|string',
            ]);

            $captchaSuccess = $this->verifyTurnstileCaptcha($field['cf_token']);
            if (!$captchaSuccess) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ไม่สามารถยืนยัน CAPTCHA ได้',
                ], 422);
            }

            $user = User::create([
                'username' => $field['username'],
                'email'    => $field['email'],
                'password' => bcrypt($field['password']),
            ]);

            $token = $user->createToken('token', ['*'], now()->addMonth())->plainTextToken;
            $user->assignRole('ลูกค้า');
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'สมัครสมาชิกสำเร็จ',
                'data'    => [
                    'user' => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'roles'    => $user->getRoleNames(),
                    'balance' => $user->balanceFloatNum,
                    ],
                'token' => $token],
            ])->cookie('token', $token, 60 * 24 * 30, '/', 'localhost', true, false);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $field = $request->validate([
                'username' => 'sometimes|required_without:email|string',
                'email'    => 'sometimes|required_without:username|email',
                'password' => 'required|string|min:8|max:20',
                'cf_token' => 'required|string',
            ]);

            $captchaSuccess = $this->verifyTurnstileCaptcha($field['cf_token']);
            if (!$captchaSuccess) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ไม่สามารถยืนยัน CAPTCHA ได้',
                ], 422);
            }

            // หา user จาก username หรือ email
            $user = User::where('username', $request->username)
                ->orWhere('email', $request->email)
                ->first();

            // ตรวจรหัสผ่าน
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
                ], 401);
            }

            $token = $user->createToken('token', ['*'], now()->addMonth())->plainTextToken;
            return response()->json([
                'status'  => 'success',
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'data'    => [
                    'user' => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'roles'    => $user->getRoleNames(),
                    'balance' => $user->balanceFloatNum,
                ],
                'token' => $token],
            ])->cookie('token', $token, 60 * 24 * 30, '/', 'localhost', true, false);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }

    }

    private function verifyTurnstileCaptcha($token)
    {
        $secret = env('CLOUDFLARE_TURNSTILE_SECRET'); // เก็บไว้ใน .env
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'],
        ]);


        return $response->json('success') === true;
    }

}
