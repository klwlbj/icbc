<?php
/**
 * description:
 * datetime: 2023/6/1 12:26
 * author: klwlbj
 * version: 1.0
 */

$productionDomain = "https://gw.open.icbc.com.cn";
$testDomain       = 'http://122.19.77.226:8081';

$domain = config('app.env') === 'local' ? $testDomain : $productionDomain;

return [
    // app id
    'appId'        => '',

    //商户线下档案编号(特约商户12位，特约部门15位)
    'mer_id'       => '',

    //e生活档案编号
    'store_code'   => '',

    // APP应用私钥
    "privateKey"   => "",

    // 网关公钥
    'icbcPulicKey' => "",

    // AES加密密钥，缺省为空''
    "encryptKey"   => "",

    // 签名方式
    "signType"     => "RSA",

    // 将需要用到的接口及对应地址放在这里 小写驼峰=>url
    'url'          => [
        'qrcode' => [
            'generate'  => $domain . '/api/cardbusiness/qrcode/qrgenerate/V3',
            'pay'       => $domain . '/api/mybank/pay/qrcode/scanned/pay/V5',
            'paystatus' => $domain . '/api/mybank/pay/qrcode/scanned/paystatus/V4',
            'reject'    => $domain . '/api/mybank/pay/qrcode/scanned/return/V2',
        ],
    ],
    'rules'        => [
        'qrcode' => [
            'generate'  => [
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
            ],
            'pay'       => [
                'qr_code'      => 'required|string|size:19',
                'out_trade_no' => 'required|string|size:35',
                // 'trade_date' => 'required|string|size:19',// 暂不使用
                // 'trade_time' => 'required|string|size:19',// 暂不使用
                'attach'       => 'string',
                'order_amt'    => 'required|int|max:99999999999999999',
            ],
            'reject'    => [
                'out_trade_no' => 'required|string|size:35',
                "reject_no"    => "'required_without:order_id',",
                "order_id"     => "'required_without:reject_no',",
                'reject_amt'   => 'required|int|max:99999999999999999',
                "oper_id"      => 'required|string|size:3',
            ],
            'paystatus' => [
                'out_trade_no' => 'required_without:order_id|string|size:35',
                "order_id"     => 'required_without:out_trade_no',
                'deal_flag'    => 'int|in:0,1',
            ],
        ],
    ],
];
