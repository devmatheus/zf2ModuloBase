<?php

namespace Base\Validator;

/**
 * Class that validates if objects does not exist in a given repository with a given list of matched fields
 */
class NoObjectExists extends ObjectExists
{
    /**
     * Error constants
     */
    const ERROR_OBJECT_FOUND    = 'objectFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_OBJECT_FOUND    => "Um objeto '%value%' foi encontrado",
    );

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $value = $this->cleanSearchValue($value);
        $match = $this->objectRepository->findOneBy($value);
        
        if ($this->exclude) {
            $matchExclude = $this->objectRepository->find($this->exclude);
        }

        if (is_object($match) && $match != $matchExclude) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);

            return false;
        }

        return true;
    }
}
