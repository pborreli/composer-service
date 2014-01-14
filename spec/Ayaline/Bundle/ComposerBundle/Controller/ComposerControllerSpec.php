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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ComposerControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        Form $composerForm,
        AMQPBackendDispatcher $sonataNotificationsBackend
    ) {
        $this->beConstructedWith($templating, $composerForm, $sonataNotificationsBackend);
    }

    function it_render_welcome_page(
        EngineInterface $templating,
        Form $composerForm,
        FormView $formView,
        Response $response
    ){
        $composerForm->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse(
            'AyalineComposerBundle:Composer:index.html.twig',
            array('form' => $formView)
        )->shouldBeCalled()->willReturn($response);

        $this->indexAction()->shouldReturn($response);
    }

    function it_return_json_with_error_message_when_form_data_is_not_valid_json(
        Request $request,
        Form $composerForm,
        FormError $composerFormError
    ){
        $composerForm->handleRequest($request)->shouldBeCalled();
        $composerForm->isValid()->shouldBeCalled()->willReturn(false);
        $composerForm->isValid()->shouldBeCalled()->willReturn(false);

        $composerForm->get('body')->shouldBeCalled()->willReturn($composerForm);
        $composerForm->getErrors()->shouldBeCalled()->willReturn(array($composerFormError));
        $composerFormError->getMessage()->shouldBeCalled()->willReturn('Please provide a composer.json');
        
        $this->uploadComposerAction($request)->shouldBeJsonResponse(
            array('status' => 'ko', 'message' => array('Please provide a composer.json'))
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

        $this->uploadComposerAction($request)->shouldBeJsonResponse(
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
