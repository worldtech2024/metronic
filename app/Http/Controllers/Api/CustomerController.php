<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use App\Exports\CustomerExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
    
    $perPage = $request->input('pageNum');

    $query = User::query();


    $column = $request->input('search');
    $value = $request->input('value');

    $allowedColumns = ['name', 'email', 'phone'];

    if ($column && $value && in_array($column, $allowedColumns)) {
        $query->where($column, 'LIKE', '%' . $value . '%');
    }

    $perPage = $request->input('pageNum', 10);

    $customers = $query->paginate($perPage);
    return ApiResponse::sendResponse(
        true,
        'Customers retrieved successfully',
        $customers
    );
}




    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $customer = User::create($request->validated());
        return ApiResponse::sendResponse(true, 'Customer created successfully', $customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return ApiResponse::sendResponse(true, 'Customer retrieved successfully', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::where('id', $id)->first();
        $data = $request->validated();
        $user->update($data);
        return ApiResponse::sendResponse(true, 'Customer updated successfully', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return ApiResponse::sendResponse(true, 'Customer deleted successfully');
    }


    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return ApiResponse::sendResponse(false, 'لم يتم إرسال أي ملف.');
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return ApiResponse::sendResponse(false, 'الملف المرفوع غير صالح.');
        }

        $rows = (new FastExcel)->withoutHeaders()->import($file->getRealPath());

        // صف الأعمدة الحقيقي
        $headers = $rows[1];

        // تخطي أول صفين (required / optional و headers)
        $dataRows = $rows->slice(2);

        foreach ($dataRows as $rowIndex => $rawRow) {
            $row = [];
            foreach (range(0, count($headers) - 1) as $i) {
                $row[trim($headers[$i])] = $rawRow[$i] ?? null;
            }

            try {
                if (empty($row['email']) || empty($row['customer_name'])) {
                    Log::warning("Missing required user data in row $rowIndex", $row);
                    continue;
                }

                $user = User::firstOrNew(['email' => $row['email']]);

                $user->name = $row['customer_name'];
                $user->phone = $row['phone'] ?? null;
                $user->address = $row['address_1'] ?? null;
                $user->address2 = $row['Address_2'] ?? null;
                $user->city = $row['city'] ?? null;
                $user->country = $row['country'] ?? null;
                $user->taxNum = $row['vat_number'] ?? null;
                $user->commercialRegister = $row['cr_number'] ?? null;



                $user->save();
            } catch (\Exception $e) {
                Log::error('User import error: ' . $e->getMessage(), $row);
            }
        }

        return ApiResponse::sendResponse(true, 'Users imported successfully');
    }


    public function export()
    {
        $users = User::all();
        return Excel::download(new CustomerExport($users), 'customers' . '.xlsx');
    }


}