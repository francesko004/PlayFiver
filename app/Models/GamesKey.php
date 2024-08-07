<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GamesKey extends Model
{
    use HasFactory;

    /*** The database table used by the model.
     *
     * @var string
     */
    protected $table = 'games_keys';

    /*** The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        // PlayFiver 
        'playfiver_url',
        'playfiver_rtp',
        'playfiver_secret',
        'playfiver_code',
        'playfiver_token'

    ];

    protected $hidden = array('updated_at');
    /*** Get the user's first name.
     */
    protected function venixAgentCode(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }

    /*** Get the user's first name.
     */
    protected function venixAgentToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }

    /*** Get the user's first name.
     */
    protected function venixAgentSecret(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }
}
