<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\DataLayer\Observer\Event;

class Purchase implements \Magento\Framework\Event\ObserverInterface
{
    /**
     *
     */
    private $layout = null;

    /**
     *
     */
    private $orderRepository = null;

    /**
     *
     */
    private $logger = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->layout = $layout;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $orderIds = array_filter((array)$observer->getEvent()->getOrderIds());
            $eventBlock = $this->layout->getBlock('datalayer.event.purchase');

            if ($orderIds && $eventBlock) {
                foreach ($orderIds as $orderId) {
                    $order = $this->orderRepository->get($orderId);
                    $eventBlock->getDataProvider()->setOrder($order);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error($e);
        }
    }
}
