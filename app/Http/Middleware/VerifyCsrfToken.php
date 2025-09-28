<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'login','adminisp/login', 'bkash/webhook' , 'bkash/test',
         'client/rocket-bill-pay/paymentValidation',
        'client/rocket-bill-pay/paymentConfirmation',
        'client/rocket-bill-pay/getPaymentStatus',
    ];
}
