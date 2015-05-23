<?php

namespace Base\Formatter;

class Date implements Formatter
{
    function set($value)
    {
        $this->value = $value;
        return $this;
    }
    
    function get()
    {
        return $this->value->format('d/m/Y');
    }
}
