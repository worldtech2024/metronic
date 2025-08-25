<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use App\Models\Order;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class HomeController extends Controller
{
    public function dashboard()
    {
        $today = now()->toDateString();

        return response()->json([
            'brandCount' => Brand::count(),
            'productsCount' => Product::count(),
            'invoicesToday' => Order::whereDate('created_at', $today)->count(),
            'totalSalesToday' => Order::whereDate('created_at', $today)->sum('totalPrice'),
            'invoicesStatusToday' => [
                'purchased' => Order::whereDate('created_at', $today)->where('status', 'purchased')->count(),
                'notPurchased' => Order::whereDate('created_at', $today)->where('status', 'projectCancelled')->count(),
                'totalOrder' => Order::whereDate('created_at', $today)->count(),
            ],

            'monthlySales' => $this->getMonthlySales(),
        ]);
    }

    private function getMonthlySales()
    {
        $sales = Order::selectRaw('MONTH(created_at) as month, SUM(totalPrice) as total')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = [
                'month' => now()->month($i)->format('M'),
                'total' => round($sales[$i] ?? 0, 2),
            ];
        }
        return [
            'year' => now()->year,
            'total_sales' => round($sales->sum(), 2),
            'monthly' => $result,
        ];
    }


}