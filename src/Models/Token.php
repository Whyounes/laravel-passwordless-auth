<?php

namespace Whyounes\Passwordless\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    protected $table = "user_tokens";

    protected $fillable = [
        'token',
        'created_at',
        'user_id',
    ];

    protected $dates = ['created_at'];

    /**
     * Is not used and not expired.
     *
     * @return bool
     */
    public function isValid()
    {
        return ! $this->isExpired();
    }

    /**
     * Is token expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->created_at
            ->diffInMinutes(Carbon::now()) > (int)config("passwordless.expire_in");
    }

    /**
     * Ignore the updated_at column.
     *
     * @param mixed $value Update date
     *
     * @return null
     */
    public function setUpdatedAt($value)
    {
    }


    /**
     * Token belongs to auth user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config("auth.providers.users.model"));
    }
}
