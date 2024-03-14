<?php
class MyRedis
{

    protected $options = [];
    protected $handler; // Declare the $handler property

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($idx = 0, $opt = [])
    {
        if (is_array($idx)) {
            $options = $idx;
        } else {
            $options = $_ENV['REDIS'][$idx];
        }
        if (is_array($opt) && $opt) {
            $options = array_merge($options, $opt);
        }
        if (!extension_loaded('redis')) {
            exit('not support: redis');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (!extension_loaded('redis')) {
            exit('not support: redis');
        }

        $this->handler = new Redis();
        if ($this->options['persistent']) {
            $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
    }

    public function lpush($key, $lisat_data)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->lpush($key, $lisat_data);
    }

    public function rpush($key, $lisat_data)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->rpush($key, $lisat_data);
    }

    public function lpop($key)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->lpop($key);
    }

    public function rpop($key)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->rpop($key);
    }

    public function hget($key, $field)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->hGet($key, $field);
    }

    public function hset($key, $field, $value)
    {
        $key = $this->getCacheKey($key);
        return $this->handler->hSet($key, $field, $value);
    }

    public function hmget($key, $fields = [])
    {
        $key = $this->getCacheKey($key);
        return $this->handler->hMGet($key, $fields);
    }

    public function hmset($key, $values = [])
    {
        $key = $this->getCacheKey($key);
        return $this->handler->hMSet($key, $values);
    }

    public function hgetAll($key)
    {
        $key = $this->getCacheKey($key);
        $result = $this->handler->hGetAll($key);
        if (!is_array($result)) {
            $result = unserialize($result);
        }
        return $result;
    }


    //通配符删除缓存  如：userinfo_  就会删除对应的缓存
    public function rmall($prefix)
    {
        $pattern = $this->getCacheKey($prefix) . '*';
        $cursor = null;
        $keysDeleted = 0;
        do {
            // SCAN 命令用于迭代数据库中的数据库键。
            $keys = $this->handler->scan($cursor, $pattern, 100); // 一次扫描100匹，可以根据实际情况调整
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $this->handler->del($key);
                    $keysDeleted++;
                }
            }
        } while ($cursor !== 0); // 当返回的游标为 0 时，表示迭代已经结束。

        return $keysDeleted; // 返回删除键的数量，方便进行检测
    }



    //关闭连接
    public function close()
    {
        return $this->handler->close();
    }

    //检测连接是否有效
    public function isConnect()
    {
        if (!$this->handler) {
            return false;
        }
        if ($this->handler->ping() != '+PONG') {
            return false;
        }
        return true;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->handler->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $value = $this->handler->get($this->getCacheKey($name));
        if (is_null($value) || false === $value) {
            return $default;
        }
        try {
            $result = 0 === strpos($value, 'serialize:') ? unserialize(substr($value, 10)) : $value;
        } catch (\Exception $e) {
            $result = $default;
        }

        return $result;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）默认1小时
     * @return boolean
     */
    public function set($name, $value, $expire = 60 * 60, $tag = null)
    {
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        $key = $this->getCacheKey($name);
        $value = is_scalar($value) ? $value : 'serialize:' . serialize($value);
        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }
        if ($tag) {
            $this->setTagItem($tag, $name); //存储原始key
        }
        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);

        return $this->handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        if (method_exists($this->handler, 'del')) {
            return $this->handler->del($this->getCacheKey($name));
        }
        return $this->handler->delete($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            //指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->rm($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return $this->handler->flushDB();
    }

    protected function getTagItem($tag)
    {
        $key = 'tag_' . md5($tag);
        $value = $this->get($key);
        if ($value) {
            return array_filter(explode(',', $value));
        } else {
            return [];
        }
    }

    protected function setTagItem($tag, $name)
    {
        if ($tag) {
            $key = 'tag_' . md5($tag);
            if ($this->has($key)) {
                $value = explode(',', $this->get($key));
                $value[] = $name;
                $value = implode(',', array_unique($value));
            } else {
                $value = $name;
            }
            $this->set($key, $value, 0);
        }
    }

    protected function getCacheKey($name)
    {
        return $this->options['prefix'] . $name;
    }
}
