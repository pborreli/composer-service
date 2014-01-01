<?php

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Composer\Json\JsonFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Ayaline\Bundle\ComposerBundle\Form\ComposerType;

class DefaultController extends Controller
{
    /**
     * @param  Request               $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(new ComposerType(), null, array(
                'action' => $this->generateUrl('_welcome')
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            if (empty($data['body'])) {
                return new JsonResponse(array('status' => 'ko', 'message' => 'Please provide a composer.json'));
            }

            if (true !== $message = $this->validateComposerJson($data['body'])) {
                return new JsonResponse(array('status' => 'ko', 'message' => nl2br($message)));
            }
            $this->get('sonata.notification.backend')->createAndPublish('upload.composer', array(
                'body' => $data['body'],
                'channelName' => $request->getSession()->get('channelName'),
                'hasDevDependencies' => $data['hasDevDependencies']
            ));

            return new JsonResponse(array('status' => 'ok'));
        }

        return $this->render('AyalineComposerBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param  string     $string The composer.json string
     * @return Boolean|mixed True if valid, string with the message otherwise
     */
    protected function validateComposerJson($string)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tempFile, $string);

        try {
            $jsonFile = new JsonFile($tempFile);
            $jsonFile->validateSchema(JsonFile::LAX_SCHEMA);
            unlink($tempFile);

            return true;
        } catch (\Exception $exception) {
            $from = array($tempFile);
            $to   = array('composer.json');
            $message = str_replace($from, $to, $exception->getMessage());
            unlink($tempFile);

            return $message;
        }
    }
}
