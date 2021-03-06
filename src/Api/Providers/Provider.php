<?php

namespace seregazhuk\PinterestBot\Api\Providers;

use seregazhuk\PinterestBot\Api\Request;
use seregazhuk\PinterestBot\Api\Response;
use seregazhuk\PinterestBot\Exceptions\AuthException;
use seregazhuk\PinterestBot\Contracts\RequestInterface;
use seregazhuk\PinterestBot\Contracts\ResponseInterface;
use seregazhuk\PinterestBot\Helpers\Providers\Traits\ProviderTrait;

/**
 * Class Provider
 *
 * @package seregazhuk\PinterestBot\Contracts
 */
abstract class Provider
{
    use ProviderTrait;

    /**
     * List of methods that require logged status
     * @var array
     */
    protected $loginRequired = [];

    /**
     * Instance of the API RequestInterface
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Instance of the API ResponseInterface
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Executes a POST request to Pinterest API
     *
     * @param array  $requestOptions
     * @param string $resourceUrl
     * @param bool   $returnData
     * @return mixed
     */
    public function callPostRequest($requestOptions, $resourceUrl, $returnData = null)
    {
        $data = array("options" => $requestOptions);
        $postString = Request::createQuery($data);
        $response = $this->request->exec($resourceUrl, $postString);

        if ($returnData) {
            return $this->response->getData($response);
        }

        return $this->response->checkResponse($response);
    }

    /**
     * Run login check before every method if needed
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            $this->checkMethodForLoginRequired($method);

            return call_user_func_array(array($this, $method), $arguments);
        }
    }

    /**
     * Checks if method requires login and if true,
     * checks logged in status.
     *
     * @param $method
     * @throws AuthException if is not logged in
     */
    protected function checkMethodForLoginRequired($method)
    {
        if (in_array($method, $this->loginRequired) && ! $this->request->isLoggedIn()) {
            throw new AuthException("You must log in before.");
        }
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        return $this->response;
    }
}
