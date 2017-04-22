<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Model;

use Nette;
use Nette\Utils\Finder;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class ConfigManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 * @inject
	 */
	private $configDir;

	/**
	 * Constructor
	 * @param string $configDir Directory with configuration files
	 */
	public function __construct(string $configDir) {
		$this->configDir = $configDir;
	}

	public function read(string $name) {
		$json = FileSystem::read($this->configDir . '/' . $name . '.json');
		return Json::decode($json, Json::FORCE_ARRAY);
	}

	public function getAll() {
		$fileNames = [];
		foreach (Finder::findFiles('*.json')->in($this->configDir) as $path => $file) {
			array_push($fileNames, str_replace($this->configDir . '/', '', $path));
			echo $path; // $path je řetězec s názvem souboru včetně cesty
			echo $file; // $file je objektem SplFileInfo
		}
		return $fileNames;
	}

	protected function parseComponents($components) {
		$array = [];
		foreach ($components as $component => $enabled) {
			array_push($array, ['ComponentName' => $component, 'Enabled' => $enabled]);
		}
		return $array;
	}

	protected function parseUdp($udp) {
		$array = [];
		$array['Name'] = 'UdpMessaging';
		$array['Enabled'] = $udp['Enabled'];
		$array['Properties'] = ['RemotePort' => $udp['RemotePort'], 'LocalPort' => $udp['LocalPort']];
		return $array;
	}

	public function saveComponents($components) {
		$file = $this->configDir . '/config.json';
		$json = Json::decode(FileSystem::read($file), Json::FORCE_ARRAY);
		$json['Components'] = $this->parseComponents($components);
		FileSystem::write($file, Json::encode($json, Json::PRETTY));
	}

	public function saveUdp($udp) {
		$file = $this->configDir . '/UdpMessaging.json';
		$json = Json::decode(FileSystem::read($file), Json::FORCE_ARRAY);
		$json['Instances'][0] = $this->parseUdp($udp);
		FileSystem::write($file, Json::encode($json, Json::PRETTY));
	}

}