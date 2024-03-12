<?php
// KafkaConfig.php
return [
    'brokers' => 'localhost:9092', // 您的Kafka brokers地址
    'producer' => [
        // 生产者特定的配置选项
        'compression.type' => 'snappy', // 使用snappy压缩
        'acks' => 'all', // 生产者收到server确认的设置
        'retries' => 5, // 设置重试次数
    ],
    'consumer' => [
        // 消费者特定的配置选项
        'group.id' => 'your_consumer_group', // 消费者群组ID
        'auto.offset.reset' => 'earliest', // 如果没有初始偏移量或偏移量是无效的，从最初开始
    ]
];
