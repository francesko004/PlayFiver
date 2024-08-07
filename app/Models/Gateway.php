<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Gateway extends Model
{
    use HasFactory;

    /*** The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gateways';

    /*** The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        // Suitpay
        'suitpay_uri',
        'suitpay_cliente_id',
        'suitpay_cliente_secret',

        // digitopay
        'digitopay_uri',
        'digitopay_cliente_id',
        'digitopay_cliente_secret',

        //EzzePay
        'ezze_uri',
        'ezze_client',
        'ezze_secret',
        'ezze_user',
        'ezze_senha'
    ];

    protected $hidden = array('updated_at');

    /*** Get the user's first name.
     */
    protected function suitpayClienteId(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }

    /*** Get the user's first name.
     */
    protected function suitpayClienteSecret(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => env('APP_DEMO') ? '*********************' : $value,
        );
    }
}
