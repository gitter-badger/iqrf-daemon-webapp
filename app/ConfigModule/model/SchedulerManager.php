<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

class SchedulerManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'Scheduler';

	/**
	 * @var array
	 */
	private $commands = [
		'std-per-io' => [
			'pnum' => '09',
			'pcmd' => [
				'direction' => '00',
				'set' => '01',
				'get' => '02',
			],
		],
		'std-per-frc' => [
			'pnum' => '0d',
			'pcmd' => [
				'send' => '00',
				'extraresult' => '01',
				'send_selective' => '02',
				'set_params' => '03',
			],
		],
		'std-per-ledg' => [
			'pnum' => '07',
			'pcmd' => [
				'off' => '00',
				'on' => '01',
				'get' => '02',
				'pulse' => '03',
			],
		],
		'std-per-ledr' => [
			'pnum' => '06',
			'pcmd' => [
				'off' => '00',
				'on' => '01',
				'get' => '02',
				'pulse' => '03',
			],
		],
		'std-per-thermometer' => [
			'pnum' => '0a',
			'pcmd' => [
				'read' => '00',
			],
		],
	];

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Add task
	 * @param string $type Task type
	 */
	public function add($type) {
		$json = $this->fileManager->read($this->fileName);
		$taskManager = new JsonFileManager(__DIR__ . '/../json');
		$tasks = $taskManager->read($this->fileName);
		$task = array_key_exists($type, $tasks) ? $tasks[$type] : null;
		array_push($json['TasksJson'], $task);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Delete task
	 * @param int $id Task ID
	 */
	public function delete($id) {
		$json = $this->fileManager->read($this->fileName);
		unset($json['TasksJson'][$id]);
		$json['TasksJson'] = array_values($json['TasksJson']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Get last ID
	 * @return int Last ID in array
	 */
	public function getLastId() {
		$json = $this->fileManager->read($this->fileName);
		$tasks = $json['TasksJson'];
		end($tasks);
		return key($tasks);
	}

	/**
	 * Get DPA request from JSON
	 * @param array $data JSON
	 * @return string DPA request
	 */
	public function getRequest(array $data) {
		if ($data['type'] === 'raw') {
			return $data['request'];
		}
		$nadr = (empty($data['nadr']) ? '00' : Strings::padLeft($data['nadr'], 2, '0')) . '.00.';
		$hwpid = (isset($data['hwpid']) ? $data['hwpid'] : 'ffff');
		switch ($data['type']) {
			case 'raw-hdp':
				$pnum = Strings::padLeft($data['pnum'], 2, '0') . '.';
				$pcmd = Strings::padLeft($data['pcmd'], 2, '0') . '.';
				$pdata = (isset($data['req_data']) ? '.' . $data['req_data'] : '');
				return $nadr . $pnum . $pcmd . $hwpid . $pdata;
			case 'std-per-frc':
			case 'std-per-io':
			case 'std-per-ledg':
			case 'std-per-ledr':
			case 'std-per-thermometer':
				$command = $this->commands[$data['type']];
				$pnum = $command['pnum'] . '.';
				$cmd = strtolower($data['cmd']);
				$pcmd = $command['pcmd'][$cmd] . '.';
				return $nadr . $pnum . $pcmd . $hwpid;
		}
	}

	/**
	 * Get tasks in Scheduler
	 * @return array Tasks
	 */
	public function getTasks() {
		$jsonTasks = $this->fileManager->read($this->fileName)['TasksJson'];
		$tasks = [];
		foreach ($jsonTasks as $json) {
			$task['time'] = $json['time'];
			$task['service'] = $json['service'];
			$task['type'] = $json['message']['ctype'] . ' | ' . $json['message']['type'];
			$task['request'] = $this->getRequest($json['message']);
			$task['id'] = array_keys($jsonTasks, $json, true)[0];
			array_push($tasks, $task);
		}
		return $tasks;
	}

	/**
	 * Convert Task JSON array to Task configuration form array
	 * @param int $id Task ID
	 * @return array Array for form
	 */
	public function load($id = 0) {
		$json = $this->fileManager->read($this->fileName);
		$tasks = $json['TasksJson'];
		if ($id > count($tasks)) {
			return [];
		}
		$data = $tasks[$id];
		$scheduler = [];
		$scheduler['time'] = $data['time'];
		$scheduler['service'] = $data['service'];
		if (array_key_exists('sensors', $data['message'])) {
			$sensors = implode(PHP_EOL, $data['message']['sensors']);
			unset($scheduler['message']['sensors']);
			$scheduler += $data['message'];
			$scheduler['sensors'] = $sensors;
		} else {
			$scheduler += $data['message'];
		}
		return $scheduler;
	}

	/**
	 * Save scheduler setting
	 * @param ArrayHash $array Scheduler settings
	 * @param int $id Task ID
	 */
	public function save(ArrayHash $array, $id = 0) {
		$json = $this->saveJson($this->fileManager->read($this->fileName), $array, $id);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Convert Task configuration form array to JSON array
	 * @param array $scheduler Original Task JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Task ID
	 * @return array JSON array
	 */
	public function saveJson(array $scheduler, ArrayHash $update, $id) {
		$data = [];
		$data['time'] = $update['time'];
		$data['service'] = $update['service'];
		unset($update['service'], $update['time']);
		if (array_key_exists('sensors', $update)) {
			$sensors = explode(PHP_EOL, $update['sensors']);
			unset($update['sensors']);
			$update['sensors'] = $sensors;
		}
		$data['message'] = (array) $update;
		$scheduler['TasksJson'][$id] = $data;
		return $scheduler;
	}

}
