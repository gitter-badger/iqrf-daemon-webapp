<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Model\InstanceManager;
use App\ConfigModule\Forms\ConfigMqttFormFactory;
use App\ConfigModule\Forms\ConfigMqttAzureFormFactory;
use App\Presenters\BasePresenter;

class MqttPresenter extends BasePresenter {

	/**
	 * @var ConfigMqttAzureFormFactory
	 */
	private $azureFormFactory;

	/**
	 * @var ConfigMqttFormFactory
	 */
	private $formFactory;

	/**
	 * @var InstanceManager
	 */
	private $configManager;

	/**
	 * @var string
	 */
	private $fileName = 'MqttMessaging';

	/**
	 * Constructor
	 * @param ConfigMqttFormFactory $formFactory
	 * @param InstanceManager $configManager
	 */
	public function __construct(ConfigMqttFormFactory $formFactory, ConfigMqttAzureFormFactory $azureFormFactory, InstanceManager $configManager) {
		$this->azureFormFactory = $azureFormFactory;
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}

	/**
	 * Render list of MQTT interfaces
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->instances = $this->configManager->getInstances($this->fileName);
	}

	/**
	 * Edit MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function renderEdit($id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Delete MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function actionDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->delete($this->fileName, $id);
		$this->redirect('Mqtt:default');
		$this->setView('default');
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttAzureForm() {
		$this->onlyForAdmins();
		return $this->azureFormFactory->create($this);
	}

}
