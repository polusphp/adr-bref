<?php declare(strict_types=1);

namespace Polus\Adr\Bref;

use Polus\Adr\ActionDispatcher\HandlerActionDispatcher;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\Resolver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class Handler implements RequestHandlerInterface
{
    /** @var class-string|null */
    protected ?string $action = null;

    public function __construct(
        private Resolver $resolver,
        private ResponseFactoryInterface $responseFactory,
        private ?ActionDispatcher $actionDispatcher = null,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this instanceof Action) {
            $action = $this;
        }
        elseif (!$this->action) {
            return $this->responseFactory->createResponse(400, 'No action');
        }
        elseif (!class_exists($this->action)) {
            return $this->responseFactory->createResponse(500, 'Action not found');
        }
        else {
            try {
                $actionClass = $this->action;
                $action = new $actionClass();
            }
            catch (\Throwable) {
                return $this->responseFactory->createResponse(500, 'Failed to create action');
            }
            if (!$action instanceof Action) {
                return $this->responseFactory->createResponse(500, 'Invalid action');
            }
        }
        $actionDispatcher = $this->actionDispatcher ?? HandlerActionDispatcher::default($this->resolver, $this->responseFactory);

        return $actionDispatcher->dispatch($action, $request);
    }
}
