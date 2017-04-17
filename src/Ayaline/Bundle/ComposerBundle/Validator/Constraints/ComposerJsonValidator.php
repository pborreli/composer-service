<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Validator\Constraints;

use Composer\Json\JsonFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class ComposerJsonValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
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
            $from = [$tempFile];
            $to = ['composer.json'];
            $this->context->addViolation(str_replace($from, $to, $exception->getMessage()));
        }
    }
}
