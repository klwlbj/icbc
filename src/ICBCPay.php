<?php
/**
 * description:
 * datetime: 2019/7/2 11:27
 * author: fa
 * version: 1.0
 * This file is part of fatryst/icbc.
 *
 * (c) fa <zengfa@hotmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klwlbj\Icbc;

use Faker\Provider\Uuid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Klwlbj\Icbc\encryption\IcbcConstants;
use Klwlbj\Icbc\encryption\DefaultIcbcClient;
use Illuminate\Validation\ValidationException;

class ICBCPay
{
    private string $appId;
    private string $privateKey;
    private string $signType;
    private string $charset;
    private string $format;
    private string $icbcPulicKey;
    private string $encryptKey;
    private string $encryptType;
    private string $ca;
    private string $password;
    private string $mer_id;
    private string $store_code;
    private array $generateRule = [
        'mer_id'                    => 'required|string|size:15',
        'out_trade_no'              => 'required|string|size:35',
        'order_amt'                 => 'required|integer|min:0|max:99999999999999999',
        'trade_date'                => 'required|date_format:Ymd',
        'trade_time'                => 'required|date_format:H:i:s',
        'pay_expire'                => 'required|int|min:0|max:3600',
        'notify_flag'               => 'required|int|between:0,1',
        'tporder_create_ip'         => 'required|ip',

        'terminal_info'             => 'required|array',
        'terminal_info.device_type' => 'required|int',
        'terminal_info.device_id'   => 'required|string',
    ];
    private DefaultIcbcClient $client;

    public function __construct($appId = null, $mer_id = null, $store_code = null, $privateKey = null, $icbcPulicKey = null, $signType = null, $encryptKey = null, $encryptType = null, $charset = null, $format = null, $ca = '', $password = null)
    {
        $this->appId        = $appId ?? config('icbc.appId', '');
        $this->mer_id       = $mer_id ?? config('icbc.mer_id', '');
        $this->store_code   = $store_code ?? config('icbc.store_code', '');
        $this->privateKey   = $privateKey ?? config('icbc.privateKey', '');
        $this->icbcPulicKey = $icbcPulicKey ?? config('icbc.icbcPulicKey', '');
        $this->encryptKey   = $encryptKey ?? config('icbc.encryptKey', '');
        $this->encryptType  = $icbcPulicKey ?? config('icbc.encryptType', '');
        $this->password     = $icbcPulicKey ?? config('icbc.password', '');
        $this->signType     = $icbcPulicKey ?? config('icbc.signType', IcbcConstants::$SIGN_TYPE_RSA2);
        $this->charset      = $icbcPulicKey ?? config('icbc.charset', IcbcConstants::$CHARSET_UTF8);
        $this->format       = $icbcPulicKey ?? config('icbc.format', IcbcConstants::$FORMAT_JSON);
        $this->ca           = preg_replace("/\s*|\t/", "", $ca) ?? '';

        $this->client = new DefaultIcbcClient($this->appId, $this->privateKey, $this->signType, $this->charset, $this->format, $this->icbcPulicKey, $this->encryptKey, $this->encryptType, $this->ca, $this->password);
    }

    public function test(): string
    {
        return 'test';
    }

    /**
     * @param array|Collection $params
     * @param array $rules
     * @param array $message
     * @return void
     * @throws ValidationException
     */
    public function validateParams(
        array | Collection $params,
        array $rules = [],
    ) {
        if ($params instanceof Collection) {
            $params = $params->toArray();
        }

        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * 二维码生成
     * @param array $params
     * @return mixed
     * @throws ValidationException
     */
    public function generate(array $params = [])
    {
        // 检测参数格式
        $this->validateParams($params, $this->generateRule);

        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.generate'),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "mer_id"            => $this->mer_id,
                "out_trade_no"      => $params['out_trade_no'],
                "order_amt"         => $params['order_amt'],
                "trade_date"        => $params['trade_date'],
                "trade_time"        => $params['trade_time'],
                "pay_expire"        => $params['pay_expire'],
                "notify_flag"       => $params['notify_flag'],
                "terminal_info"     => $params['terminal_info'],
                "tporder_create_ip" => $params['tporder_create_ip'],

                "store_code"        => $this->store_code,
            ],
        ];

        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码退款
     * @param $reject_no
     * @param $reject_amt
     * @param null $cust_id
     * @param null $out_trade_no
     * @param null $order_id
     * @param null $oper_id
     * @return mixed
     * @throws \Exception
     */
    public function reject($reject_no, $reject_amt, $cust_id = null, $out_trade_no = null, $order_id = null, $oper_id = null)
    {
        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.reject', null),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "mer_id"       => $this->mer_id,
                "reject_no"    => $reject_no,
                "reject_amt"   => $reject_amt,
                "cust_id"      => $cust_id,
                "out_trade_no" => $out_trade_no,    //该字段非必输项,out_trade_no和order_id选一项上送即可
                "order_id"     => $order_id,            //该字段非必输项,out_trade_no和order_id选一项上送即可
                "oper_id"      => $oper_id,
            ],
        ];
        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码查询
     * @param null $cust_id
     * @param null $out_trade_no
     * @param null $order_id
     * @return mixed
     * @throws \Exception
     */
    public function query($cust_id = null, $out_trade_no = null, $order_id = null)
    {
        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.query', null),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "mer_id"       => $this->mer_id,
                "cust_id"      => $cust_id,              //该字段非必输项
                "out_trade_no" => $out_trade_no,    //该字段非必输项,out_trade_no和order_id选一项上送即可
                "order_id"     => $order_id,            //该字段非必输项,out_trade_no和order_id选一项上送即可
            ],
        ];
        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码被扫支付
     * @param $qr_code
     * @param $out_trade_no
     * @param $order_amt
     * @param $trade_date
     * @param $trade_time
     * @return mixed
     * @throws \Exception
     */
    public function pay($qr_code, $out_trade_no, $order_amt, $trade_date, $trade_time)
    {
        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.pay', null),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "qr_code"      => $qr_code,
                "mer_id"       => $this->mer_id,
                "out_trade_no" => $out_trade_no,
                "order_amt"    => $order_amt,
                "trade_date"   => $trade_date,
                "trade_time"   => $trade_time,
            ],
        ];
        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码冲正
     * @param $out_trade_no
     * @param null $cust_id
     * @param null $order_id
     * @param null $reject_no
     * @param null $reject_amt
     * @param null $oper_id
     * @return mixed
     * @throws \Exception
     */
    public function reverse(string $out_trade_no, $cust_id = null, $order_id = null, $reject_no = null, $reject_amt = null, $oper_id = null)
    {
        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.reverse', null),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "mer_id"       => $this->mer_id,
                "out_trade_no" => $out_trade_no,
                "cust_id"      => $cust_id,              //该字段非必输项
                "order_id"     => $order_id,            //该字段非必输项
                "reject_no"    => $reject_no,          //该字段非必输项
                "reject_amt"   => $reject_amt,        //该字段非必输项
                "oper_id"      => $oper_id,               //该字段非必输项
            ],
        ];
        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码退款查询
     * @param $out_trade_no
     * @param $order_id
     * @param $reject_no
     * @param null $cust_id
     * @return mixed
     * @throws \Exception
     */
    public function rejectQuery($out_trade_no, $order_id, $reject_no, $cust_id = null)
    {
        $request = [
            "serviceUrl"    => config('icbc.url.qrcode.reject_query', null),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => [
                "mer_id"       => $this->mer_id,
                "out_trade_no" => $out_trade_no,
                "order_id"     => $order_id,
                "reject_no"    => $reject_no,
                "cust_id"      => $cust_id,
            ],
        ];
        $msgId = Uuid::uuid();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }
}
