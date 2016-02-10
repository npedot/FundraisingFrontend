<?php


namespace WMDE\Fundraising\Frontend\Domain;

use WMDE\Fundraising\Entities\Subscription;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
interface SubscriptionRepository {
	public function storeSubscription( Subscription $subscription );

	/**
	 * Count the number of subscriptions with the same email address that were created after the cutoff date.
	 *
	 * @param Subscription $subscription
	 * @param \DateInterval $interval
	 * @return int
	 */
	public function countSimilar( Subscription $subscription, \DateTime $cutoffDateTime ): int;
}