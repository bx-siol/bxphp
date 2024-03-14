<?php
//####################相关配置开始###########################
define('SYS_KEY', 'asfasvcv4856e13asd35a3v1a5dv485adcxx'); //全局签名密钥

$_ENV['PREFIX'] = 'bx'; //项目前/后缀

$_ENV['DB'] = [
	'default' => 'sys',
	'connections' => [
		'sys' => [
			'trigger_sql' => true,
			'debug' => false,
			'deploy' => 0,
			'type' => 'mysql',
			'hostname' => '127.0.0.1',
			'database' => 'bx',
			'username' => 'bx',
			'password' => 'tNh5Mx28nzsjJPeZ',
			'hostport' => '3306',
			'prefix' => '',
			'charset' => 'utf8mb4',
			'params' => (!PHP_CLI ? [] : [
				\PDO::ATTR_PERSISTENT => true
			]),
			'break_reconnect' => !PHP_CLI ? false : true
		],
		'logs' => [
			'trigger_sql' => true,
			'debug' => false,
			'deploy' => 0,
			'type' => 'mysql',
			'hostname' => '127.0.0.1',
			'database' => 'bx_logs_a',
			'username' => 'bx_logs_a',
			'password' => 'pATGkZpRWwADpswy',
			'hostport' => '3306',
			'prefix' => '',
			'charset' => 'utf8mb4',
			'params' => (!PHP_CLI ? [] : [
				\PDO::ATTR_PERSISTENT => true
			]),
			'break_reconnect' => !PHP_CLI ? false : true
		]
	]
];

$_ENV['REDIS'][0] = [
	'host' => '127.0.0.1',
	'port' => 63791,
	'password' => '',
	'select' => 1,
	'timeout' => 0,
	'expire' => 0,
	'persistent' => true,
	'prefix' => strtolower($_ENV['PREFIX']) . '_'
];

$_ENV['MEMCACHE'] = [
	'host' => '127.0.0.1:11211', //多台用逗号隔开 例如：127.0.0.1:11211,127.0.0.1:11212
	'expire' => 0,
	'timeout' => 0, // 超时时间（单位：毫秒）
	'persistent' => false,
	'prefix' => strtolower($_ENV['PREFIX']) . '_'
];


$_ENV['CONFIG'] = [];
$_ENV['CONFIG']['TOKEN_EXPIRE_TIME'] = 7200; //token有效时间 秒 0则不限
$_ENV['API_URL'] = 'http://38.55.214.59:740/';
$_ENV['cnf_domain'] = '38.55.214.59:740';
$_ENV['oauth_domain'] = '38.55.214.59:740';
//语言配置
$_ENV['LANG_ARR'] = [
	// 'zh-cn'=>'简体中文',
	// 'zh-tw'=>'繁體中文',
	//'pt-BR'=>'Português',
	//'sp-es'=>'español',
	'en-us' => 'English',
	// 'en-usydy'=>'हिन्दी',
	// 'en-uslgt'=>'తెలుగు',
	// 'en-us1'=>'Bangla'
];


$_ENV['rsa_pt_public'] = <<<EOD
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZV7BY1UPv+mfGyWLjmSzx22aD
9WhNA4rMAiZ0GMqtduyx8orZvlV5aiXEk7hfqzqPupJvFeanSDuJOz4oK+YrlYHK
a7v0bX/Z5RUbzFA1HmYsnj7CQci+XpyYfRrIiNdaWvN2I6o7iBHdcYBEoUjUYG/u
ncx1uisDKP5sT31tpQIDAQAB
-----END PUBLIC KEY-----
EOD;

