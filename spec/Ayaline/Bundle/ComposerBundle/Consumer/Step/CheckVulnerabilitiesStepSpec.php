<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Consumer\Step;

use PhpSpec\ObjectBehavior;
use SensioLabs\Security\SecurityChecker;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\Message;

class CheckVulnerabilitiesStepSpec extends ObjectBehavior
{
    public function let(\Pusher $pusher, SecurityChecker $securityChecker)
    {
        $this->beConstructedWith($securityChecker);
        $this->setPusher($pusher);
        $this->setWorkingTempPath(sys_get_temp_dir());
    }

    public function it_is_step()
    {
        $this->shouldBeAnInstanceOf('Ayaline\Bundle\ComposerBundle\Consumer\Step\StepInterface');
    }

    public function it_return_zero_status_if_no_vulnerabilities_found(
        SecurityChecker $securityChecker,
        ConsumerEvent $event,
        Message $message,
        \Pusher $pusher
    ) {
        $event->getMessage()->shouldBeCalled()->willReturn($message);
        $message->getValue('channelName')->shouldBeCalled()->willReturn('new_channel');

        $pusher->trigger(
            ['new_channel'],
            'consumer:new-step',
            array('message' => 'Checking vulnerability')
        )->shouldBeCalled();

        $securityChecker->check(sys_get_temp_dir().'/composer_dir/composer.lock')
            ->shouldBeCalled();
        $securityChecker->getLastVulnerabilityCount()->shouldBeCalled()->willReturn(0);

        $this->execute($event, 'composer_dir')->shouldReturn(0);
    }

    public function it_push_error_message_when_error_occurs_during_vulnerability_check(
        SecurityChecker $securityChecker,
        ConsumerEvent $event,
        Message $message,
        \Pusher $pusher
    ) {
        $event->getMessage()->shouldBeCalled()->willReturn($message);
        $message->getValue('channelName')->shouldBeCalled()->willReturn('new_channel');

        $pusher->trigger(
            ['new_channel'],
            'consumer:new-step',
            array('message' => 'Checking vulnerability')
        )->shouldBeCalled();

        $securityChecker->check(sys_get_temp_dir().'/composer_dir/composer.lock')
            ->shouldBeCalled()->willThrow(new \RuntimeException('Error!'));

        $pusher->trigger(
            ['new_channel'],
            'consumer:error',
            array('message' => 'Error!')
        )->shouldBeCalled();

        $this->execute($event, 'composer_dir')->shouldReturn(1);
    }

    public function it_push_error_message_and_alerts_when_vulnerability_found(
        SecurityChecker $securityChecker,
        ConsumerEvent $event,
        Message $message,
        \Pusher $pusher
    ) {
        $event->getMessage()->shouldBeCalled()->willReturn($message);
        $message->getValue('channelName')->shouldBeCalled()->willReturn('new_channel');

        $pusher->trigger(
            ['new_channel'],
            'consumer:new-step',
            array('message' => 'Checking vulnerability')
        )->shouldBeCalled();

        $securityChecker
            ->check(sys_get_temp_dir().'/composer_dir/composer.lock')
            ->shouldBeCalled()->willReturn($this->getVulnerabilityMessage());

        $securityChecker->getLastVulnerabilityCount()->shouldBeCalled()->willReturn(1);

        $pusher->trigger(
            ['new_channel'],
            'consumer:step-error',
            array(
                'message' => 'Vulnerability found : 1',
            )
        )->shouldBeCalled();

        $pusher->trigger(
            ['new_channel'],
            'consumer:vulnerabilities',
            array(
                'message' => $this->getVulnerabilityMessage(),
            )
        )->shouldBeCalled();

        $this->execute($event, 'composer_dir')->shouldReturn(0);
    }

    /**
     * @return array
     */
    private function getVulnerabilityMessage()
    {
        return explode("\n", <<<'EOT'
Security Report
===============

The checker detected 1 package(s) that have known* vulnerabilities in
your project. We recommend you to check the related security advisories
and upgrade these dependencies.

symfony/symfony (v2.0.10)
-------------------------

CVE-2013-1397: Ability to enable/disable object support in YAML parsing and dumping
               http://symfony.com/blog/security-release-symfony-2-0-22-and-2-1-7-released

xxx-xxxx-xxxx: Security fixes related to the way XML is handled
               http://symfony.com/blog/security-release-symfony-2-0-17-released

CVE-2012-6431: Routes behind a firewall are accessible even when not logged in
               http://symfony.com/blog/security-release-symfony-2-0-20-and-2-1-5-released


* Disclaimer: This checker can only detect vulnerabilities that are referenced
              in the SensioLabs security advisories database.
EOT
        );
    }
}
