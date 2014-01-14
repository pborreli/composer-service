<?php

namespace Ayaline\Bundle\ComposerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Composer\Json\JsonFile;

/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class ComposerJsonValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tempFile, $value);

        try {
            $jsonFile = new JsonFile($tempFile);
            $jsonFile->validateSchema(JsonFile::LAX_SCHEMA);
            unlink($tempFile);

        } catch (\Exception $exception) {
            unlink($tempFile);
            $from = array($tempFile);
            $to   = array('composer.json');
            $this->context->addViolation(str_replace($from, $to, $exception->getMessage()));
        }
    }
}
