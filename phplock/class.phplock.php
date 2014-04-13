<?php
/**
 * PHPLock进程锁
 * 本进程锁用来解决php在并发时候的锁控制
 * 他根据文件锁来模拟多个进程之间的锁定，效率不是非常高。如果文件建立在内存中，可以大大提高效率。
 * PHPLOCK在使用过程中，会在指定的目录产生$hashNum个文件用来产生对应粒度的锁。不同锁之间可以并行执行。
 * 这有点类似mysql的innodb的行级锁，不同行的更新可以并发的执行。
 */

class PHPLock {
	/**
	 * 锁文件路径
	 *
	 * @var String
	 */
	private $path = null;
	/**
	 * 文件句柄
	 *
	 * @var resource 
	 */
	private $fp = false;

    /**
    * 是否阻塞
    * @var bool
    */
    private $block = false;

	/**
	 * 锁的粒度控制，设置的越大粒度越小
	 *
	 * @var int
	 */
	private $hashNum = 10000;
	private $name = null;
	/**
	 * 构造函数
	 *
	 * @param string $path 锁的存放目录，以"/"结尾
	 * @param string $name 锁名称，一般在对资源加锁的时候，会命名一个名字，这样不同的资源可以并发的进行。
	 */

	public function __construct($name = 'lock', $path = '/dev/shm/', $bolck = false) 
    {
        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            $dir = $path;
        } else {
            $dir = $path.DIRECTORY_SEPARATOR;
        }
		$this->path = $dir.$name.'_'.($this->mycrc32($name)%$this->hashNum).'.txt';
		$this->name = $name;
        $this->block = $bolck;
	}

    // This function returns the same int value on a 64 bit mc. like the crc32() function on a 32 bit mc.
    // URL: http://cn2.php.net/manual/zh/function.crc32.php#79567
	/**
	 * crc32的封装
	 *
	 * @param string $string
	 * @return int
	 */
	private function mycrc32($string) 
    {
		$crc = abs (crc32($string));
		if ($crc & 0x80000000) {
			$crc ^= 0xffffffff;
			$crc += 1;
		}
		return $crc;
	}

	/**
	 * 开始加锁
	 *
	 * @return bool 加锁成功返回true,失败返回false
	 */
	public function lock() 
    {
        if(!$this->fp) { //自动打开文件句柄
            $this->fp = fopen ($this->path, "w+");
        }

        if ($this->fp === false) {
            return false;
        }

        return $this->block ? flock($this->fp, LOCK_EX) : flock($this->fp, LOCK_EX|LOCK_NB);
	}

	/**
	 * 释放锁
	 *
	 */
	public function unlock() 
    {
        if ($this->fp !== false) {
            flock ($this->fp, LOCK_UN);
            clearstatcache ();
        }
        return true;
	}

    public function __destruct()
    {
        if($this->fp) {
            fclose($this->fp);
            $rs = unlink($this->path);
        }
    }
}
