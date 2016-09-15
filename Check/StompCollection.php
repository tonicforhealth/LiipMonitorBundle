<?php

namespace Liip\MonitorBundle\Check;

use Stomp\StatefulStomp;
use Stomp\Client as StompClient;
use TonicHealthCheck\Check\ActiveMQ\Connect\ActiveMQConnectCheck;
use ZendDiagnostics\Check\CheckCollectionInterface;

/**
 * ActiveMQ(Stomp) service check
 *
 * @author Dmitry Gopkalo <drefixs@gmail.com>
 */
class StompCollection implements CheckCollectionInterface
{
    private $checks = array();

    /**
     * StompCollection constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $clientStomp = static::createClientStomp($config);

            $statefulStomp = new StatefulStomp($clientStomp);

            $check = new ActiveMQConnectCheck($name, $statefulStomp);

            $this->checks[sprintf('stomp_%s', $name)] = $check;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * @param $config
     *
     * @return StompClient
     */
    protected static function createClientStomp($config)
    {
        $clientStomp = new StompClient(
            $config['broker']
        );

        $clientStomp->setLogin(
            $config['user'],
            $config['password']
        );

        return $clientStomp;
    }
}
