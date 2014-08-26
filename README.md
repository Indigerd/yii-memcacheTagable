yii-memcacheTagable
===================

yii-memcacheTagable is Yii extension that provide tags support for memcache

##Installation and usage

1) clone the repository to YOUR_EXTENSIONS_DIR/memcacheTagable
2) modify your config file to enable extension
for example

```
'components'=>array(
        ......
        'cache' => array(
            'class' => 'ext.memcacheTagable.CMemCacheTagable',
            'servers' => array(
                ...
            )
        )
        .....
),
```

After that you can use it almost like Yii memcache component. The difference is in signature of set() method which now supports setting tags for cache value, you can pass string for single tag or array if you are assigning multiple tags for value.

```
set($id, $value, $expire = 0, $tags = null, $dependency=null)
```

Also there are several new method available

```
setMany(array $data, $expire = 0, $tags = null)
getByTag($tags)
deleteByTag($tags)
```