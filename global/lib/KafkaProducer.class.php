<?php
// KafkaProducer.class.php
require_once dirname(__FILE__) . '/../global/KafkaConf.php'; // 调整路径根据实际位置

class KafkaProducer {
    private $producer;

    public function __construct() {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', KAFKA_BROKER_LIST);
        $this->producer = new RdKafka\Producer($conf);
        
        if (!$this->producer->getMetadata(true, null, 10000)) {
            throw new Exception("Failed to connect to Kafka");
        }
    }

    public function sendMessage($topicName, $message) {
        $topic = $this->producer->newTopic($topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->poll(0);

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $this->producer->flush(1000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new Exception('Failed to send message');
        }
    }
}
