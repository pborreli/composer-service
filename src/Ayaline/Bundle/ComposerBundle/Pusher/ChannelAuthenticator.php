<?php

namespace Ayaline\Bundle\ComposerBundle\Pusher;

use Lopi\Bundle\PusherBundle\Authenticator\ChannelAuthenticatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelAuthenticator implements ChannelAuthenticatorInterface
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $socketId
     * @param string $channelName
     * @return Boolean
     */
    public function authenticate($socketId, $channelName)
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $session_name = $session->getName();
        $cookie_name = $request->cookies->get($session_name);

        if (strpos($channelName, $cookie_name) === false) {
            return false;
        }

        $session->set('socketId', $socketId);
        $session->set('channelName', $channelName);

        return true;
    }
}