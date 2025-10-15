<?php

namespace App\Observers;

use App\Models\Coupon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class CouponObserver
{
    /**
     * Handle the Coupon "created" event.
     */
    public function created(Coupon $coupon)
    {
        // Generar un GUID
        $guid = Str::uuid()->toString();
        // Generar el QR Code basado en el ID o alguna otra información del modelo
        $qrContent = route('coupon.infoQr', ['id' => $coupon->id, 'guid' => $guid]);
        $qrCode = QrCode::format('svg')->size(300)->generate($qrContent);
        // Actualizar el modelo con la ruta del QR Code
        $coupon->update([
            'qrCode' => $qrCode,
            'guid' => $guid,
        ]);
    }

    /**
     * Handle the Coupon "updated" event.
     */
    public function updated(Coupon $Coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "deleted" event.
     */
    public function deleted(Coupon $Coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "restored" event.
     */
    public function restored(Coupon $Coupon): void
    {
        //
    }

    /**
     * Handle the Coupon "force deleted" event.
     */
    public function forceDeleted(Coupon $Coupon): void
    {
        //
    }
}
