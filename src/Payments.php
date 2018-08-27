<?php

namespace nickknissen\QuickPay;

class Payments extends Quickpay
{

    public function find(int $paymentId)
    {
        $url = sprintf('/payments/%s', $paymentId);
        return $this->request('get', $url);
    }

    public function create($orderId, $options = [])
    {
        return $this->request('post', '/payments', array_merge([
            'order_id' => sprintf('%s%s', $this->orderIdPrefix(), $orderId),
            'currency' => $this->currency,
        ], $options));
    }

    public function authorize(int $paymentId, int $amount, Card $card, $options = [])
    {
        $url = sprintf('/payments/%s/authorize?synchronized', $paymentId);

        return $this->request('post', $url, array_merge([
            'amount' => $amount,
            'card' => $card->getInfo(),
        ], $options));

    }

    public function capture(int $paymentId, int $amount, $options = [])
    {
        $url = sprintf('/payments/%s/capture?synchronized', $paymentId);

        return $this->request('post', $url, array_merge([
            'amount' => $amount,
        ], $options));
    }

    public function cancel(int $paymentId)
    {
        $url = sprintf('/payments/%s/cancel', $paymentId);

        return $this->request('post', $url);
    }

    public function renew(int $paymentId)
    {
        $url = sprintf('/payments/%s/renew', $paymentId);

        return $this->request('post', $url);
    }
}
