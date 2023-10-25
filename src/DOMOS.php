<?php

namespace Domos\Core;

use Domos\Core\Sync\DomosClient;
use Domos\Core\Sync\SyncManager;

class DOMOS
{
    protected ?string $url;

	public DomosClient $api;
    public SyncManager $sync;
    public URLResolver $urlResolver;
	public Options $options;

    public function __construct()
    {
		$this->options = new Options();
        $this->urlResolver = new URLResolver();
		$this->sync = new SyncManager();
		$this->api = new DomosClient($this->url());
    }

    public function url(): ?string
    {
        if (!isset($this->url)) {
            $this->url = $this->options->url->get();
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
