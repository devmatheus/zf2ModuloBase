<?php

namespace Base\Formatter;

class DateTime implements Formatter
{
    function set($value)
    {
        $this->value = $value;
        return $this;
    }
    
    function get()
    {
        if ($this->value instanceof \DateTime) {
            return $this->value->format('d/m/Y - H:i:s');
        }
    }
}
