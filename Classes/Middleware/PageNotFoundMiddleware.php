<?php

namespace ArbkomEKvW\Evangtermine\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageNotFoundMiddleware implements MiddlewareInterface
{
    protected $uriParts = [
        '/termindetails/',
        '/terminteaser/',
    ];
    protected $requestUri = '';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * If it's the 404 error page, check if it's the detail page of an event.
         * If true, redirect to the event list page
         */
        if ($this->is404Request($request)) {
            foreach ($this->uriParts as $uriPart) {
                if (strpos($this->requestUri, $uriPart) !== false) {
                    $uriArray = explode($uriPart, $this->requestUri);
                    if (count($uriArray) > 1) {
                        $uri = $this->createUriString($uriArray[0], $request);
                        $newUri = GeneralUtility::makeInstance(Uri::class, $uri);

                        /** @var ServerRequest $newRequest */
                        $newRequest = GeneralUtility::makeInstance(ServerRequest::class,
                            $newUri,
                            $request->getMethod(),
                            $request->getBody(),
                            $request->getHeaders(),
                            $request->getServerParams()
                        );
                        foreach ($request->getAttributes() as $key => $attribute) {
                            $newRequest = $newRequest->withAttribute($key, $attribute);
                        }
                        return $handler->handle($newRequest);
                    }
                }
            }
        }
        return $handler->handle($request);
    }

    protected function is404Request(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $serverParams = $request->getServerParams();
        $this->requestUri = $serverParams['REQUEST_URI'] ?? '';
        if (!empty($this->requestUri) && $path !== $this->requestUri) {
            return true;
        }
        return false;
    }

    /**
     * @param $uriArray
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function createUriString($uriArray, ServerRequestInterface $request): string
    {
        $uri = $uriArray;
        $uri = $request->getUri()->getHost() . $uri;
        $uri = str_replace('//', '/', $uri);
        return $request->getUri()->getScheme() . '://' . $uri;
    }
}
