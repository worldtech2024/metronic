<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AdminResource;
use App\Http\Resources\BusbarResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderUnitResource;
use App\Http\Resources\ProductUnitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'orderNumber' => $this->orderNumber,
            'projectName' => $this->projectName,
            // Flags
            'isPurchaseDone' => $this->admin_buy_id === $user->id
                ? in_array($this->status, ['purchased', 'installed'])
                : false,

            'isInstallationDone' => $this->admin_install_id === $user->id
                ? $this->status === 'installed'
                : false,
            'customerFileNumber' => $this->CustomerFileNumber ?: '',
            'description' => $this->description ?: '',
            'deadline' => $this->deadline,
            'status' => $this->status,
            'subtotal' => $this->subTotal,
            'totalBusbar' => $this->totalBusbar,
            'totalDiscount' => $this->DiscountTotal,
            'totalVat' => $this->totalVAT,
            'totalPrice' => $this->totalPrice,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d'),
            'admin' => AdminResource::make($this->admin) ?? null,
            'purchasingOfficer' => AdminResource::make($this->admin_buy) ?? null,
            'installationEmployee' => AdminResource::make($this->admin_install) ?? null,
            'cutomer' => new CustomerResource($this->user) ?? null,
            'order_units' => OrderUnitResource::collection($this->orderUnits) ?? null,
            // 'basebars' => BusbarResource::collection($this->busbars) ?? null,
        ];
    }
}
