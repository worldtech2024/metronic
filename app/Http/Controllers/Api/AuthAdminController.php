<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AdminResource;
use App\Mail\AuthMail;
use App\Models\Admin;
use App\Trait\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthAdminController extends Controller
{

    //  public function register(Request $request)
    // {
    //     $data = $request->validate([
    //         'username' => 'required|string|unique:admins,username',
    //         'name' => 'required|string',
    //         'email' => 'required|string|email|unique:admins,email',
    //         'phone' => 'required|string|unique:admins,phone',
    //         'password' => 'required|string|min:8',
    //         'image' => 'nullable|image|',
    //         'role' => 'required|in:admin,supervisor,employee',
    //         'permissions' => 'nullable|array',

    //     ]);

    //     if (!empty($data['password'])) {
    //         $data['password'] = Hash::make($data['password']);
    //     }
    //     $user = Admin::create($data);

    //     Mail::to($user->email)->send(new AuthMail($user->otp));

    //     return ApiResponse::sendResponse(
    //         true,
    //         'Employee Created successfully',
    //         new AdminResource($user)
    //     );
    // }
    public function register(Request $request)
    {
        $data = $request->validate([
            'username'    => 'required|string|unique:admins,username',
            'name'        => 'required|string',
            'email'       => 'required|string|email|unique:admins,email',
            'phone'       => 'required|string|unique:admins,phone',
            'password'    => 'required|string|min:8',
            'image'       => 'nullable|image',
            'role'        => 'required|in:admin,supervisor,employee',
            'permissions' => 'nullable|array',
        ]);

        // تحويل النصوص "true"/"false" لقيم Boolean حقيقية مع تنظيف اسم المفتاح

        if (! empty($data['permissions'])) {
            $allPermissions      = ["create_estimation", "price_estimation", "approve_estimation"];
            $data['permissions'] = Admin::normalizePermissions($data['permissions'], $allPermissions);
        }

        // تشفير الباسورد
        $data['password'] = Hash::make($data['password']);

        // إنشاء المستخدم
        $user = Admin::create($data);

        // إرسال الإيميل
        Mail::to($user->email)->send(new AuthMail($user->otp));

        return ApiResponse::sendResponse(
            true,
            'Employee Created successfully',
            new AdminResource($user)
        );
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string',
            'username'    => 'sometimes|string|unique:admins,username,' . $admin->id,
            'email'       => 'sometimes|email|unique:admins,email,' . $admin->id,
            'image'       => 'nullable|image',
            'phone'       => 'sometimes|string|unique:admins,phone,' . $admin->id,
            'role'        => 'sometimes|in:admin,supervisor,employee',
            'permissions' => 'sometimes|array',
        ]);

        if (! empty($data['permissions'])) {
            $allPermissions      = ["create_estimation", "price_estimation", "approve_estimation"];
            $data['permissions'] = Admin::normalizePermissions($data['permissions'], $allPermissions);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $admin->update($data);

        return ApiResponse::sendResponse(true, 'Admin updated successfully', new AdminResource($admin));
    }

    //  public function index(Request $request)
    // {

    //     $perPage = $request->input('pageNum');

    //     $query = Admin::query();

    //     // if ($request->filled('name')) {
    //     //     $query->where('name', 'LIKE', '%' . $request->name . '%');
    //     // }

    //     // $customers = $query->paginate($perPage); // عدد العناصر لكل صفحة
    //     $column = $request->input('search');
    //     $value = $request->input('value');

    //     // تحديد الأعمدة المسموح البحث فيها
    //     $allowedColumns = ['name', 'email', 'phone'];

    //     if ($column && $value && in_array($column, $allowedColumns)) {
    //         $query->where($column, 'LIKE', '%' . $value . '%');
    //     }

    //     $perPage = $request->input('pageNum', 10);

    //     $employees = $query->paginate($perPage);
    //     return ApiResponse::sendResponse(
    //         true,
    //         'Customers retrieved successfully',
    //         $employees
    //     );
    // }
// public function index(Request $request)
// {
//     $perPage = $request->input('pageNum', 10);

    //     $query = Admin::query();

    //     $column = $request->input('search');
//     $value = $request->input('value');
//     $allowedColumns = ['name', 'email', 'phone'];

    //     if ($column && $value && in_array($column, $allowedColumns)) {
//         $query->where($column, 'LIKE', '%' . $value . '%');
//     }

    //     $employees = $query->paginate($perPage);

    //     $employees->getCollection()->transform(function ($admin) {
