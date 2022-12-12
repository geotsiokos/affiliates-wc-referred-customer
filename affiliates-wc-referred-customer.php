<?php
/**
 * affiliates-wc-referred-customer.php
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author gtsiokos
 * @package affiliates-wc-referred-customer
 * @since 1.0.0
 *
 * Plugin Name: Affiliates WooCommerce Referred Customer
 * Plugin URI: https://www.netpad.gr/
 * Description: This plugin adds customers to the referring affiliate group upon their first completed order.
 * Version: 1.0.0
 * Author: gtsiokos
 * Author URI: hhttps://www.netpad.gr/
 * Donate-Link: https://www.netpad.gr/
 */

/**
 * Plugin class.
 */
class Affiliates_WC_Referred_Customer {

	/**
	 * Adds our action on plugins_loaded.
	 */
	public static function boot() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
	}

	/**
	 * Adds our action on completed order status if WooCommerce and Groups are present.
	 */
	public static function plugins_loaded() {
		if ( function_exists( 'wc_get_order' ) && defined( 'GROUPS_CORE_VERSION' ) ) {
			add_action( 'affiliates_referral', array( __CLASS__, 'affiliates_referral' ), 10, 2 );
		}
	}

	/**
	 * Reads the referral data and
	 * creates a group to add the referring 
	 * customer in it
	 *
	 * @param int $referral_id
	 * @param array $data
	 */
	public static function affiliates_referral( $referral_id, $data ) {
		$referral = new Affiliates_Referral();
		if ( $referral->read( $referral_id ) ) {
			$order_id = $referral->reference;
			$affiliate_id = $referral->affiliate_id;
			$referral_status = $referral->status;
			if ( $referral_status !== 'rejected' ) {
				if ( $order = wc_get_order( $order_id ) ) {
					if ( $user_id = $order->get_customer_id() ) {
						$affiliate_user_id = affiliates_get_affiliate_user( $affiliate_id );
						$affiliate_user = get_user_by( 'ID', $affiliate_user_id );
						if ( $affiliate_user ) {
							$name = $affiliate_user->user_login . '_group';
						}
						if ( $group = Groups_Group::read_by_name( $name ) ) {
							$group_id = $group->group_id;
						} else {
							$group_id = Groups_Group::create( array( 'name' => $name ) );
						}
						if ( $group_id ) {
							if ( !Groups_User_Group::read( $user_id, $group_id ) ) {
								Groups_User_Group::create( array( 'group_id' => $group_id, 'user_id' => $user_id ) );
							}
						}
					}
				}
			}
		}
	}
}
Affiliates_WC_Referred_Customer::boot();