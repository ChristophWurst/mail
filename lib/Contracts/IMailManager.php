<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Mail\Contracts;

use OCA\Mail\Account;
use OCA\Mail\Attachment;
use OCA\Mail\Db\Mailbox;
use OCA\Mail\Db\Message;
use OCA\Mail\Db\Tag;
use OCA\Mail\Exception\ClientException;
use OCA\Mail\Exception\ServiceException;
use OCA\Mail\Exception\SmimeDecryptException;
use OCA\Mail\Model\IMAPMessage;
use OCA\Mail\Service\Quota;
use OCA\Mail\Service\Search\SearchQuery;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Protocol-agnostic entry point for mailbox and message operations.
 *
 * Implementations dispatch to the account's configured protocol (IMAP or JMAP)
 * and keep the local database in sync with the remote server.
 */
interface IMailManager {

	/**
	 * @throws ClientException
	 */
	public function getMailbox(string $uid, int $id): Mailbox;

	/**
	 * @return Mailbox[]
	 * @throws ServiceException
	 */
	public function getMailboxes(Account $account, bool $forceSync = false): array;

	/**
	 * @param string[] $specialUse
	 * @throws ServiceException
	 * @throws DoesNotExistException the mailbox could not be re-read after being created on the remote (IMAP-backed accounts only)
	 */
	public function createMailbox(Account $account, string $name, array $specialUse = []): Mailbox;

	/**
	 * @throws ServiceException
	 */
	public function renameMailbox(Account $account, Mailbox $mailbox, string $name): Mailbox;

	/**
	 * @throws ServiceException
	 */
	public function deleteMailbox(Account $account, Mailbox $mailbox): void;

	/**
	 * @throws ServiceException
	 * @throws DoesNotExistException the mailbox could not be re-read after being updated on the remote (IMAP-backed accounts only)
	 */
	public function updateSubscription(Account $account, Mailbox $mailbox, bool $subscribed): Mailbox;

	/**
	 * @throws ClientException
	 * @throws ServiceException
	 */
	public function clearMailbox(Account $account, Mailbox $mailbox): void;

	/**
	 * @throws DoesNotExistException
	 */
	public function getMessage(string $uid, int $id): Message;

	/**
	 * @throws ClientException
	 * @throws ServiceException
	 * @throws SmimeDecryptException
	 */
	public function getImapMessage(Account $account, Mailbox $mailbox, Message $message, bool $loadBody = false): IMAPMessage;

	/**
	 * @return IMAPMessage[]
	 * @throws ServiceException
	 * @throws SmimeDecryptException
	 */
	public function getImapMessages(Account $account, Mailbox $mailbox, bool $loadBody = false, Message ...$messages): array;

	/**
	 * @throws ClientException
	 * @throws ServiceException
	 */
	public function getRawMessage(Account $account, Mailbox $mailbox, Message $message): ?string;

	public function getMessageIdForUid(Mailbox $mailbox, $uid): ?int;

	/**
	 * @return Message[]
	 */
	public function getMessagesByMessageId(Account $account, string $messageId): array;

	/**
	 * @return int[]
	 * @throws ServiceException
	 */
	public function findMessages(Account $account, Mailbox $mailbox, SearchQuery $searchQuery): array;

	/**
	 * @return ?int the new UID (or null if couldn't be determined)
	 * @throws ServiceException
	 */
	public function moveMessage(Account $sourceAccount, Mailbox $sourceMailbox, Message $message, Account $destinationAccount, Mailbox $destinationMailbox): ?int;

	/**
	 * @return int[] the new UIDs of the moved messages
	 * @throws ServiceException
	 */
	public function moveMessages(Account $account, Mailbox $targetMailbox, Mailbox $sourceMailbox, Message ...$messages): array;

	/**
	 * @throws ClientException
	 * @throws ServiceException
	 * @todo evaluate if we should sync mailboxes first
	 */
	public function deleteMessage(Account $account, Mailbox $mailbox, Message $message): void;

	/**
	 * @throws ClientException
	 * @throws ServiceException
	 */
	public function deleteMessages(Account $account, Mailbox $sourceMailbox, Message ...$messages): void;

	/**
	 * @throws ServiceException
	 */
	public function flagMessages(Account $account, Mailbox $mailbox, string $flag, bool $value, Message ...$messages): void;

	/**
	 * @throws ServiceException
	 */
	public function tagMessages(Account $account, Mailbox $mailbox, Tag $tag, bool $value, Message ...$messages): void;

	/**
	 * Mark all messages of a mailbox as read.
	 *
	 * @throws ServiceException
	 */
	public function markFolderAsRead(Account $account, Mailbox $mailbox): void;

	/**
	 * @return Message[]
	 */
	public function getThread(Account $account, string $threadRootId): array;

	/**
	 * Finds all messages in the thread of the given thread root id.
	 *
	 * @return array<string, Message> array of messages in the thread, keyed by remote id
	 * @throws DoesNotExistException
	 */
	public function fetchThread(Account $account, Mailbox $mailbox, string $threadRootId): array;

	/**
	 * @return int[] the new UIDs of the moved messages
	 * @throws DoesNotExistException
	 * @throws ServiceException
	 */
	public function moveThread(Account $srcAccount, Mailbox $srcMailbox, Account $dstAccount, Mailbox $dstMailbox, string $threadRootId): array;

	/**
	 * @throws ClientException
	 * @throws DoesNotExistException
	 * @throws ServiceException
	 */
	public function deleteThread(Account $account, Mailbox $mailbox, string $threadRootId): void;

	/**
	 * @return Attachment[]
	 * @throws DoesNotExistException
	 * @throws ServiceException
	 */
	public function getMailAttachments(Account $account, Mailbox $mailbox, Message $message): array;

	/**
	 * @throws DoesNotExistException
	 * @throws ServiceException
	 */
	public function getMailAttachment(Account $account, Mailbox $mailbox, Message $message, string $attachmentId): Attachment;

	/**
	 * @see https://tools.ietf.org/html/rfc2087
	 * @throws ServiceException
	 */
	public function getQuota(Account $account): ?Quota;

	/**
	 * Check the account's protocol/server for support of permanent flags.
	 *
	 * @throws ServiceException
	 */
	public function isPermflagsEnabled(Account $account, Mailbox $mailbox): bool;

	/**
	 * @throws ClientException if no tag with this label exists
	 */
	public function getTagByLabel(string $imapLabel, string $userId): Tag;

	/**
	 * @throws ClientException
	 */
	public function createTag(string $displayName, string $color, string $userId): Tag;

	/**
	 * @throws ClientException if the tag does not exist
	 */
	public function updateTag(int $id, string $displayName, string $color, string $userId): Tag;

	/**
	 * @param Account[] $accounts accounts to remove the tag from
	 * @throws ClientException
	 * @throws ServiceException
	 */
	public function deleteTag(int $id, string $userId, array $accounts): Tag;

}
