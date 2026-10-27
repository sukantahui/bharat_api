<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'trade_name',
        'legal_name',
        'tagline',
        'is_gst_registered',
        'gst_scheme',
        'gstin',
        'pan_number',
        'hallmark_license_no',
        'phone',
        'email',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'district_id',
        'state_id',
        'state_code',
        'state_name',
        'pincode',
        'currency_symbol',
        'invoice_prefix',
        'purchase_prefix',
        'stock_entry_prefix',
        'voucher_prefix',
        'financial_year_start',
        'terms_and_conditions',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'upi_id',
        'traded_metals',
    ];

    protected function casts(): array
    {
        return [
            'is_gst_registered'    => 'boolean',
            'state_code'           => 'integer',
            'financial_year_start' => 'date',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
