## 介紹

廣匯付 PHP 版本封裝。

## 安裝

使用 Composer 安裝。

```
composer require jetfueltw/ghfpay-php
```

## 使用方法

### 掃碼支付下單

使用QQ錢包、銀聯掃碼支付，下單後返回支付網址，請自行轉為 QR Code。

```
$merchantId = 'XXXXXXXXXXXXXXX'; // 商家號
$secretKey = 'XXXXXXXXXXXXXXX'; // SHA 密鑰
$tradeNo = '20180109023351XXXXX'; // 商家產生的唯一訂單號
$channel = Channel::WECHAT; // 支付通道，支援微信支付、QQ錢包、支付寶
$amount = 1.00; // 消費金額 (元)
$notifyUrl = 'https://XXX.XXX.XXX'; // 交易完成後異步通知接口
$returnUrl = 'https://XXX.XXX.XXX'; // 交易完成後會跳轉到這個頁面
```
```
$payment = new DigitalPayment($merchantId, $secretKey);
$result = $payment->order($tradeNo, $channel, $amount, $notifyUrl, $returnUrl);
```
```
Result:
[
    'codeUrl' =>'weixin://wxpay/bizpayurl?pr=XXXXXXX', // 支付網址
    'respCode' =>'S0001', // S0001 表示請求成功
    'respMessage' =>'XXXXX', // 如果請求失敗,會有此欄位敘述錯誤訊息
];
```

### 掃碼支付交易成功通知

消費者支付成功後，平台會發出 HTTP POST 請求到你下單時填的 $notifyUrl，商家在收到通知並處理完後必須回應 `success`，否則平台會認為通知失敗，並且平臺會在交易產生後的 24 小時內返回支付訂單信息，返回的時間頻率會逐漸減弱，（1 分鐘、3 分鐘、5 分鐘、10 分鐘、15...）。

* 商家必需正確處理重複通知的情況。
* 能使用 `NotifyWebhook@successNotifyResponse` 返回成功回應。  
* 務必使用 `NotifyWebhook@verifyNotifyPayload` 驗證簽證是否正確。
* 通知的消費金額單位為 `元`。 

```
Post Data: （並非每個欄位都會回傳）
[
    'body' => 'GOODS_BODY', // 商品描述
    'buyer_email' => 'XXXXXXXXXXXXX', // 買家email
    'buyer_id' => 'XXXXXXXXXXXX', // 買家ID
    'discount' => '0.00', // 折扣
    'ext_param1' => 'XXXXXXX', // 預留
    'ext_param2' => 'XXXXXXX', // 當支付通道為支付寶、QQ等時，異步通知返回擴展字段　ext_param2，賦值為相應的通道編碼，如：ext_param2=ALIPAY
    'gmt_create' => '2018-01-16 16:13:29', // 交易創建時間
    'gmt_logistics_modify' => '2018-01-16 16:23:29', // 訂單更改時間
    'gmt_payment' => '2018-01-16 19:13:29', // 交易付款時間
    'is_success' => 'X', // 通訊狀態：T 表示成功、F 表示失敗
    'is_total_fee_adjust' => 5.00, // 總費用，預留
    'notify_id' => 'XXXXXXXX', // 通知流水號
    'notify_time' => '2018-01-16 20:13:29', // 通知時間
    'notify_type' => 'WAIT_TRIGGER',
    'order_no' => '20180109023351XXXXX', // 商户订单号
    'payment_type' => '1', // 固定值
    'price' => '0.00', // 單價，預留值
    'quantity' => '1', // 數量，預留值
    'seller_actions' => 'SEND_GOODS', // 固定值
    'seller_email' => 'XXXXXX', // 賣家 email
    'seller_id' => 'XXXXX', // 賣家 id
    'title' => 'GOODS_NAME', // 商品名稱
    'total_fee' => 1.00, // 交易金額，單位為元
    'trade_no' => 'XXXXXXXXXXXXXX', // 平台交易流水號
    'trade_status' => 'TRADE_FINISHED', // 成功狀態
    'use_coupon' => 'X', // 是否使用優惠券
    'sign' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', // 簽名（字母大寫）
    'signType' => 'SHA', // 簽名類型
]
```

### 掃碼支付訂單查詢

使用商家訂單號查詢單筆訂單狀態。

```
$merchantId = 'XXXXXXXXXXXXXXX'; // 商家號
$secretKey = 'XXXXXXXXXXXXXXX'; // SHA 密鑰
$tradeNo = '20180109023351XXXXX'; // 商家產生的唯一訂單號
```
```
$tradeQuery = new TradeQuery(merchantId, secretKey);
$result = $tradeQuery->find($tradeNo);
```
```
Result:
[
    'feeAmount' => 0.00, // 手續費
    'amount' => 1.00, // 交易金額，元
    'tradeNo' => 'XXXXXXXXXXXXXXX', // 平台交易號
    'subject' => 'GOODS_NAME', // 商品名稱
    'outTradeNo' => '20180109023351XXXXX', // 商家產生的唯一訂單號
    'createdTime' => '2018-01-16 16:13:29', // 訂單創建時間
    'tradeDate' => '20180116', // 交易日期
    'tradeType' => 'payment', // 交易類型：固定值，payment 表示支付
    'timestamp' => '2018-01-16 16:13:30', // 時間
    'status' => 'XXXX', // 支付狀態：wait 等待支付，completed 支付成功，failed 支付失败
    'respCode' =>'S0001', // S0001 表示請求成功
    'respMessage' =>'XXXXX', // 如果請求失敗，會有此欄位敘述錯誤訊息 
]    
```

### 掃碼支付訂單支付成功查詢

使用商家訂單號查詢單筆訂單是否支付成功。

```
$merchantId = 'XXXXXXXXXXXXXXX'; // 商家號
$secretKey = 'XXXXXXXXXXXXXXX'; // SHA 密鑰
$tradeNo = '20180109023351XXXXX'; // 商家產生的唯一訂單號
```
```
$tradeQuery = new TradeQuery($merchantId, $secretKey);
$result = $tradeQuery->isPaid($tradeNo);
```
```
Result:
bool(true|false)
```   

### 網銀支付下單

使用網路銀行支付，下單後返回跳轉頁面，請 render 到客戶端。

```
$merchantId = 'XXXXXXXXXXXXXXX'; // 商家號
$secretKey = 'XXXXXXXXXXXXXXX'; // SHA 密鑰
$tradeNo = '20180109023351XXXXX'; // 商家產生的唯一訂單號
$bank = Bank::CCB; // 銀行編號
$amount = 1.00; // 消費金額 (元)
$notifyUrl = 'https://XXX.XXX.XXX'; // 交易完成後異步通知接口
$returnUrl = 'https://XXX.XXX.XXX'; // 交易完成後會跳轉到這個頁面
```
```
$payment = new BankPayment($merchantId, $secretKey);
$result = $payment->order($tradeNo, $bank, $amount, $notifyUrl, $returnUrl);
```
```
Result:
跳轉用的 HTML，請 render 到客戶端
```

### 網銀支付交易成功通知

同掃碼支付交易成功通知

### 網銀支付訂單查詢

同掃碼支付訂單查詢

### 網銀支付訂單支付成功查詢

同掃碼支付訂單支付成功查詢 