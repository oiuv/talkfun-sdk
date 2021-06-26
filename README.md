# 欢拓云直播WEB API

## 安装

```
composer require oiuv/talkfun-sdk
```

## 使用

```php
use Oiuv\TalkFunSdk\MTCloud;

$config = [
    'openID' => '***',
    'openToken' => '***',
];

$MTCloud = new MTCloud($config);

//获取一个直播专辑
$res = $MTCloud->albumGet(123456);

//获取剪辑信息
$res = $MTCloud->clipGet(123456);

//获取剪辑列表
$res = $MTCloud->clipList();

//获取直播间地址
$res = $MTCloud->userAccessUrl(7300637,'雪风小哥哥','user',123456);

```
## 接口文档

 * [欢拓云直播服务端API方法列表](https://api.oiuv.cn/MTCloud)
 * [欢拓云直播服务端API接口文档](https://open.talk-fun.com/docs/getstartV2/api/backend_api.html)
