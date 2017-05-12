<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Presenters;

use App\Model\GwInfoManager;

/**
 * Gateway Info presenter
 */
class GwInfoPresenter extends BasePresenter {

	/**
	 * @var GwInfoManager
	 * @inject
	 */
	private $gwInfoManager;

	/**
	 * @param GwInfoManager $gwInfoManager
	 */
	public function __construct(GwInfoManager $gwInfoManager) {
		$this->gwInfoManager = $gwInfoManager;
	}

	public function renderDefault() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->template->ipAddresses = $this->gwInfoManager->getIpAddresses();
		$this->template->macAddresses = $this->gwInfoManager->getMacAddresses();
		$this->template->hostname = $this->gwInfoManager->getHostname();
	}

}