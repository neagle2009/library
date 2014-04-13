<?php
/**
 * 测试例子
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
