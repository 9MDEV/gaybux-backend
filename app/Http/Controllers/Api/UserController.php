<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use DB;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller implements HasMiddleware
{
    // Middleware for permissions
    public static function middleware(): array
    {
        return [
            new Middleware('role:แอดมิน', only: ['index', 'show', 'store', 'update', 'destroy']),
            // ถ้าต้องการให้ role admin ใช้ได้เฉพาะฟังก์ชันเหล่านี้
        ];
    }

    // GET /api/users
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = User::with('roles')->simplePaginate($perPage);
        return response()->json($users);
    }

    // GET /api/users/:id
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบผู้ใช้'], 404);
        }
        return response()->json($user);
    }

    // POST /api/users
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'email'  => 'required|email',
                'password'  => 'required|string',
                'role' => 'required|exists:roles,name',  // Ensure a valid role is provided
            ], [
                'required' => 'กรุณาป้อนค่าในช่อง :attribute',
                'numeric'  => 'ค่า :attribute รองรับเฉพาะตัวเลข',
                'exists'   => 'ไม่พบค่า :attribute ในฐานข้อมูล',
            ]);

            $user = User::create([
                'username' => $request->username,
                'email'  => $request->email,
                'password'  => Hash::make($request->password),
            ]);

            // Assign role to the user
            $user->assignRole($request->role);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'เพิ่มผู้ใช้สำเร็จ',
                'data'    => ['user' => $user],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'ไม่พบผู้ใช้'], 404);
            }

            $request->validate([
                'username' => 'sometimes|string|max:255',
                'email'  => 'sometimes|numeric',
                'role' => 'required|exists:roles,name',  // Ensure a valid role is provided
            ]);
            $updateData = array_filter([
                'username' => $request->username,
                'email' => $request->email,
            ]);

            $user->update($updateData);

            // Update the user's role
            $user->syncRoles($request->role);  // syncRoles will replace all the roles

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'แก้ไขผู้ใช้สำเร็จ',
                'data'    => ['user' => $user],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/users/{id}
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'ไม่พบผู้ใช้'], 404);
            }

            $user->delete();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'ลบผู้ใช้สำเร็จ',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    // GET /api/users/{search}
    public function search($search)
    {
        $users = User::select('id', 'username', 'email')
            ->where('username', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->take(5)
            ->get();

        return $users;
    }
}
