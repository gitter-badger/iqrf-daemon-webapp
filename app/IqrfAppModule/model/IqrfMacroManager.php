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

namespace App\IqrfAppModule\Model;

use Nette;

class IqrfMacroManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to IQRF IDE MAcro file
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * Read .ini file and parse macros from IQRF IDE
	 * @return array
	 */
	public function read() {
		$config = parse_ini_file($this->path, true)['Macro'];
		$array = $this->parseMacros($config['Macros']);
		return $array;
	}

	/**
	 * Parse data from HEX to array
	 * @param string $hex Data in HEX
	 * @return array Data in array
	 */
	public function parseMacros($hex) {
		$array = explode("\r\n", trim($this->hex2ascii($hex)));
		for ($i = 0; $i < 3; $i++) {
			array_shift($array);
		}
		$macros = [];
		foreach ($array as $key => $value) {
			$category = $key % 63;
			$categoryId = floor($key / 63);
			$macro = (($key - ($categoryId * 63)) - 2) % 5;
			$macroId = floor(($key - 3 - ($categoryId * 63)) / 5);
			if ($category === 0) {
				$macros[$categoryId]['Name'] = $value;
			} elseif ($category === 1) {
				$macros[$categoryId]['Enabled'] = $value === 'True';
			} elseif ($macro === 1) {
				$macros[$categoryId]['Macros'][$macroId]['Name'] = $value;
			} elseif ($macro === 2) {
				$macros[$categoryId]['Macros'][$macroId]['Packet'] = $this->fixPacket($value);
			} elseif ($macro === 3) {
				$macros[$categoryId]['Macros'][$macroId]['Enabled'] = $value === 'True';
			}
		}
		return $macros;
	}

	/**
	 * Fix DPA packet
	 * @param string $packet DPA packet
	 * @return string Fixed DPA packet
	 */
	public function fixPacket($packet) {
		$data = explode('.', trim($packet, '.'));
		$tmp0 = array_shift($data);
		$tmp1 = array_shift($data);
		array_unshift($data, $tmp0);
		array_unshift($data, $tmp1);
		$fixedPacket = trim(implode('.', $data), '.');
		return $fixedPacket;
	}

	/**
	 * Convert data from HEX to ASCII
	 * @param string $hex Data in HEX
	 * @return string Data in ASCII
	 */
	public function hex2ascii($hex) {
		$string = '';
		for ($i = 0; $i < strlen($hex); $i += 2) {
			$string .= chr(hexdec(substr($hex, $i, 2)));
		}
		return $string;
	}

}
