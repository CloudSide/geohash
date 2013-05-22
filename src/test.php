<?php

/**
 * Geohash generation class for php - test
 *
 * This file copyright (C) 2013 Bruce Chen (http://weibo.com/smcz)
 *
 * Author: Bruce Chen (weibo: @一个开发者)
 *
 */


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

