<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/digitalhydra
 * @since      1.0.0
 *
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Advance_Flat_Rates_For_Woocommerce
 * @subpackage Advance_Flat_Rates_For_Woocommerce/includes
 * @author     Jairo Rondon <jimnoname@gmail.com>
 */
class WC_Advance_Flat_Rate extends WC_Shipping_Method
{

	/**
	 * Cost passed to [fee] shortcode.
	 *
	 * @var string Cost.
	 */
	protected $fee_cost = '';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance ID.
	 */
	public function __construct($instance_id = 0)
	{
		$this->id                 = 'wc-adv-flat-rate';
		$this->instance_id        = absint($instance_id);
		$this->method_title       = __('Advance Flat rate', 'advance-flat-rates-for-woocommerce');
		$this->method_description = __('Lets you charge a fixed rate for shipping with some options.', 'advance-flat-rates-for-woocommerce');
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->init();

		add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
	}

	/**
	 * Init user set variables.
	 */
	public function init()
	{
		$this->init_form_fields();
		$this->title                = $this->get_option('title');
		$this->tax_status           = $this->get_option('tax_status');
		$this->cost                 = $this->get_option('cost');
		$this->type                 = $this->get_option('type', 'class');
	}

	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	// public function get_instance_form_fields() {
	// 	return parent::get_instance_form_fields();
	// }

