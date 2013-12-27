<?php

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Composer\Installer;
use Composer\Json\JsonFile;
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
            ->add('body', 'textarea', array('attr' => array('class' => 'form-control', 'rows' => 15), 'data' => $defaultComposerBody))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            $temp_file = tempnam(sys_get_temp_dir(), 'composer');
            file_put_contents($temp_file, $data['body']);

            try {
                $jsonFile = new JsonFile($temp_file);
                $jsonFile->validateSchema(JsonFile::LAX_SCHEMA);
                unlink($temp_file);
            } catch (\Exception $exception) {
                $from = array($temp_file);
                $to   = array('composer.json');
                $message = str_replace($from, $to, $exception->getMessage());
                unlink($temp_file);

                return new JsonResponse(array('status' => 'ko', 'message' => nl2br($message)));
            }

            $this->get('sonata.notification.backend')->createAndPublish('upload.composer', array(
                'body' => $data['body'],
                'channelName' => $request->getSession()->get('channelName')
            ));

            return new JsonResponse(array('status' => 'ok'));
        }

        return $this->render('AyalineComposerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
