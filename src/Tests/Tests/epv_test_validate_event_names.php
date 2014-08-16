<?php
/**
 *
 * EPV :: The phpBB Forum Extension Pre Validator.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace Phpbb\Epv\Tests\Tests;

use Phpbb\Epv\Events\php_exporter;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Tests\BaseTest;

class epv_test_validate_event_names extends BaseTest
{
	public function __construct($debug, \Phpbb\Epv\Output\OutputInterface $output, $basedir, $namespace, $titania)
	{
		parent::__construct($debug, $output, $basedir, $namespace, $titania);

		$this->directory = true;

		$this->totalDirectoryTests = 0;
	}

	public function validateDirectory(array $dirList)
	{
		$exporter = new php_exporter($this->output);

		foreach ($dirList as $file)
		{
			try
			{
				if (substr($file, -4) === '.php')
				{
					$exporter->crawl_php_file($file);
				}
			}
			catch
			(\LogicException $e)
			{
				$this->output->inMaxPogress(1);
				$this->output->addMessage(Output::FATAL, $e->getMessage());
			}
		}

		$events = $exporter->get_events();
		$this->output->inMaxPogress(sizeof($events) * 3);
		$vendor = strtolower(str_replace('/', '.', $this->namespace)); // event names are requierd to be lowercase!

		foreach ($events as $event)
		{
			if (strtolower(substr($event['event'], 0, 5)) == 'phpbb')
			{
				$this->output->addMessage(Output::ERROR, sprintf('The phpbb vendorname should only be used for official extensions in event names in %s. Current event name: %s', $event['file'], $event['event']));
			}
			else if (strtolower(substr($event['event'], 0, 4)) == 'core')
			{
				$this->output->addMessage(Output::FATAL, sprintf('The core vendorname should not be used in event names in %s. Current event name: %s', $event['file'], $event['event']));
			}
			else
			{
				$this->output->printErrorLevel();
			}

			$substr = substr($event['event'], 0, strlen($vendor));
			if ($substr != $vendor)
			{
				$this->output->addMessage(Output::NOTICE, sprintf('The event name should start with vendor.namespace (Which is %s) but started with %s in %s', $vendor, $substr, $event['file']));
			}
			else
			{
				$this->output->printErrorLevel();
			}
		}
	}

	public function testName()
	{
		return "Validate directory structure";
	}
}