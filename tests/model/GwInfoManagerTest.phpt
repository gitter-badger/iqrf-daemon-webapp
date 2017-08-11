<?php

/**
 * TEST: App\Model\GwInfoManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Model;

use App\Model\CommandManager;
use App\Model\GwInfoManager;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\IqrfAppParser;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class GwInfoManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with("ls /sys/class/net | awk '{ print $0 }'", true)->andReturn('eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$cmdEth0 = 'ip a s eth0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
		$commandManager->shouldReceive('send')->with($cmdEth0, true)->andReturn('192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$cmdWlan0 = 'ip a s wlan0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
		$commandManager->shouldReceive('send')->with($cmdWlan0, true)->andReturn('');
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$iqrfAppParser = new IqrfAppParser();
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager, $iqrfAppParser);
		Assert::same(['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']], $gwInfoManager->getIpAddresses());
	}

	/**
	 * @test
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with("ls /sys/class/net | awk '{ print $0 }'", true)->andReturn('eth0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with('cat /sys/class/net/eth0/address', true)->andReturn('01:02:03:04:05:06');
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$iqrfAppParser = new IqrfAppParser();
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager, $iqrfAppParser);
		Assert::same(['eth0' => '01:02:03:04:05:06'], $gwInfoManager->getMacAddresses());
	}

	/**
	 * @test
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$output = 'gateway';
		$commandManager->shouldReceive('send')->with('hostname -f')->andReturn($output);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$iqrfAppParser = new IqrfAppParser();
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager, $iqrfAppParser);
		Assert::same($output, $gwInfoManager->getHostname());
	}

	/**
	 * @test
	 * Test function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('iqrfapp raw 00.00.02.00.FF.FF', true)->andReturn(null);
		$iqrfAppManager = new IqrfAppManager($commandManager);
		$iqrfAppParser = new IqrfAppParser();
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager, $iqrfAppParser);
		Assert::null($gwInfoManager->getCoordinatorInfo());
	}

}

$test = new GwInfoManagerTest($container);
$test->run();
