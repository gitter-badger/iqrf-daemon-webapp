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

class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var bool
	 * @inject
	 */
	private $sudo;

	/**
	 * Constructor
	 * @param bool $sudo Sudo required
	 */
	public function __construct($sudo) {
		//$this->sudo = $sudo;
		$this->sudo = false;
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 */
	public function sendRaw($packet) {
		$cmd = $this->sudo ? 'sudo ' : '';
		$cmd .= 'iqrfapp raw ' . $packet;
		return shell_exec($cmd);
	}

	/**
	 * Validate raw IQRF packet
	 * @param string $packet Raw IQRF packet
	 * @return bool Status
	 */
	public function validatePacket($packet) {
		$pattern = '/^([0-9a-fA-F]{1,2}(\.|\ )){1,64}[0-9a-fA-F]{1,2}$/';
		return preg_match($pattern, $packet);
	}

}