<?php

class Cloudns_Forwards {
	/**
	 * @var Cloudns_Core
	 */
	protected $core;

	public function __construct ($params) {
		$this->core = Cloudns_Core::inst($params);
	}

	/**
	 * Adds a new mail forward.
	 *
	 * @param string $zone
	 * @param string $from Local mailbox part before @domain
	 * @param string $to Destination email address
	 * @return array
	 */
	public function addForward ($zone, $from, $to) {
		$request = array(
			'domain-name' => $zone,
			'box' => $from,
			'destination' => $to,
		);

		return $this->core->Api->call('dns/add-mail-forward.json', $request);
	}

	/**
	 * Gets all mail forwards for a zone.
	 *
	 * @param string $zone
	 * @return array
	 */
	public function getForwards ($zone) {
		$request = array('domain-name' => $zone);
		$response = $this->core->Api->call('dns/mail-forwards.json', $request);

		if (isset($response['status']) && $response['status'] == 0) {
			return array();
		}

		return $response;
	}

	/**
	 * Updates a mail forward.
	 *
	 * @param string $zone
	 * @param int|string $id
	 * @param string $source Local mailbox part before @domain
	 * @param string $destination Destination email address
	 * @return array
	 */
	public function forwardEdit ($zone, $id, $source, $destination) {
		$request = array(
			'domain-name' => $zone,
			'mail-forward-id' => $id,
			'box' => $source,
			'destination' => $destination,
		);

		return $this->core->Api->call('dns/modify-mail-forward.json', $request);
	}

	/**
	 * Deletes a mail forward.
	 *
	 * @param string $zone
	 * @param int|string $forwardId
	 * @return array
	 */
	public function delete ($zone, $forwardId) {
		$request = array(
			'domain-name' => $zone,
			'mail-forward-id' => $forwardId,
		);

		return $this->core->Api->call('dns/delete-mail-forward.json', $request);
	}
}
