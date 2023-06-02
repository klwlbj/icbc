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
            'generate'    => $domain . '/api/cardbusiness/qrcode/qrgenerate/V3',
            'pay'         => $domain . '/api/qrcode/V2/pay',
            'query'       => $domain . '/api/qrcode/V2/query',
            'reject'      => $domain . '/api/qrcode/V2/reject',
            'reverse'     => $domain . '/api/qrcode/V2/reverse',
            'rejectQuery' => $domain . '/api/qrcode/reject/query/V3',
        ],
    ],
];
