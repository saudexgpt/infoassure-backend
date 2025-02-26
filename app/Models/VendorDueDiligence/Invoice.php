<?php

namespace App\Models\VendorDueDiligence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Invoice extends Model
{
    use SoftDeletes;
    //
    protected $connection = 'vdd';
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
