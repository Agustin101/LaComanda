<?php

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;

interface IApiUsable
{
	public function AgregarUsuario(IRequest $request, IResponse $response, $args);
}
