geohash
============

这年头和LBS相关的应用越来越火. 从foursquare的热闹程度就可见一般, 更不用说微信、陌陌了 (什么, 没听过 foursquare... 哥们, 你 out 了). 和 LBS有关的应用一般都包括一些共同的操作, 最常见的一个, 就是找附近的东东（餐馆, 商店, 妞....）. 所以, 这里就抛出了一个问题, 怎样才能在大量经纬度数据中检索出附近的点呢?

geohash能做到  [@一个开发者](http://weibo.com/smcz)

[![](http://service.t.sina.com.cn/widget/qmd/1656360925/02781ba4/4.png)](http://weibo.com/smcz)

[![paypaldonate](https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VW7YCWNMQ7ZXY)

![](http://image.sinastorage.com/donate.png)


### Requirements

* PHP >= 4

### Usage


- 例如: 用iPhone/android手机定位得到理想国际大厦的经纬度: 39.98123848, 116.30683690 然后查找附近的妞 ![](http://www.sinaimg.cn/uc/myshow/blog/misc/gif/E___6715EN00SIGG.gif)

```php

require_once('geohash.class.php');
$geohash = new Geohash;
//得到这点的hash值
$hash = $geohash->encode(39.98123848, 116.30683690);
//取前缀，前缀约长范围越小
$prefix = substr($hash, 0, 6);
//取出相邻八个区域
$neighbors = $geohash->neighbors($prefix);
array_push($neighbors, $prefix);

print_r($neighbors);

```

- 得到9个geohash值

```php

//得到9个geohash值

Array
(
    [top] => wx4eqx
    [bottom] => wx4eqt
    [right] => wx4eqy
    [left] => wx4eqq
    [topleft] => wx4eqr
    [topright] => wx4eqz
    [bottomright] => wx4eqv
    [bottomleft] => wx4eqm
    [0] => wx4eqw
)

```

- 范围如图:
 
![](http://s15.sinaimg.cn/orignal/62ba0fddtab3b8381ce8e&690)



- 用sql语句查询

```sql
SELECT * FROM xy WHERE geohash LIKE 'wx4eqw%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqx%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqt%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqy%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqq%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqr%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqz%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqv%';
SELECT * FROM xy WHERE geohash LIKE 'wx4eqm%';
```

- 看一下是否用上索引 (一共有50多万行测试数据):

索引:

![](http://s15.sinaimg.cn/orignal/62ba0fddtab3b8463f9ce&690)

数据:

![](http://s1.sinaimg.cn/orignal/62ba0fddtab3b84d6c250&690)

```sql
EXPLAIN SELECT * FROM xy WHERE geohash LIKE 'wx4eqw%';
```

![](http://s8.sinaimg.cn/orignal/62ba0fddtab3b86ca9007&690)


其他资料：
- geohash演示:  http://openlocation.org/geohash/geohash-js/
- wiki: http://en.wikipedia.org/wiki/Geohash
- 原理: https://github.com/CloudSide/geohash/wiki


