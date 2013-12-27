<?php

namespace Ayaline\Bundle\ComposerBundle\Pusher;

use Lopi\Bundle\PusherBundle\Authenticator\ChannelAuthenticatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ChannelAuthenticator implements ChannelAuthenticatorInterface
{
    /**
     * @var $container
     */
    public $container;

    /**
     * Constructor
     *
     * @param ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
    }

    /**
     * @param string $socketId
     * @param string $channelName
     * @return Boolean
     */
    public function authenticate($socketId, $channelName)
    {
        $request = $this->container->get('request');
        $logger = $this->container->get('logger');
        $session = $request->getSession();

        $session_name = $session->getName();
        $cookie_name = $request->cookies->get($session_name);

        if (strpos($channelName, $cookie_name) === false) {
            return false;
        }

        $logger->addInfo('Connected with socketId :'. $socketId. ' in channel :'.$channelName);
        $session->set('socketId', $socketId);
        $session->set('channelName', $channelName);

        return true;
    }
}