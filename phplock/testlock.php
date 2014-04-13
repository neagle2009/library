<?php
/**
 * 测试例子
 * @link http://code.google.com/p/phplock/
 * @author sunli
 * @blog http://sunli.cnblogs.com
 * @svnversion  $Id: testlock.php 6 2010-06-28 03:13:02Z sunli1223 $
 * @version v1.0 beta1
 * @license Apache License Version 2.0
 * @copyright  sunli1223@gmail.com
 */
require __DIR__.'/class.phplock.php';
$lock = new PHPLock ('lock', '/tmp/', true);
$status = $lock->lock ();
if (! $status) {
	exit ( "lock error" );
}
echo "sleeping";
sleep(100);
echo increment ();
$lock->unlock ();

function increment() {
	if (! file_exists ( 'testlockfile' )) {
		file_put_contents ( 'testlockfile', 0 );
	}
	$num = file_get_contents ( 'testlockfile' );
	$num = $num + 1;
	file_put_contents ( 'testlockfile', $num );
	return file_get_contents ( 'testlockfile' );
}
?>
