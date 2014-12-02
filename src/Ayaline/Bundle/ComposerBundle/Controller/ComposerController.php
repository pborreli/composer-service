<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ComposerController
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var FormInterface
     */
    private $composerForm;

    /**
     * @var AMQPBackendDispatcher
     */
    private $sonataNotificationsBackend;

    /**
     * @param EngineInterface       $templating
     * @param FormInterface         $composerForm
     * @param AMQPBackendDispatcher $sonataNotificationsBackend
     */
    public function __construct(
        EngineInterface $templating,
        FormInterface $composerForm,
        AMQPBackendDispatcher $sonataNotificationsBackend
    ) {
        $this->templating = $templating;
        $this->composerForm = $composerForm;
        $this->sonataNotificationsBackend = $sonataNotificationsBackend;
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'AyalineComposerBundle:Composer:index.html.twig',
            array('form' => $this->composerForm->createView())
        );
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function uploadComposerAction(Request $request)
    {
        $this->composerForm->handleRequest($request);

        if ($this->composerForm->isValid()) {
            $data = $this->composerForm->getData();
            $this->sonataNotificationsBackend->createAndPublish('upload.composer', array(
                'body' => $data['body'],
                'channelName' => $request->getSession()->get('channelName'),
                'hasDevDependencies' => $data['hasDevDependencies']
            ));

            return new JsonResponse(array('status' => 'ok'));
        }

        $errors = array_map(function (FormError $error) {
            return $error->getMessage();

        }, $this->composerForm->get('body')->getErrors());

        return new JsonResponse(array('status' => 'ko', 'message' => $errors));
    }
}
