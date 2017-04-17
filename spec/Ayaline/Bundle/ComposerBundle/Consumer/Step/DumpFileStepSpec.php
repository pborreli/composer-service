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
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\Message;
use Symfony\Component\Filesystem\Filesystem;

class DumpFileStepSpec extends ObjectBehavior
{
    public function let(\Pusher $pusher, Filesystem $filesystem)
    {
        $this->setPusher($pusher);
        $this->setFileSystem($filesystem);
        $this->setWorkingTempPath(sys_get_temp_dir());
    }

    public function it_is_step()
    {
        $this->shouldBeAnInstanceOf('Ayaline\Bundle\ComposerBundle\Consumer\Step\StepInterface');
    }

    public function it_dump_composer_json_content_to_file(
        ConsumerEvent $event,
        Message $message,
        \Pusher $pusher,
        Filesystem $filesystem
    ) {
        $event->getMessage()->shouldBeCalled()->willReturn($message);
        $message->getValue('channelName')->shouldBeCalled()->willReturn('new_channel');
        $message->getValue('body')->shouldBeCalled()->willReturn('composer.json content');

        $pusher->trigger(['new_channel'], 'consumer:new-step', ['message' => 'Starting async job'])
            ->shouldBeCalled();
        $filesystem->mkdir(sys_get_temp_dir().'/'.'composer_dir')->shouldBeCalled();
        $filesystem->dumpFile(
            sys_get_temp_dir().'/composer_dir/composer.json',
            'composer.json content'
        )->shouldBeCalled();

        $this->execute($event, 'composer_dir')->shouldReturn(0);
    }
}
