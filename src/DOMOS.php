<?php

namespace Domos\Core;

use Domos\Core\Sync\SyncManager;

class DOMOS
{
    protected string $url;

    public SyncManager $sync;
    public URLResolver $urlResolver;
	public Options $options;

    public function __construct()
    {
        $this->sync = new SyncManager($this->url());
        $this->urlResolver = new URLResolver();
		$this->options = new Options();
    }

    public function url(): string
    {
        if (!isset($this->url)) {
            $this->url = 'https://adler.domos.app';
        }

        return $this->url;
    }


    public static self $instance;

    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
