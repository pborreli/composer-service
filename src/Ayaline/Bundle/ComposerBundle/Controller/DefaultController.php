<?php

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Composer\Composer;
use Composer\Factory;
use Composer\Installer;
use Composer\IO\BufferIO;
use Composer\Json\JsonFile;
use Composer\Util\RemoteFilesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {

        $defaultComposerBody = <<<DCB
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
DCB;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('_welcome'))
            ->add('body', 'textarea', array('attr' => array('class' => 'form-control', 'rows' => 20), 'data' => $defaultComposerBody))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            $this->get('sonata.notification.backend')->createAndPublish('upload.composer', array(
                'body' => $data['body'],
                'channelName' => $request->getSession()->get('channelName')
            ));

            return new JsonResponse(array('status' => 'ok', 'message' => 'Launching...'));
        }

        return $this->render('AyalineComposerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
