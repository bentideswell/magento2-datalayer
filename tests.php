<?php
/**
 * @author Ben Tideswell (ben@fishpig.com)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

for ($i = 3; $i <= 4; $i++) {
    $path = __DIR__ . str_repeat('/..', $i) . '/app/bootstrap.php';
    if (is_file($path)) {
        require $path;
        break;
    }
}

$objectManager = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER)->getObjectManager();
$objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');


try {
    $testPool = $objectManager->get(
        \FishPig\DataLayer\Tests\Pool::class
    );

    foreach ($testPool->getAll() as $test) {
        try {
            $test->runTest();
        } catch (\Throwable $e) {
            echo sprintf(
                "\nTest '%s' Failed\n\n%s",
                $test->getId(),
                $e->getMessage()
            );
            exit(1);
        }
    }

    echo "OK";
} catch (\Throwable $e) {
    echo $e;
    exit(1);
}
