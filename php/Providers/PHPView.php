<?php

namespace Crisis\Providers;

use UMA\DIC\Container;
use Slim\Views\PhpRenderer;

class PHPView implements \UMA\DIC\ServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function provide(Container $c): void
	{
		$c->set(PhpRenderer::class, static function (Container $c): PhpRenderer {
			/** @var array $settings */
			// $settings = $c->get('settings');

			$phpView = new PhpRenderer(TEMPLATES_DIR, ['title' => 'Crisis']);
			$phpView->setLayout('layout.phtml');
			return $phpView;
		});
	}
}
