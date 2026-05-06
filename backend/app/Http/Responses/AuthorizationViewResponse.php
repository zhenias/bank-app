<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Laravel\Passport\Contracts\AuthorizationViewResponse as AuthorizationViewResponseContract;

class AuthorizationViewResponse implements AuthorizationViewResponseContract, Responsable
{
    private $view;
    private $parameters;

    public function __construct($view = 'passport::authorize', array $parameters = [])
    {
        $this->view = $view;
        $this->parameters = $parameters;
    }

    public function toResponse($request)
    {
        return response()->view($this->view, $this->parameters);
    }

    public function withParameters(array $parameters = []): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