	/**
	 * Init form fields.
	 */
	public function init_form_fields()
	{

		$cost_desc = __('Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'advance-flat-rates-for-woocommerce') . '<br/><br/>' . __('Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'advance-flat-rates-for-woocommerce');

		$this->instance_form_fields = array(
			'title'      => array(
				'title'       => __('Title', 'advance-flat-rates-for-woocommerce'),
				'type'        => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'advance-flat-rates-for-woocommerce'),
				'default'     => $this->method_title,
				'desc_tip'    => true,
			),
			'cost'       => array(
				'title'             => __('Cost', 'advance-flat-rates-for-woocommerce'),
				'type'              => 'text',
				'placeholder'       => '',
				'description'       => $cost_desc,
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array($this, 'sanitize_cost'),
			),
			'roles'   => array(
				'title'   => __('Apply to users with roles', 'advance-flat-rates-for-woocommerce'),
				'type'    => 'multiselect',
				'class'   => 'wc-enhanced-select',
				'default' => 'any',
				'options' => array_merge(array(
					'any'           => __('Any role|Not Logged In', 'advance-flat-rates-for-woocommerce'),
				), $this->get_roles_options()),
			),
			'min_amount' => array(
				'title'       => __('Minimum order amount', 'advance-flat-rates-for-woocommerce'),
				'type'        => 'price',
				'placeholder' => wc_format_localized_price(0),
				'description' => __('Users will need to spend this amount to enable this value, leave empty for disable.', 'advance-flat-rates-for-woocommerce'),
				'default'     => '0',
				'desc_tip'    => true,
			),
			'max_amount' => array(
				'title'       => __('Maximum order amount', 'advance-flat-rates-for-woocommerce'),
				'type'        => 'price',
				'placeholder' => wc_format_localized_price(0),
				'description' => __('Users will need to spend this amount to enable tis value, leave empty for disable.', 'advance-flat-rates-for-woocommerce'),
				'default'     => '0',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Evaluate a cost from a sum/string.
	 *
	 * @param  string $sum Sum of shipping.
	 * @param  array  $args Args, must contain `cost` and `qty` keys. Having `array()` as default is for back compat reasons.
	 * @return string
	 */
	protected function evaluate_cost($sum, $args = array())
	{
		// Add warning for subclasses.
		if (!is_array($args) || !array_key_exists('qty', $args) || !array_key_exists('cost', $args)) {
			wc_doing_it_wrong(__FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1');
		}

		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

		// Allow 3rd parties to process shipping cost arguments.
		$args           = apply_filters('woocommerce_evaluate_shipping_cost_args', $args, $sum, $this);
		$locale         = localeconv();
		$decimals       = array(wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',');
		$this->fee_cost = $args['cost'];

		// Expand shortcodes.
		add_shortcode('fee', array($this, 'fee'));

		$sum = do_shortcode(
			str_replace(
				array(
					'[qty]',
					'[cost]',
				),
				array(
					$args['qty'],
					$args['cost'],
				),
				$sum
			)
		);

		remove_shortcode('fee', array($this, 'fee'));

		// Remove whitespace from string.
		$sum = preg_replace('/\s+/', '', $sum);

		// Remove locale from string.
		$sum = str_replace($decimals, '.', $sum);

		// Trim invalid start/end characters.
		$sum = rtrim(ltrim($sum, "\t\n\r\0\x0B+*/"), "\t\n\r\0\x0B+-*/");

		// Do the math.
		return $sum ? WC_Eval_Math::evaluate($sum) : 0;
	}

	/**
	 * Work out fee (shortcode).
	 *
	 * @param  array $atts Attributes.
	 * @return string
	 */
	public function fee($atts)
	{
		$atts = shortcode_atts(
			array(
				'percent' => '',
				'min_fee' => '',
				'max_fee' => '',
			),
			$atts,
			'fee'
		);

		$calculated_fee = 0;

		if ($atts['percent']) {
			$calculated_fee = $this->fee_cost * (floatval($atts['percent']) / 100);
		}

		if ($atts['min_fee'] && $calculated_fee < $atts['min_fee']) {
			$calculated_fee = $atts['min_fee'];
		}

		if ($atts['max_fee'] && $calculated_fee > $atts['max_fee']) {
			$calculated_fee = $atts['max_fee'];
		}

		return $calculated_fee;
	}

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 */
	public function calculate_shipping($package = array())
	{
		$rate = array(
			'id'      => $this->get_rate_id(),
			'label'   => $this->title,
			'cost'    => 0,
			'package' => $package,
		);

		// Calculate the costs.
		$has_costs = false; // True when a cost is set. False if all costs are blank strings.
		$has_cart_min = false; // True when the cart subtotal equal the min_amount. False if cart subtotal is lower.
		$has_role = false; // True when user belongs in roles selected for this method.
		$cost      = $this->get_option('cost');
		$min      = $this->get_option('min_amount');
		$max      = $this->get_option('max_amount');
		$valid_roles      = $this->get_option('roles');

		if ('' !== $cost) {
			$has_costs    = true;

			if ('' !== $min  && is_numeric($min)) {
				$cart_subtotal = $package['cart_subtotal'];
				$has_cart_min = $cart_subtotal >= $min;

				$has_costs = $has_cart_min;
			}

			if ('' !== $max && is_numeric($max)) {
				$cart_subtotal = $package['cart_subtotal'];
				$has_cart_max = $cart_subtotal <= $max;

				$has_costs = $has_cart_max;
			}
			if ('' !== $min && '' !== $max && is_numeric($min) &&is_numeric($max)) {
				$cart_subtotal = $package['cart_subtotal'];
				$has_cart_min = $cart_subtotal >= $min;
				$has_cart_max = $cart_subtotal <= $max;

				$has_costs = $has_cart_min && $has_cart_max;
			}

			$rate['cost'] = $this->evaluate_cost(
				$cost,
				array(
					'qty'  => $this->get_package_item_qty($package),
					'cost' => $package['contents_cost'],
				)
			);
		}

		// Add shipping class costs.
		$shipping_classes = WC()->shipping()->get_shipping_classes();

		if (!empty($shipping_classes)) {
			$found_shipping_classes = $this->find_shipping_classes($package);
			$highest_class_cost     = 0;

			foreach ($found_shipping_classes as $shipping_class => $products) {
				// Also handles BW compatibility when slugs were used instead of ids.
				$shipping_class_term = get_term_by('slug', $shipping_class, 'product_shipping_class');
				$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option('class_cost_' . $shipping_class_term->term_id, $this->get_option('class_cost_' . $shipping_class, '')) : $this->get_option('no_class_cost', '');

				if ('' === $class_cost_string) {
					continue;
				}

				$has_costs  = true;
				$class_cost = $this->evaluate_cost(
					$class_cost_string,
					array(
						'qty'  => array_sum(wp_list_pluck($products, 'quantity')),
						'cost' => array_sum(wp_list_pluck($products, 'line_total')),
					)
				);

				if ('class' === $this->type) {
					$rate['cost'] += $class_cost;
				} else {
					$highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
				}
			}

			if ('order' === $this->type && $highest_class_cost) {
				$rate['cost'] += $highest_class_cost;
			}
		}

		if (is_user_logged_in() && !in_array('any', $valid_roles)) {

			$user = wp_get_current_user();
			$current_user_roles = (array) $user->roles;
			foreach ($current_user_roles as $key => $role) {
				if (in_array($role, $valid_roles)) {
					$has_role = true;
					break;
				}
			}
		} else {
			$has_role = true;
		}

		if ($has_costs && $has_role) {
			$this->add_rate($rate);
		}

		/**
		 * Developers can add additional flat rates based on this one via this action since @version 2.4.
		 *
		 * Previously there were (overly complex) options to add additional rates however this was not user.
		 * friendly and goes against what Flat Rate Shipping was originally intended for.
		 */
		do_action('woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate);
	}

	/**
	 * Get items in package.
	 *
	 * @param  array $package Package of items from cart.
	 * @return int
	 */
	public function get_package_item_qty($package)
	{
		$total_quantity = 0;
		foreach ($package['contents'] as $item_id => $values) {
			if ($values['quantity'] > 0 && $values['data']->needs_shipping()) {
				$total_quantity += $values['quantity'];
			}
		}
		return $total_quantity;
	}

	/**
	 * Finds and returns shipping classes and the products with said class.
	 *
	 * @param mixed $package Package of items from cart.
	 * @return array
	 */
	public function find_shipping_classes($package)
	{
		$found_shipping_classes = array();

		foreach ($package['contents'] as $item_id => $values) {
			if ($values['data']->needs_shipping()) {
				$found_class = $values['data']->get_shipping_class();

				if (!isset($found_shipping_classes[$found_class])) {
					$found_shipping_classes[$found_class] = array();
				}

				$found_shipping_classes[$found_class][$item_id] = $values;
			}
		}

		return $found_shipping_classes;
	}

	/**
	 * Sanitize the cost field.
	 *
	 * @since 3.4.0
	 * @param string $value Unsanitized value.
	 * @throws Exception Last error triggered.
	 * @return string
	 */
	public function sanitize_cost($value)
	{
		$value = is_null($value) ? '' : $value;
		$value = wp_kses_post(trim(wp_unslash($value)));
		$value = str_replace(array(get_woocommerce_currency_symbol(), html_entity_decode(get_woocommerce_currency_symbol())), '', $value);
		// Thrown an error on the front end if the evaluate_cost will fail.
		$dummy_cost = $this->evaluate_cost(
			$value,
			array(
				'cost' => 1,
				'qty'  => 1,
			)
		);
		if (false === $dummy_cost) {
			throw new Exception(WC_Eval_Math::$last_error);
		}
		return $value;
	}



	private function map_role_options($role)
	{
		return $role['name'];
	}

	private function get_roles_options()
	{
		$roles = wp_roles();
		$roles = $roles->roles;
		$role_options = array_map(array($this, 'map_role_options'), $roles);

		return $role_options;
	}
}