//         // لو عندك الأعمدة permissions مخزنة كـ Array أو JSON في قاعدة البيانات
//         $existingPermissions = is_array($admin->permissions)
//             ? $admin->permissions
//             : json_decode($admin->permissions, true);

    //         if (!is_array($existingPermissions)) {
//             $existingPermissions = [];
//         }

    //         // قائمة الصلاحيات الثابتة
//         $allPermissions = ["create_estimation", "price_estimation", "approve_estimation"];

    //         // نعمل الماب بحيث اللي موجود يبقى true والباقي false
//         $permissionsMap = [];
//         foreach ($allPermissions as $perm) {
//             $permissionsMap[$perm] = in_array($perm, $existingPermissions);
//         }

    //         return [
//             "id"       => $admin->id,
//             "username" => $admin->username,
//             "name"     => $admin->name,
//             "email"    => $admin->email,
//             "phone"    => $admin->phone,
//             "password" => $admin->password,
//             "image"    => $admin->image,
//             "otp"      => $admin->otp,
//             "role"     => $admin->role,
//             "permissions" => $permissionsMap,
//             "remember_token" => $admin->remember_token,
//             "created_at"     => $admin->created_at,
//             "updated_at"     => $admin->updated_at,
//         ];
//     });

    //     return ApiResponse::sendResponse(
//         true,
//         'Customers retrieved successfully',
//         $employees
//     );
// }

    // public function index(Request $request)
// {
//     $perPage = $request->input('pageNum', 10);

    //     $query = Admin::query();

    //     $column = $request->input('search');
//     $value = $request->input('value');
//     $allowedColumns = ['name', 'email', 'phone'];

    //     if ($column && $value && in_array($column, $allowedColumns)) {
//         $query->where($column, 'LIKE', '%' . $value . '%');
//     }

    //     $employees = $query->paginate($perPage);

    //     $employees->getCollection()->transform(function ($admin) {
//         // قراءة permissions كـ array أو JSON
//         $existingPermissions = is_array($admin->permissions)
//             ? $admin->permissions
//             : json_decode($admin->permissions, true);

    //         if (!is_array($existingPermissions)) {
//             $existingPermissions = [];
//         }

    //         // الصلاحيات الثابتة
//         $allPermissions = ["create_estimation", "price_estimation", "approve_estimation"];

    //         $permissionsMap = [];
//         foreach ($allPermissions as $perm) {
//             $permissionsMap[$perm] = isset($existingPermissions[$perm])
//                 ? (bool) $existingPermissions[$perm]
//                 : false;
//         }

    //         return [
//             "id"       => $admin->id,
//             "username" => $admin->username,
//             "name"     => $admin->name,
//             "email"    => $admin->email,
//             "phone"    => $admin->phone,
//             "password" => $admin->password,
//             "image"    => $admin->image,
//             "otp"      => $admin->otp,
//             "role"     => $admin->role,
//             "permissions" => $permissionsMap,
//             "remember_token" => $admin->remember_token,
//             "created_at"     => $admin->created_at,
//             "updated_at"     => $admin->updated_at,
//         ];
//     });

    //     return ApiResponse::sendResponse(
