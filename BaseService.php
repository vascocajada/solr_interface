<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class BaseService
{
    protected $logger;
    protected $request;
    protected $session;
    protected $translator;
    protected $router;
    protected $params;

    public function __construct(LoggerInterface $logger, RequestStack $request_stack, SessionInterface $session, TranslatorInterface $translator, UrlGeneratorInterface $router, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->request = $request_stack->getCurrentRequest();
        $this->session = $session;
        $this->translator = $translator;
        $this->router = $router;
        $this->params = $params;
    }
}
