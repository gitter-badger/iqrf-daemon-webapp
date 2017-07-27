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

namespace App\Forms;

use App\Forms\FormFactory;
use App\Model\ConfigManager;
use App\Presenters\ConfigPresenter;
use Nette;
use Nette\Application\UI\Form;

class ConfigMainFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 * @inject
	 */
	private $configManager;

	/**
	 * @var FormFactory
	 * @inject
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param ConfigManager $configManager
	 */
	public function __construct(FormFactory $factory, ConfigManager $configManager) {
		$this->factory = $factory;
		$this->configManager = $configManager;
	}

	/**
	 * Create Tracer configuration form
	 * @param ConfigPresenter $presenter
	 * @return Form Tracer configuration form
	 */
	public function create(ConfigPresenter $presenter) {
		$form = $this->factory->create();
		$json = $this->configManager->read('config');
		$items = ['forwarding' => 'Forwarding', 'operational' => 'Operational', 'service' => 'Service'];
		$form->addText('Configuration', 'Configuration');
		$form->addText('ConfigurationDir', 'ConfigurationDir');
		$form->addInteger('WatchDogTimeoutMilis', 'WatchDogTimeoutMilis');
		$form->addSelect('Mode', 'Mode', $items);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($json);
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->configManager->saveMain($values);
			$presenter->redirect('Config:default');
		};
		return $form;
	}

}