$_ENV['rsa_pt_private'] = <<<EOD
-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBANlXsFjVQ+/6Z8bJ
YuOZLPHbZoP1aE0DiswCJnQYyq127LHyitm+VXlqJcSTuF+rOo+6km8V5qdIO4k7
Pigr5iuVgcpru/Rtf9nlFRvMUDUeZiyePsJByL5enJh9GsiI11pa83YjqjuIEd1x
gEShSNRgb+6dzHW6KwMo/mxPfW2lAgMBAAECgYEAtop6lzGPXMfAT2om/ydQY1W1
VVFRJ5W5Bj0sFMgZufUvOQmPau/8E/x9+gtjiB5lEgBOKrSHQ6pcLpcuTkkIh7dZ
oQbrjwthmncXBYGCXV3wcF5OYbYpfXAkkIF1qmJXAVrmb+JdR9cDZrxzyf8A8xzs
PNa57hXAbeTkBq6ShgUCQQD87u8jeplv2EPtssXu1PZGRfmWEC+PAtiXqCp0Pwo4
rNACPxewqkrPsE0WQ02xkuahZKZib/GykuXZJFEHxfJHAkEA2/pJUMhHSjShR15C
KVQ9J5BwNODaA0nkaViPlg31wuI5j4aa4SC80cKPTSOIBb82HEBWEFig4qiv9VAn
PU/KswJAUFRaIrx3KqxWtpFGh5mfNaZXpC8zy4mt3EyOXyj+SA2qamw7S+JprBhk
AyQQ8sqi8LMUnMLM0lgRI6uDCKL3VQJAR3etUh1/a9BlhXZOMHc51xzKF6PlsKPV
HZS0MaAfdE1jPgoRT4r/TXbUb89XZeaVgeA1FeJ1gq4jXKPJ29WMmQJBAJyKsjnZ
90zlx6sghn6x4ywycO6mABogZrpMhf1KAXmL79STn9mmdBbQ8TKuIhuaJSfQcoTL
wuPP4Bag90BAfa8=
-----END PRIVATE KEY-----
EOD;

//默认语言
$_ENV['LANG_DEF'] = 'en-us';

