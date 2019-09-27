<?php

namespace Hailkongsan\AntiBotLink;

use Illuminate\Session\Store;

class AntiBotLinkSessionManager
{
    /**
	 * The Laravel session instace
	 * 
	 * @var \Illuminate\Session\Store
	 */
    protected $session;
    
    protected $key;

    public function __construct()
    {
        $this->session = app(Store::class);
        $this->key = config('antibotlink.session_key', 'antibotlink');
    }

    public function clear()
    {
        $this->session->forget($this->key);
    }

    public function put($data)
    {
        $this->session->put($this->key, $data);
    }

    public function get(string $key = '', $default = null)
    {
        return $this->session->get($this->buildKey($key), $default);
    }

    public function has(string $key = ''): bool
    {
        return $this->session->has($this->buildKey($key));
    }

    protected function buildKey(string $key): string
    {
        if ($key !== '') {
            return $this->key.'.'.$key;
        }

        return $this->key;
    }

    /**
     * Get the Laravel Session instance.
     *
     * @return Illuminate\Session\Store
     */
    public function getInstance()
    {
        return $this->session;
    }

    /**
     * Get session key prefix
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}