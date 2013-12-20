<?php

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Composer\Composer;
use Composer\Factory;
use Composer\Installer;
use Composer\IO\BufferIO;
use Composer\Json\JsonFile;
use Composer\Util\RemoteFilesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {

        $tempfile = '/Users/ayoub/tmp/'. uniqid('composer', true);

        $this->get('sonata.notification.backend')->createAndPublish('upload.composer', array(
            'path' => $tempfile
        ));

        $form = $this->createFormBuilder()
            ->add('file', 'file')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database

        }

        return $this->render('AyalineComposerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
