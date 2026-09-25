<?php

class Cloudns_Parkedtemplates {

	protected $core;
	protected $params;

	public function __construct($params) {
		$this->core = Cloudns_Core::inst($params);
		$this->params = $params;
	}

	public function getTemplates() {
		return $this->core->Api->call('dns/get-parked-templates.json', array());
	}

	public function getSettings($zone) {
		return $this->core->Api->call('dns/get-parked-settings.json', array(
			'domain-name' => $zone,
		));
	}

	public function applySettings($zone, $template, $title = '', $description = '', $keywords = '', $contactForm = '0') {
		$request = array(
			'domain-name' => $zone,
			'template' => (int)$template,
		);

		$title = trim((string)$title);
		$description = trim((string)$description);
		$keywords = trim((string)$keywords);
		$contactForm = trim((string)$contactForm);

		if ($title !== '') {
			$request['title'] = $title;
		}
		if ($description !== '') {
			$request['description'] = $description;
		}
		if ($keywords !== '') {
			$request['keywords'] = $keywords;
		}
		if ($contactForm === '1' || $contactForm === '2') {
			$request['contact-form'] = (int)$contactForm;
		}

		return $this->core->Api->call('dns/set-parked-settings.json', $request);
	}
}
