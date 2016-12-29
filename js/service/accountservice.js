/**
 * Mail
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Christoph Wurst 2015
 */

define(function(require) {
	'use strict';

	var $ = require('jquery');
	var OC = require('OC');
	var Radio = require('radio');

	Radio.account.reply('create', createAccount);
	Radio.account.reply('entities', getAccountEntities);

	function createAccount(config) {
		return new Promise(function(resolve, reject) {
			$.ajax(OC.generateUrl('apps/mail/accounts'), {
				data: config,
				type: 'POST',
				success: function() {
					resolve();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					switch (jqXHR.status) {
						case 400:
							var response = JSON.parse(jqXHR.responseText);
							reject(response.message);
							break;
						default:
							var error = errorThrown || textStatus || t('mail', 'Unknown error');
							reject(t('mail', 'Error while creating an account: ' + error));
					}
				}
			});
		});
	}

	function getAccountEntities() {
		return new Promise(function(resolve, reject) {
			var $serialized = $('#serialized-accounts');
			var accounts = require('state').accounts;

			if ($serialized.val() !== '') {
				var serialized = $serialized.val();
				var serialzedAccounts = JSON.parse(atob(serialized));

				accounts.reset();
				for (var i = 0; i < serialzedAccounts.length; i++) {
					accounts.add(serialzedAccounts[i]);
				}
				resolve(accounts);

				$serialized.val('');
			} else {
				accounts.fetch({
					success: function(accounts) {
						require('cache').cleanUp(accounts);
						resolve(accounts);
					},
					error: function() {
						reject();
					}
				});
			}
		});
	}

	return {
		createAccount: createAccount,
		getAccountEntities: getAccountEntities
	};
});
