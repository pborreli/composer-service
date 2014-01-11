<?php

namespace Ayaline\Bundle\ComposerBundle\Controller;

use Composer\Json\JsonFile;
use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $sonataNotificationsBackend;
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var FormInterface
     */
    private $composerForm;

    /**
     * @param EngineInterface $templating
     * @param FormInterface $composerForm
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
     * @param  Request               $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        $this->composerForm->handleRequest($request);

        if ($this->composerForm->isValid()) {

            $data = $this->composerForm->getData();
            if (empty($data['body'])) {
                return new JsonResponse(array('status' => 'ko', 'message' => 'Please provide a composer.json'));
            }

            if (true !== $message = $this->validateComposerJson($data['body'])) {
                return new JsonResponse(array('status' => 'ko', 'message' => nl2br($message)));
            }

            $this->sonataNotificationsBackend->createAndPublish('upload.composer', array(
                'body' => $data['body'],
                'channelName' => $request->getSession()->get('channelName'),
                'hasDevDependencies' => $data['hasDevDependencies']
            ));

            return new JsonResponse(array('status' => 'ok'));
        }

        return $this->templating->renderResponse(
            'AyalineComposerBundle:Default:index.html.twig',
            array('form' => $this->composerForm->createView())
        );
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
