<?php

namespace App\Models\Seller;

use App\Models\User\User;
use App\Models\Seller\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seller extends Model
{

    protected $fillable = [
        'user_id',
        'account_type',
        'account_status',
        'dob',
        'whatsapp_no',
        'store_name',
        'business_description',
        'identity_type',
        'proof_of_identity',
        'brand_name',
        'brand_logo',
    ];

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_BLOCKED  = 'blocked';

    // scopes
    public function scopePending($q)   { return $q->where('account_status','pending'); }
    public function scopeApproved($q)  { return $q->where('account_status','approved'); }
    public function scopeBlocked($q)   { return $q->where('account_status','blocked'); }


    // state-transition helpers
    public function approve()
    {
        $this->update(['account_status'=>'approved']);
        // optionally fire event: SellerApproved…
    }

    public function block(string $reason=null)
    {
        $this->update(['account_status'=>'block']);
        // notify user…
    }


    /**
     * Get the user that owns the Seller
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
