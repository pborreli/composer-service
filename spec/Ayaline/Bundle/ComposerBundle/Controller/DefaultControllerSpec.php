<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Controller;

use PhpSpec\ObjectBehavior;
use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        Form $composerForm,
        AMQPBackendDispatcher $sonataNotificationsBackend
    ) {
        $this->beConstructedWith($templating, $composerForm, $sonataNotificationsBackend);
    }

    function it_render_welcome_page(
        Request $request,
        EngineInterface $templating,
        Form $composerForm,
        FormView $formView,
        Response $response
    ){
        $composerForm->handleRequest($request)->shouldBeCalled();
        $composerForm->isValid()->shouldBeCalled()->willReturn(false);
        $composerForm->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse(
            'AyalineComposerBundle:Default:index.html.twig',
            array('form' => $formView)
        )->shouldBeCalled()->willReturn($response);

        $this->indexAction($request)->shouldReturn($response);
    }

    function it_return_json_with_error_message_when_form_data_is_empty(
        Request $request,
        Form $composerForm
    ) {
        $composerForm->handleRequest($request)->shouldBeCalled();
        $composerForm->isValid()->shouldBeCalled()->willReturn(true);
        $composerForm->getData()->shouldBeCalled()->willReturn(array());

        $this->indexAction($request)->shouldBeJsonResponse(
            array('status' => 'ko', 'message' => 'Please provide a composer.json')
        );
    }

    function it_return_json_with_error_message_when_form_data_is_not_valid_json(
        Request $request,
        Form $composerForm
    ){
        $composerForm->handleRequest($request)->shouldBeCalled();
        $composerForm->isValid()->shouldBeCalled()->willReturn(true);
        $composerForm->getData()->shouldBeCalled()->willReturn(array(
            'body' => 'not valid json'
        ));

        $this->indexAction($request)->shouldBeJsonResponse(
            array('status' => 'ko', 'message' => <<<EOT
"composer.json" does not contain valid JSON<br />
Parse error on line 1:<br />
not valid json<br />
^<br />
Expected one of: 'STRING', 'NUMBER', 'NULL', 'TRUE', 'FALSE', '{', '['
EOT
            )
        );
    }

    function it_return_json_with_success_status_and_create_sonata_notification(
        Request $request,
        Session $session,
        Form $composerForm,
        AMQPBackendDispatcher $sonataNotificationsBackend
    ) {
        $composerJsonContent = <<<EOT
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
EOT;

        $composerForm->handleRequest($request)->shouldBeCalled();
        $composerForm->isValid()->shouldBeCalled()->willReturn(true);
        $composerForm->getData()->shouldBeCalled()->willReturn(array(
            'body' => $composerJsonContent,
            'hasDevDependencies' => false
        ));

        $request->getSession()->shouldBeCalled()->willReturn($session);
        $session->get('channelName')->shouldBeCalled()->willReturn('example_channel_name');

        $sonataNotificationsBackend->createAndPublish('upload.composer', array(
            'body' => $composerJsonContent,
            'channelName' => 'example_channel_name',
            'hasDevDependencies' => false
        ))->shouldBeCalled();

        $this->indexAction($request)->shouldBeJsonResponse(
            array('status' => 'ok')
        );
    }

    public function getMatchers()
    {
        return array(
            'beJsonResponse' => function ($response, $data) {
                if (!$response instanceof JsonResponse) {
                    return false;
                }

                return $response->getContent() === json_encode(
                    $data,
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                );
            },
        );
    }
}
