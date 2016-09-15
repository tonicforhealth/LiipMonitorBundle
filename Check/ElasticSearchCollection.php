<?php

namespace Liip\MonitorBundle\Check;

use TonicHealthCheck\Check\Elasticsearch\Ping\ElasticsearchPingCheck;
use ZendDiagnostics\Check\CheckCollectionInterface;
use Elasticsearch\ClientBuilder as ElasticsearchClientBuilder;

/**
 * ElasticSearch service ping check
 *
 * @author Dmitry Gopkalo <drefixs@gmail.com>
 */
class ElasticSearchCollection implements CheckCollectionInterface
{
    private $checks = array();

    /**
     * ElasticSearchCollection constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $sslVerification = isset($config['ssl_verification']) ? $config['ssl_verification'] : false;
            $elasticsearchClient = ElasticsearchClientBuilder::create()
                ->setHosts($config['hosts'])
                ->setSSLVerification($sslVerification)
                ->build();

            $check = new ElasticsearchPingCheck(
                $name,
                $elasticsearchClient
            );

            $this->checks[sprintf('elasticsearch_%s', $name)] = $check;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
