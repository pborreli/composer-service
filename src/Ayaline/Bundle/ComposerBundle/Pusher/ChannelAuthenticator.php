<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Pusher;

use Lopi\Bundle\PusherBundle\Authenticator\ChannelAuthenticatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelAuthenticator implements ChannelAuthenticatorInterface
{
    protected $requestStack;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack A RequestStack instance
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $socketId
     * @param string $channelName
     *
     * @return bool
     */
    public function authenticate($socketId, $channelName)
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $session->set('socketId', $socketId);
        $session->set('channelName', $channelName);

        return true;
    }
}
