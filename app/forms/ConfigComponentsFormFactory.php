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

use Nette;
use Nette\Application\UI\Form;
use App\Presenters\ConfigPresenter;
use App\Model\ConfigManager;

class ConfigComponentsFormFactory {

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

	public function __construct(FormFactory $factory, ConfigManager $configManager) {
		$this->factory = $factory;
		$this->configManager = $configManager;
	}

	/**
	 * Create components configuration form
	 * @param ConfigPresenter $presenter
	 * @return Form Components configuration form
	 */
	public function create(ConfigPresenter $presenter) {
		$form = $this->factory->create();
		$components = $this->configManager->read('config')['Components'];
		foreach ($components as $component) {
			$form->addCheckbox($component['ComponentName'], $component['ComponentName'])
					->setDefaultValue($component['Enabled']);
		}
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->configManager->saveComponents($values);
			$presenter->redirect('Config:');
		};

		return $form;
	}

}
