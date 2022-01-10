# 欢拓云直播WEB API

![](https://open.talk-fun.com/docs/getstartV2/image/logo.png)

## 安装

```
composer require oiuv/talkfun-sdk
```

## 使用

### 通过composer自动加载

```php
require __DIR__ . '/vendor/autoload.php';

use Oiuv\TalkFunSdk\MTCloud;

$config = [
    'openID' => '***',
    'openToken' => '***',
];

$MTCloud = new MTCloud($config);

// 获取房间列表
$res = $MTCloud->roomList();
```

### 在Laravel框架中使用

在`.env`中增加以下配置：

    TALKFUN_OPENID=XXXXX
    TALKFUN_TOKEN=XXXXXX

在`config/services.php`中增加以下配置：

```php
    'talkfun' => [
        'openID' => env('TALKFUN_OPENID'),
        'openToken' => env('TALKFUN_TOKEN'),
    ],
```

方法参数注入的方式调用:

```php
use Oiuv\TalkFunSdk\MTCloud;

public function show(MTCloud $talkfun)
{
    // 获取房间列表
    return $talkfun->roomList();
}
```

使用Facade(名称：`TalkFun`)的方式调用

```php
public function show()
{
    // 获取房间列表
    return TalkFun::roomList();
}
```


### 示例

```php
// 获取房间列表
$res = $MTCloud->roomList();

// 获取剪辑列表
$res = $MTCloud->clipList();

// 获取最新的几个直播记录
$res = $MTCloud->liveGetLast();

// 根据房间ID获取主播登录地址
$res = $MTCloud->roomLogin($roomid);

// 获取一个直播专辑
$res = $MTCloud->albumGet($albumid);

// 获取剪辑信息
$res = $MTCloud->clipGet($clipid);

// 获取某场直播的记录信息及回放地址
$res = $MTCloud->liveGet($liveid);

// 根据直播id获取回放视频
$res = $MTCloud->livePlaybackVideo($liveid);

```

> 更多方法请见接口文档：https://api.oiuv.cn/MTCloud/Oiuv/TalkFunSdk/MTCloud.html

## 接口文档

 * [欢拓云直播服务端API方法列表](https://api.oiuv.cn/MTCloud)
 * [欢拓云直播服务端API接口文档](https://open.talk-fun.com/docs/getstartV2/api/backend_api.html)
