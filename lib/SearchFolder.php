<?php declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail;

use Horde_Imap_Client;
use Horde_Imap_Client_Mailbox;

class SearchFolder extends Folder {

	/**
	 * @param Account $account
	 * @param Horde_Imap_Client_Mailbox $mailbox
	 * @param array $attributes
	 * @param string $delimiter
	 */
	public function __construct(int $accountId, Horde_Imap_Client_Mailbox $mailbox, array $attributes, $delimiter) {
		$attributes[] = Horde_Imap_Client::SPECIALUSE_FLAGGED;
		parent::__construct($accountId, $mailbox, $attributes, $delimiter);
	}

	/**
	 * @return string
	 */
	public function getMailbox() {
		return parent::getMailbox() . '/FLAGGED';
	}

	/**
	 * @return boolean
	 */
	public function isSearchable() {
		return false;
	}

}
