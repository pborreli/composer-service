<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Pusher;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class ChannelAuthenticatorSpec extends ObjectBehavior
{
    public function let(
        RequestStack $requestStack,
        Request $request,
        Session $session,
        ParameterBag $cookies
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $request->cookies = $cookies;
        $this->beConstructedWith($requestStack);
    }

    public function it_is_channel_authenticator()
    {
        $this->shouldBeAnInstanceOf(
            'Lopi\Bundle\PusherBundle\Authenticator\ChannelAuthenticatorInterface'
        );
    }

    public function it_authenticate_channel(Session $session, ParameterBag $cookies)
    {
        $this->authenticate(1, 'new_channel')->shouldReturn(true);
    }
}
