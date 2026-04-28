<?php
/**
 * Register PMPro abilities for the WordPress Abilities API.
 *
 * @since 3.8.0
 */

/**
 * Determine whether PMPro should load Abilities API integrations.
 *
 * @since 3.8.0
 *
 * @return bool
 */
function pmpro_abilities_api_enabled() {
	if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'wp_register_ability_category' ) ) {
		return false;
	}

	/**
	 * Filter whether PMPro should register Abilities API categories and abilities.
	 *
	 * @since 3.8.0
	 *
	 * @param bool $enabled Whether ability registration is enabled.
	 */
	return apply_filters( 'pmpro_enable_abilities_api', true );
}

/**
 * Set up PMPro Abilities API hooks.
 *
 * @since 3.8.0
 */
function pmpro_setup_abilities_api() {
	if ( ! pmpro_abilities_api_enabled() ) {
		return;
	}

	add_action( 'wp_abilities_api_categories_init', 'pmpro_register_ability_categories' );
	add_action( 'wp_abilities_api_init', 'pmpro_register_abilities' );
}
add_action( 'plugins_loaded', 'pmpro_setup_abilities_api', 20 );

/**
 * Register PMPro ability categories.
 *
 * @since 3.8.0
 */
function pmpro_register_ability_categories() {
	wp_register_ability_category(
		'pmpro-memberships',
		array(
			'label'       => __( 'PMPro Memberships', 'paid-memberships-pro' ),
			'description' => __( 'Read and manage member access and membership levels in Paid Memberships Pro.', 'paid-memberships-pro' ),
		)
	);

	wp_register_ability_category(
		'pmpro-reports',
		array(
			'label'       => __( 'PMPro Reports', 'paid-memberships-pro' ),
			'description' => __( 'Read reporting metadata for Paid Memberships Pro.', 'paid-memberships-pro' ),
		)
	);
}

/**
 * Register PMPro abilities.
 *
 * @since 3.8.0
 */
function pmpro_register_abilities() {
	wp_register_ability(
		'pmpro/list-levels',
		array(
			'label'               => __( 'List PMPro Levels', 'paid-memberships-pro' ),
			'description'         => __( 'Lists all Paid Memberships Pro levels, including pricing and billing settings.', 'paid-memberships-pro' ),
			'category'            => 'pmpro-memberships',
			'execute_callback'    => 'pmpro_ability_list_levels',
			'permission_callback' => 'pmpro_ability_can_list_levels',
			'output_schema'       => array(
				'type'        => 'array',
				'description' => __( 'Membership levels available in PMPro.', 'paid-memberships-pro' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array(
							'type'        => 'integer',
							'description' => __( 'Membership level ID.', 'paid-memberships-pro' ),
						),
						'name'            => array(
							'type'        => 'string',
							'description' => __( 'Membership level name.', 'paid-memberships-pro' ),
						),
						'allow_signups'   => array(
							'type'        => 'boolean',
							'description' => __( 'Whether signups are allowed for the level.', 'paid-memberships-pro' ),
						),
						'initial_payment' => array(
							'type'        => 'number',
							'description' => __( 'Initial payment amount.', 'paid-memberships-pro' ),
						),
						'billing_amount'  => array(
							'type'        => 'number',
							'description' => __( 'Recurring billing amount.', 'paid-memberships-pro' ),
						),
						'cycle_number'    => array(
							'type'        => 'integer',
							'description' => __( 'Number of billing cycle units.', 'paid-memberships-pro' ),
						),
						'cycle_period'    => array(
							'type'        => 'string',
							'description' => __( 'Billing cycle period (e.g. Month).', 'paid-memberships-pro' ),
						),
					),
				),
			),
			'meta'               => array(
				'show_in_rest' => true,
			),
		)
	);

	wp_register_ability(
		'pmpro/list-reports',
		array(
			'label'               => __( 'List PMPro Reports', 'paid-memberships-pro' ),
			'description'         => __( 'Lists registered report slugs and names in Paid Memberships Pro.', 'paid-memberships-pro' ),
			'category'            => 'pmpro-reports',
			'execute_callback'    => 'pmpro_ability_list_reports',
			'permission_callback' => 'pmpro_ability_can_list_reports',
			'output_schema'       => array(
				'type'        => 'array',
				'description' => __( 'Registered PMPro reports.', 'paid-memberships-pro' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'slug'  => array(
							'type'        => 'string',
							'description' => __( 'The report slug.', 'paid-memberships-pro' ),
						),
						'label' => array(
							'type'        => 'string',
							'description' => __( 'The report label.', 'paid-memberships-pro' ),
						),
					),
				),
			),
			'meta'               => array(
				'show_in_rest' => true,
			),
		)
	);
}

/**
 * Permission callback for pmpro/list-levels.
 *
 * @since 3.8.0
 *
 * @return bool
 */
function pmpro_ability_can_list_levels() {
	return current_user_can( 'pmpro_membershiplevels' ) || current_user_can( 'manage_options' );
}

/**
 * Execute callback for pmpro/list-levels.
 *
 * @since 3.8.0
 *
 * @return array
 */
function pmpro_ability_list_levels() {
	$levels  = pmpro_getAllLevels( true );
	$results = array();

	foreach ( $levels as $level ) {
		$results[] = array(
			'id'              => (int) $level->id,
			'name'            => (string) $level->name,
			'allow_signups'   => ! empty( $level->allow_signups ),
			'initial_payment' => (float) $level->initial_payment,
			'billing_amount'  => (float) $level->billing_amount,
			'cycle_number'    => (int) $level->cycle_number,
			'cycle_period'    => (string) $level->cycle_period,
		);
	}

	return $results;
}

/**
 * Permission callback for pmpro/list-reports.
 *
 * @since 3.8.0
 *
 * @return bool
 */
function pmpro_ability_can_list_reports() {
	return current_user_can( 'pmpro_reports' ) || current_user_can( 'manage_options' );
}

/**
 * Execute callback for pmpro/list-reports.
 *
 * @since 3.8.0
 *
 * @return array
 */
function pmpro_ability_list_reports() {
	global $pmpro_reports;

	if ( ! is_array( $pmpro_reports ) ) {
		return array();
	}

	$results = array();
	foreach ( $pmpro_reports as $slug => $label ) {
		$results[] = array(
			'slug'  => (string) $slug,
			'label' => (string) $label,
		);
	}

	return $results;
}
