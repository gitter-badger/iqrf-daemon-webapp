<?php

/**
 * TEST: App\ConfigModule\Model\InstanceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\InstanceManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class InstanceManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager
	 */
	private $fileManagerTemp;

	/**
	 * @var array
	 */
	private $array = [
		'Name' => 'MqttMessaging2',
		'Enabled' => false,
		'BrokerAddr' => 'tcp://iot.eclipse.org:1883',
		'ClientId' => 'IqrfDpaMessaging2',
		'Persistence' => 1,
		'Qos' => 1,
		'TopicRequest' => 'Iqrf/DpaRequest',
		'TopicResponse' => 'Iqrf/DpaResponse',
		'User' => '',
		'Password' => '',
		'EnabledSSL' => false,
		'KeepAliveInterval' => 20,
		'ConnectTimeout' => 5,
		'MinReconnect' => 1,
		'MaxReconnect' => 64,
		'TrustStore' => 'server-ca.crt',
		'KeyStore' => 'client.pem',
		'PrivateKey' => 'client-privatekey.pem',
		'PrivateKeyPassword' => '',
		'EnabledCipherSuites' => '',
		'EnableServerCertAuth' => true
	];

	/**
	 * @var string
	 */
	private $fileName = 'MqttMessaging';

	/**
	 * @var string
	 */
	private $path = __DIR__ . '/../../configuration/';

	/**
	 * @var string
	 */
	private $pathTest = __DIR__ . '/../../configuration-test/';

	/**
	 * Constructor
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new JsonFileManager($this->path);
		$this->fileManagerTemp = new JsonFileManager($this->pathTest);
	}

	/**
	 * @test
	 * Test function to delete configuration of Instances
	 */
	public function testDelete() {
		$manager = new InstanceManager($this->fileManagerTemp);
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['Instances'][1]);
		$manager->delete($this->fileName, 1);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testGetInstances() {
		$manager = new InstanceManager($this->fileManager);
		$expected = $this->fileManager->read($this->fileName)['Instances'];
		Assert::equal($expected, $manager->getInstances($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Scheduler
	 */
	public function testLoad() {
		$manager = new InstanceManager($this->fileManager);
		Assert::equal($this->array, $manager->load($this->fileName, 1));
		Assert::equal([], $manager->load($this->fileName, 10));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSave() {
		$manager = new InstanceManager($this->fileManagerTemp);
		$array = $this->array;
		$array['EnabledSSL'] = true;
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['Instances'][1]['Properties']['EnabledSSL'] = true;
		$manager->save($this->fileName, ArrayHash::from($array), 1);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

	/**
	 * @test
	 * Test function to parse configuration of Instances
	 */
	public function testSaveJson() {
		$manager = new InstanceManager($this->fileManager);
		$instances = $this->fileManager->read($this->fileName)['Instances'];
		$array = $this->array;
		$array['EnabledSSL'] = true;
		$expected = $instances;
		$expected[1]['Properties']['EnabledSSL'] = true;
		Assert::equal($expected, $manager->saveJson($instances, ArrayHash::from($array), 1));
	}

}

$test = new InstanceManagerTest($container);
$test->run();
