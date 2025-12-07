<?php

namespace Davoodf1995\Desk365\DTO;

trait DTOCommon
{
    public function except(array $keys): self {
        $clone = clone $this;
        foreach ($keys as $key) {
            $clone->{$key} = null;
        }

        return $clone;
    }
}

