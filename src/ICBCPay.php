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

use Faker\Core\Uuid;
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

    private DefaultIcbcClient $client;

    private Uuid $uuidGenerator;

    public function __construct($appId = null, $mer_id = null, $store_code = null, $privateKey = null, $icbcPulicKey = null, $signType = null, $encryptKey = null, $encryptType = null, $charset = null, $format = null, $ca = '', $password = null)
    {
        $this->appId        = $appId ?? config('icbc.appId', '');
        $this->mer_id       = $mer_id ?? config('icbc.mer_id', '');
        $this->store_code   = $store_code ?? config('icbc.store_code', '');
        $this->privateKey   = $privateKey ?? config('icbc.privateKey', '');
        $this->icbcPulicKey = $icbcPulicKey ?? config('icbc.icbcPulicKey', '');
        $this->encryptKey   = $encryptKey ?? config('icbc.encryptKey', '');
        $this->encryptType  = $encryptType ?? config('icbc.encryptType', '');
        $this->password     = $password ?? config('icbc.password', '');
        $this->signType     = $signType ?? config('icbc.signType', IcbcConstants::$SIGN_TYPE_RSA2);
        $this->charset      = $charset ?? config('icbc.charset', IcbcConstants::$CHARSET_UTF8);
        $this->format       = $format ?? config('icbc.format', IcbcConstants::$FORMAT_JSON);
        $this->ca           = preg_replace("/\s*|\t/", "", $ca) ?? '';

        $this->uuidGenerator = new Uuid();

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
        array | Collection &$params,
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
        $this->validateParams($params, config('icbc.rules.qrcode.' . __FUNCTION__));

        $params['mer_id']     = $this->mer_id;
        $params['store_code'] = $this->store_code;
        $request              = [
            "serviceUrl"    => config('icbc.url.qrcode.' . __FUNCTION__),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => $params,
        ];

        $msgId = $this->uuidGenerator->uuid3();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码退款
     * @param array $params
     * @return mixed
     * @throws ValidationException
     */
    public function reject(array $params = [])
    {
        // 检测参数格式
        $this->validateParams($params, config('icbc.rules.qrcode.' . __FUNCTION__));

        $params['mer_id'] = $this->mer_id;
        $request          = [
            "serviceUrl"    => config('icbc.url.qrcode.reject' . __FUNCTION__),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => $params,
        ];
        $msgId = $this->uuidGenerator->uuid3();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码查询
     * @param array $params
     * @return mixed
     * @throws ValidationException
     */
    public function query(array $params = [])
    {
        // 检测参数格式
        $this->validateParams($params, config('icbc.rules.qrcode.' . __FUNCTION__));

        $params['mer_id'] = $this->mer_id;
        $request          = [
            "serviceUrl"    => config('icbc.url.qrcode.query' . __FUNCTION__),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => $params,
        ];
        $msgId = $this->uuidGenerator->uuid3();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }

    /**
     * 二维码被扫支付
     * @param array $params
     * @return mixed
     * @throws ValidationException
     */
    public function pay(array $params = [])
    {
        // 检测参数格式
        $this->validateParams($params, config('icbc.rules.qrcode.' . __FUNCTION__));

        $params['mer_id'] = $this->mer_id;
        $request          = [
            "serviceUrl"    => config('icbc.url.qrcode.' . __FUNCTION__),
            "method"        => 'POST',
            "isNeedEncrypt" => false,
            "biz_content"   => $params,
        ];
        $msgId = $this->uuidGenerator->uuid3();
        $resp  = $this->client->execute($request, $msgId, '');
        return json_decode($resp, true);
    }
}