//         true,
//         'Customers retrieved successfully',
//         $employees
//     );
// }

    public function index(Request $request)
    {
        $perPage = $request->input('pageNum', 10);

        $query = Admin::query();

        $column         = $request->input('search');
        $value          = $request->input('value');
        $allowedColumns = ['name', 'email', 'phone'];

        if ($column && $value && in_array($column, $allowedColumns)) {
            $query->where($column, 'LIKE', '%' . $value . '%');
        }

        $allPermissions = ["create_estimation", "price_estimation", "approve_estimation"];

        $employees = $query->paginate($perPage);

        $allPermissions = ["create_estimation", "price_estimation", "approve_estimation"];

$employees->getCollection()->transform(function ($admin) use ($allPermissions) {
    return [
        "id" => $admin->id,
        "username" => $admin->username,
        "name" => $admin->name,
        "email" => $admin->email,
        "phone" => $admin->phone,
        "image" => $admin->image,
        "otp" => $admin->otp,
        "role" => $admin->role,
        "permissions" => Admin::normalizePermissions($admin->permissions ?? [], $allPermissions),
        "remember_token" => $admin->remember_token,
        "created_at" => $admin->created_at,
        "updated_at" => $admin->updated_at,
    ];
});


        return ApiResponse::sendResponse(
            true,
            'Admins retrieved successfully',
            $employees
        );
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username'  => 'required|string|exists:admins,username',
                'password'  => 'required|string|min:8',
                'fcm_token' => 'sometimes|string',
            ]);
            $user = Admin::where('username', $request->username)->first();
            if (! $user) {
                return ApiResponse::errorResponse(false, ('username does not exist'));
            }

            if (! Hash::check($request->password, $user->password)) {
                return ApiResponse::errorResponse(false, 'Invalid credentials.');
            }
            if ($request->filled('fcm_token') && $user->fcm_token !== $request->fcm_token) {
                $user->update(['fcm_token' => $request->fcm_token]);
            }

            $user->tokens()->delete();
            $user["token"] = $user->createToken('Bearer ', ['app:all'])->plainTextToken;
            return ApiResponse::sendResponse(true, 'Login Successful!', new AdminResource($user));
        } catch (Exception $e) {
            return ApiResponse::errorResponse(false, $e->getMessage());
        }
    }
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::errorResponse(false, 'No authenticated user');
            }
            $user->currentAccessToken()->delete();
            return ApiResponse::sendResponse(true, 'Logout Successful!');
        } catch (\Exception $e) {
            return ApiResponse::errorResponse(false, $e->getMessage());
        }
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp'   => 'required|string',
            'email' => 'required|exists:admins,email',
        ]);
        $user = Admin::where('email', $request->email)->first();

        if (! $user) {
            return ApiResponse::errorResponse(false, 'User not found');
        }

        if ($user->otp != $request->otp) {
            return ApiResponse::errorResponse(false, 'Invalid OTP.');
        }

        return ApiResponse::sendResponse(true, 'Email verified successfully');
    }

    public function forgetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => [
                    'required',
                    'string',
                    'exists:admins,email',
                ],
            ]);

            $otp  = rand(1111, 9999);
            $user = Admin::where('email', $request->email)->first();

            if (! $user) {
                return ApiResponse::sendResponse(false, 'User not found');
            }
            $user->update(['otp' => $otp]);
            Mail::to($user->email)->send(new AuthMail($user->otp));

            return ApiResponse::sendResponse(true, 'OTP sent successfully. Please verify to reset your password.', [
                'otp' => $user->otp,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::errorResponse(false, $e->getMessage());
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'email'    => [
                    'required',
                    'string',
                    'exists:admins,email',
                ],
                'password' => 'required|string|min:8|confirmed',

            ]);

            $user = Admin::where('email', $request->email)->first();

            if (! $user) {
                return ApiResponse::sendResponse(false, 'User not found');
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return ApiResponse::sendResponse(true, 'Password reset successfully.');
        } catch (\Exception $e) {
            return ApiResponse::errorResponse(false, $e->getMessage());

        }
    }
    public function profile()
    {
        $user = Auth::user();
        return ApiResponse::sendResponse(true, 'Data Retrieve Successfully', new AdminResource($user));
    }
    public function updateProfile(UpdateProfileRequest $request)
    {

        $user = Auth::user();
        $data = $request->validated();

        if (! isset($data['image']) || $data['image'] === null) {
            unset($data['image']);
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $path = 'uploads/images/admins';

            // 1- حذف الصورة القديمة لو موجودة
            if (! empty($user->image)) {
                                                                           // الصورة القديمة متخزنة في DB زي: storage/uploads/images/admins/xxx.png
                $oldImagePath = str_replace('storage/', '', $user->image); // عشان يوصل لـ storage/app/public

                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }

            // 2- رفع الصورة الجديدة
            $uploadedFilePath = $file->store($path, 'public');

            // 3- تخزين المسار في DB مع كلمة storage/ زي ما انت عاوز
            $data['image'] = 'storage/' . $uploadedFilePath;
        }

        if (! isset($data['password']) || empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return ApiResponse::sendResponse(true, 'Data Updated Successfully', new AdminResource($user));
    }

    public function resetCode(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|string|exists:admins,email',
            ]);
            $user = Admin::where('email', $request->email)->first();
            if (! $user) {
                return ApiResponse::errorResponse(false, 'User not found');
            }
            $data['otp'] = rand(1111, 9999);
            $user->update($data);
            Mail::to($user->email)->send(new AuthMail($user->otp));
            return ApiResponse::sendResponse(true, 'Code Resend Successful', [
                'otp' => $user->otp,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::errorResponse(false, $e->getMessage());
        }
    }
}
