<?php
/**
 * Copyright (C) 2012 Louis-Philippe Huberdeau
 *
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace HireVoice\Neo4j;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Transport;

class Configuration
{
    private $transport = 'default';
    private $host = 'localhost';
    private $port = 7474;
    private $proxyDir = '/tmp';
    private $debug = false;
    private $annotationReader;
    private $username;
    private $password;

    function __construct(array $configs = array())
    {
        if (isset($configs['transport'])) {
            $this->transport = $configs['transport'];
        }

        if (isset($configs['host'])) {
            $this->host = $configs['host'];
        }

        if (isset($configs['port'])) {
            $this->port = (int) $configs['port'];
        }

        if (isset($configs['debug'])) {
            $this->debug = (bool) $configs['debug'];
        }

        if (isset($configs['proxy_dir'])) {
            $this->proxyDir = $configs['proxy_dir'];
        }

        if (isset($configs['annotation_reader'])) {
            $this->annotationReader = $configs['annotation_reader'];
        }

        if (isset($configs['username'], $configs['password'])) {
            $this->username = $configs['username'];
            $this->password = $configs['password'];
        }
    }

    function getClient()
    {
        $transport = $this->getTransport();
        $transport->setAuth($this->username, $this->password);

        return new Client($transport);
    }

    private function getTransport()
    {
        // @todo: rolniczo ale działa na tymczas ...
        if(is_object($this->transport)) {
            return $this->transport;
        } else {
            switch ($this->transport) {
                case 'stream':
                    return new Transport\Stream($this->host, $this->port);
                case 'curl':
                default:
                    if (class_exists($this->transport) && in_array('Everyman\Neo4j\Transport', class_parents($this->transport))) {
                        return new $this->transport($this->host, $this->port);
                    }

                    return new Transport\Curl($this->host, $this->port);
            }
        }
    }

    function getProxyFactory()
    {
        return new Proxy\Factory($this->proxyDir, $this->debug);
    }

    function getMetaRepository()
    {
        return new Meta\Repository($this->annotationReader);
    }
}

