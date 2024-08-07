<?php

namespace App\Exceptions;

use Exception;

class AccessDeniedException extends Exception
{
    // Construtor padrão com mensagem e código padrão
    public function __construct($message = "Acesso negado.", $code = 403, Exception $previous = null)
    {
        if (auth()->user()->hasRole('admin')) {
            $message = "Você precisa deslogar do usuário administrador para acessar esse painel";
        }
        parent::__construct($message, $code, $previous);
    }
}
