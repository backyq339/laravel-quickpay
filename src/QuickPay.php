<?php

namespace nickknissen\QuickPay;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

use QuickPay\QuickPay as QuickPayVendor;
use nickknissen\QuickPay\Exceptions\QuickPayValidationError;
use nickknissen\QuickPay\Exceptions\QuickPayTestNotAllowed;
use nickknissen\QuickPay\Exceptions\ConfigNotCorrect;
use nickknissen\QuickPay\Exceptions\CardNotAccepted;


class Quickpay
{

    protected $client;

    protected $currency = 'DKK';
    protected $payment_methods = '3d,creditcard';
    protected $callback_url;
    protected $continue_url;

    public function __construct()
    {
        $credentials = null;

        $this->callback_url = route('quickpay.callback');
        $this->continue_url = route('/');

        if (config('quickpay.api_key')) {
            $credentials = ":".config('quickpay.api_key');
        } else if (config('quickpay.login') && config('quickpay.password')) {
            $credentials = sprintf('%s:%s', config('quickpay.login'), config('quickpay.password'));
        }

        if (!$credentials) {
            throw new ConfigNotCorrect('You should specify an `api_key` or `login` and `password` in the `quickpay` config file');
        }

        $this->client = new QuickPayVendor($credentials);
    }

    public function request(string $method, string $url, array $data = []) : object
    {
        $response = $this->client->request->$method($url, $data);

        if ($response->isSuccess()) {
            $data = $response->asObject();

            if (App::environment('production') && $data->test_mode) {
                throw new QuickPayTestNotAllowed();
            }

            if (!empty($data->operations) && !$data->accepted) {
                throw new CardNotAccepted($data);
            }

            return $data;
        } else {
            throw new QuickPayValidationError($response);
        }
    }

    public function orderIdPrefix() : string
    {
        if (!Str::contains(Str::lower(config('app.env')), 'prod')) {
            return '';
        }

        return  'E' . mb_substr(config('app.env'), 0, 1);
    }
}
