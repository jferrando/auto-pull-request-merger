<?php

namespace Library;
use App;

class Base
{
    public function addDependency($name, $obj)
    {
        $internalObjName = "_" . $name;
        $this->$internalObjName = $obj;
    }
}