$_ENV['PAY_CONFIG']['nicepay'] = [
	'mch_id' => '202403664',
	'md5_key' => 'EWaRjklAIfGLutocvTxSezrOqwFDdpKZ',
	'ptkey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0+JGzjRdFIyVQj1xIyetE/n5YBjRXTIX5yVOE9MVObdYjE6Mow0yHVr24i81c4w3cpLRbPRaHcEIZud0qTnGh1tBU0qo7MqZh0FEb0sgoPpoHVTULevQmVRgA/iPI7u2KuXQ85AwiiiQzXaiCACAA1Bz/MhtIYYq5YY0wuSPUTYZ+9z+hsLnKbUxTBibIs0gSKfggCGDMzlLQ24t6y8L6Uvw8/+dasFjTdCxlvn8XTcCU9hM19brzenZCU/EuihiamL6FREV6UjMebH3ZeQoapfD2XexDLmeIb0PKS+1QSWoKxlm+PXULru84W0Jd8PV5nH9wgcuTtEPu4xrgpip7wIDAQAB',
	'skey' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDG7s7Jt7B/H8pg7wH1N2X6kc+S86bY7/I5PV4+1AeJeYL0wZUwycgWnExW4FPi7PcfmNQ7VrubhWlJ4zlxyuGlfKSVeBVOrYVA/uqCkZRu8qbg9eWTcseRuQMaGKsltohxEkwTS0iU+bGjsMO+KDod1cvBsAqbAWEpI6qkRhVOmHpzn0k0dpu6diKgMKkS/dt2gbaQIjNiGum5zpB9zHfHcZIn2tESf7wnRe5qcqNlqat2aXg2MlFpBZquyLba8wIDSxdRU/73b8T+72aVCMlZdHRwijGMrnFO1jInad4k3Zx+pEoPf1IecMvtmfIh22tgpZ5waqAbc8j73J1CxZrJAgMBAAECggEAfUKNbrJ9Le6kjdixEOirC9WYMF2/J0Ye7S/ZEhKMFGIwtVDXUfscRDByWRmzeGXhTUH8EMkSJGEi5OVwnFg1MPCE6aDNOddE2qIoo8JrBlk//tKWlftl8jne3CXATmTbEOeGg3eZ4kQ/OGTrO+c0gOjH/dONOgDqXu8YWYvPisaXmma9m/OVhHFnta2VKCv14fRyGHgH+f7Pf7hShw0rWctn+bfQ8KVsMLwITlVQxg3tsFVpq1VJ4UZeOvVT7i5PqnQZ3FYW3Tt7R9pDu4r1CTp3+2jg4BlQq/VLc8pk1rVNia33mLz4cb3jTiBcvLDbodv0mMNnnHt4CsD2T8YSoQKBgQDqr9HZgmXSmnL8JN5q4h8/itvrijU+9F8cPewiklyvrvtF3h/nu4DiUpcHeLMP+EScG6je6o1eLebEVJrY+9M2QxO8+o1ax1onl/ETULV0EWCv8XXIhuTytp3WJVSvML3qxXCtpa9jUfdTPR30o5565oPpIAjH5cfeZdY80BgnpQKBgQDY/8ChxGVrUC8lS9r0Mfqy6WmK+I/o3WgrC0063hNrS5mvYNJIY6xsiKmJJI8Bvp79xY9d2PH6xWX74ErhwhPDAuguDfhcxou0Vcy8XtLAqmiAgU7Fv69Cu93W3viUpkReY84fqnaUl9pg4TKO8yrCuhTdJj850ZM4+VoB/7vdVQKBgQDPIZKC+LXJpQtrQ1cS2rRdrr7fKkJIaAIxuO1arfckD7oyNhPOwQzFs8MckwjnRWW4qijnZfiTcMvF6C4q6EfIEnevSSvNPTlyzIW3WHpuEJBXeTNNYtFa7pZr1NLjNI/KT6xddmhPrp4tVznDG03ahu1RqjZVx5YZ0aO+VKOdCQKBgAOZYlmMh14bS1zkADKyrYTQKEb8zm4/Vd345wQF8O6Dp33oLjSzKoX2UlGLALA3uC1fXHxi/8h4A7QEyVkrPearIqy44JixVlncXnP1Yqt9aBRRkLvo7QA/W3OX6SmWBGZIY3/U9PiL+DxJH+yNDfWC3pu4aa4M0tNMF3kXA8etAoGBANTPabuz9R2QYZOrCd3SGjApKZ3Hx+9wAJR6bw1ajCc12ItOMu4vhxPJnGRAcPnsg7lG3arRznfK0VXCcrf5J1VU6njCbF+kz48/sVoAhlDeKjL/51oGLKW9B+QFQJWOHDAescwbVVkZXSJOfNCDv0ZA0lWgmUcjO4Jwgon/cVtR',

	'query_url' => '',
	'pay_url' => 'https://g.verynicepay.life/api/pay/query',
	'dpay_url' => 'https://g.verynicepay.life/api/pay/repay',

	'returnUrl' => 'https://' . PAY_BACKURL . '/',
	'notifyUrl' => 'https://' . PAY_BACKURL . '/' . 'api/Notify/nicepay/cash',
	'dnotify_url' => 'https://' . PAY_BACKURL . '/' . 'api/Notify/nicepay/cash'
];

$_ENV['PAY_CONFIG']['bobopay'] = [
	'mch_id' => 'TEST100',
	'mch_key' => 'DRNnGuqGrY',

	'pay_url' => 'https://api.bobopay.in/api/payin',
	'dpay_url' => 'https://api.bobopay.in/api/payout',
	'query_url' => '',

	'returnUrl' => 'https://' . PAY_BACKURL . '/',
	'notify_url' => 'https://' . PAY_BACKURL . '/api/Notify/bobopay/pay',
	'dnotify_url' => 'https://' . PAY_BACKURL . '/' . 'api/Notify/bobopay/cash'
];

$_ENV['PAY_CONFIG']['jwpay'] = [
	'mch_id' => '100',
	'mch_key' => 'nrCV5HpZ3FzvuX6L',

	'pay_url' => 'https://api.jwpay.net/api/payment/createOrder',
	'dpay_url' => 'https://api.jwpay.net/api/payout/createOrder',
	'balance_url' => 'https://api.jwpay.net/api/payout/balance',
	
	'query_url' => '',
	'returnUrl' => 'https://' . PAY_BACKURL . '/',
	'notify_url' => 'https://' . PAY_BACKURL . '/api/Notify/bobopay/pay',
	'dnotify_url' => 'https://' . PAY_BACKURL . '/' . 'api/Notify/bobopay/cash'
];
