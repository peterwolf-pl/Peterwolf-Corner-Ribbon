<?php
/**
 * Core plugin class.
 *
 * @package PeterwolfCornerRibbon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers ribbon settings and renders the frontend ribbon.
 */
class Peterwolf_Corner_Ribbon {

	/**
	 * Option key used to store plugin settings.
	 */
	const OPTION_NAME = 'pwcr_settings';

	/**
	 * Settings screen slug.
	 */
	const PAGE_SLUG = 'peterwolf-corner-ribbon';

	/**
	 * Hooks plugin features into WordPress.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_ribbon' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( PWCR_FILE ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Creates settings with sensible first-use defaults.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, self::get_defaults() );
		}
	}

	/**
	 * Adds the settings page under Appearance.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_theme_page(
			__( 'Corner Ribbon', 'peterwolf-corner-ribbon' ),
			__( 'Corner Ribbon', 'peterwolf-corner-ribbon' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers plugin settings with the WordPress Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'pwcr_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'default'           => self::get_defaults(),
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * Adds admin styling and WordPress color picker behavior only on this screen.
	 *
	 * @param string $hook_suffix Current admin page identifier.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'appearance_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'pwcr-admin',
			PWCR_URL . 'assets/admin.css',
			array( 'wp-color-picker' ),
			$this->get_asset_version( 'assets/admin.css' )
		);
		wp_enqueue_script(
			'pwcr-admin',
			PWCR_URL . 'assets/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			$this->get_asset_version( 'assets/admin.js' ),
			true
		);
	}

	/**
	 * Adds the frontend stylesheet when the ribbon is enabled.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		$settings = $this->get_settings();

		if ( ! $settings['enabled'] || '' === $settings['text'] ) {
			return;
		}

		wp_enqueue_style(
			'pwcr-ribbon',
			PWCR_URL . 'assets/frontend.css',
			array(),
			$this->get_asset_version( 'assets/frontend.css' )
		);

		wp_add_inline_style( 'pwcr-ribbon', $this->get_custom_css( $settings ) );
	}

	/**
	 * Outputs the configured ribbon at the end of the public page.
	 *
	 * @return void
	 */
	public function render_ribbon() {
		$settings = $this->get_settings();

		if ( ! $settings['enabled'] || '' === $settings['text'] ) {
			return;
		}

		?>
		<aside class="pwcr-ribbon pwcr-ribbon--<?php echo esc_attr( $settings['side'] ); ?>" aria-label="<?php esc_attr_e( 'Site ribbon', 'peterwolf-corner-ribbon' ); ?>">
			<span class="pwcr-ribbon__text"><?php echo nl2br( esc_html( $settings['text'] ) ); ?></span>
		</aside>
		<?php
	}

	/**
	 * Adds a direct Settings link on the Plugins screen.
	 *
	 * @param array<int,string> $links Existing action links.
	 * @return array<int,string>
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'themes.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'peterwolf-corner-ribbon' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Validates all submitted plugin settings.
	 *
	 * @param mixed $input Submitted option value.
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( $input ) {
		$defaults = self::get_defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = $defaults;

		$output['enabled']        = isset( $input['enabled'] );
		$output['text']           = isset( $input['text'] ) ? sanitize_textarea_field( $input['text'] ) : '';
		$output['side']           = $this->allowed_value( $input, 'side', array( 'left', 'right' ), $defaults['side'] );
		$output['background']     = $this->sanitize_color( $input, 'background', $defaults['background'] );
		$output['text_color']     = $this->sanitize_color( $input, 'text_color', $defaults['text_color'] );
		$output['shadow_enabled'] = isset( $input['shadow_enabled'] );
		$output['shadow_color']   = $this->sanitize_color( $input, 'shadow_color', $defaults['shadow_color'] );
		$output['width']          = $this->bounded_number( $input, 'width', 160, 1200, $defaults['width'] );
		$output['padding']        = $this->bounded_number( $input, 'padding', 4, 80, $defaults['padding'] );
		$output['top_offset']     = $this->bounded_number( $input, 'top_offset', -300, 800, $defaults['top_offset'] );
		$output['font_size']      = $this->bounded_number( $input, 'font_size', 8, 100, $defaults['font_size'] );
		$output['line_height']    = $this->bounded_number( $input, 'line_height', 8, 200, $defaults['line_height'] );
		$output['letter_spacing'] = $this->bounded_number( $input, 'letter_spacing', -5, 40, $defaults['letter_spacing'] );
		$output['font_family']    = $this->allowed_value( $input, 'font_family', array_keys( self::get_font_choices() ), $defaults['font_family'] );
		$output['font_weight']    = $this->allowed_value( $input, 'font_weight', array( '300', '400', '500', '600', '700' ), $defaults['font_weight'] );
		$output['text_transform'] = $this->allowed_value( $input, 'text_transform', array( 'none', 'uppercase' ), $defaults['text_transform'] );
		$output['z_index']        = $this->bounded_number( $input, 'z_index', 1, 2147483647, $defaults['z_index'] );

		return $output;
	}

	/**
	 * Displays the settings form.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->get_settings();
		?>
		<div class="wrap pwcr-settings">
			<h1><?php esc_html_e( 'Peterwolf Corner Ribbon', 'peterwolf-corner-ribbon' ); ?></h1>
			<p><?php esc_html_e( 'Add a diagonal ribbon above any public-facing site layout. The ribbon remains fixed to the selected upper corner while visitors scroll.', 'peterwolf-corner-ribbon' ); ?></p>
			<form action="options.php" method="post">
				<?php settings_fields( 'pwcr_settings_group' ); ?>

				<h2><?php esc_html_e( 'Content and Position', 'peterwolf-corner-ribbon' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Display ribbon', 'peterwolf-corner-ribbon' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]" value="1" <?php checked( $settings['enabled'] ); ?>>
								<?php esc_html_e( 'Enable the ribbon on the frontend', 'peterwolf-corner-ribbon' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pwcr-text"><?php esc_html_e( 'Ribbon text', 'peterwolf-corner-ribbon' ); ?></label></th>
						<td>
							<textarea id="pwcr-text" class="large-text" rows="3" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[text]"><?php echo esc_textarea( $settings['text'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter one line or use line breaks for a multi-line ribbon.', 'peterwolf-corner-ribbon' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pwcr-side"><?php esc_html_e( 'Screen side', 'peterwolf-corner-ribbon' ); ?></label></th>
						<td>
							<select id="pwcr-side" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[side]">
								<option value="right" <?php selected( $settings['side'], 'right' ); ?>><?php esc_html_e( 'Right corner', 'peterwolf-corner-ribbon' ); ?></option>
								<option value="left" <?php selected( $settings['side'], 'left' ); ?>><?php esc_html_e( 'Left corner', 'peterwolf-corner-ribbon' ); ?></option>
							</select>
						</td>
					</tr>
					<?php $this->render_number_field( 'top_offset', __( 'Top offset', 'peterwolf-corner-ribbon' ), $settings['top_offset'], -300, 800, 'px', __( 'Moves the ribbon vertically from the top edge.', 'peterwolf-corner-ribbon' ) ); ?>
					<?php $this->render_number_field( 'z_index', __( 'Layer order (z-index)', 'peterwolf-corner-ribbon' ), $settings['z_index'], 1, 2147483647, '', __( 'Increase this when the theme covers the ribbon.', 'peterwolf-corner-ribbon' ) ); ?>
				</table>

				<h2><?php esc_html_e( 'Appearance', 'peterwolf-corner-ribbon' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php $this->render_color_field( 'background', __( 'Ribbon color', 'peterwolf-corner-ribbon' ), $settings['background'] ); ?>
					<?php $this->render_color_field( 'text_color', __( 'Text color', 'peterwolf-corner-ribbon' ), $settings['text_color'] ); ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Shadow', 'peterwolf-corner-ribbon' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[shadow_enabled]" value="1" <?php checked( $settings['shadow_enabled'] ); ?>>
								<?php esc_html_e( 'Display a drop shadow beneath the ribbon', 'peterwolf-corner-ribbon' ); ?>
							</label>
						</td>
					</tr>
					<?php $this->render_color_field( 'shadow_color', __( 'Shadow color', 'peterwolf-corner-ribbon' ), $settings['shadow_color'] ); ?>
					<?php $this->render_number_field( 'width', __( 'Ribbon width', 'peterwolf-corner-ribbon' ), $settings['width'], 160, 1200, 'px', __( 'Controls the length of the diagonal band.', 'peterwolf-corner-ribbon' ) ); ?>
					<?php $this->render_number_field( 'padding', __( 'Vertical padding', 'peterwolf-corner-ribbon' ), $settings['padding'], 4, 80, 'px', __( 'Controls the ribbon thickness around the text.', 'peterwolf-corner-ribbon' ) ); ?>
				</table>

				<h2><?php esc_html_e( 'Typography', 'peterwolf-corner-ribbon' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="pwcr-font-family"><?php esc_html_e( 'Font family', 'peterwolf-corner-ribbon' ); ?></label></th>
						<td>
							<select id="pwcr-font-family" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[font_family]">
								<?php foreach ( self::get_font_choices() as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['font_family'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<?php $this->render_number_field( 'font_size', __( 'Font size', 'peterwolf-corner-ribbon' ), $settings['font_size'], 8, 100, 'px', '' ); ?>
					<?php $this->render_number_field( 'line_height', __( 'Line height', 'peterwolf-corner-ribbon' ), $settings['line_height'], 8, 200, 'px', __( 'Controls the vertical spacing between lines in multi-line ribbon text.', 'peterwolf-corner-ribbon' ) ); ?>
					<?php $this->render_number_field( 'letter_spacing', __( 'Letter spacing', 'peterwolf-corner-ribbon' ), $settings['letter_spacing'], -5, 40, 'px', '' ); ?>
					<tr>
						<th scope="row"><label for="pwcr-font-weight"><?php esc_html_e( 'Font weight', 'peterwolf-corner-ribbon' ); ?></label></th>
						<td>
							<select id="pwcr-font-weight" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[font_weight]">
								<?php foreach ( array( '300' => __( 'Light', 'peterwolf-corner-ribbon' ), '400' => __( 'Regular', 'peterwolf-corner-ribbon' ), '500' => __( 'Medium', 'peterwolf-corner-ribbon' ), '600' => __( 'Semi-bold', 'peterwolf-corner-ribbon' ), '700' => __( 'Bold', 'peterwolf-corner-ribbon' ) ) as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['font_weight'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pwcr-transform"><?php esc_html_e( 'Letter case', 'peterwolf-corner-ribbon' ); ?></label></th>
						<td>
							<select id="pwcr-transform" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[text_transform]">
								<option value="none" <?php selected( $settings['text_transform'], 'none' ); ?>><?php esc_html_e( 'Keep entered text', 'peterwolf-corner-ribbon' ); ?></option>
								<option value="uppercase" <?php selected( $settings['text_transform'], 'uppercase' ); ?>><?php esc_html_e( 'Uppercase', 'peterwolf-corner-ribbon' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Ribbon Settings', 'peterwolf-corner-ribbon' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Outputs a reusable numeric setting field row.
	 *
	 * @param string $key         Option key.
	 * @param string $label       Visible label.
	 * @param int    $value       Current value.
	 * @param int    $min         Minimum allowed value.
	 * @param int    $max         Maximum allowed value.
	 * @param string $unit        Displayed measurement unit.
	 * @param string $description Optional help text.
	 * @return void
	 */
	private function render_number_field( $key, $label, $value, $min, $max, $unit, $description ) {
		?>
		<tr>
			<th scope="row"><label for="pwcr-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input id="pwcr-<?php echo esc_attr( $key ); ?>" class="small-text" type="number" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
				<?php echo esc_html( $unit ); ?>
				<?php if ( '' !== $description ) : ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Outputs a color picker setting row.
	 *
	 * @param string $key   Option key.
	 * @param string $label Visible label.
	 * @param string $value Current color.
	 * @return void
	 */
	private function render_color_field( $key, $label, $value ) {
		?>
		<tr>
			<th scope="row"><label for="pwcr-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input id="pwcr-<?php echo esc_attr( $key ); ?>" class="pwcr-color-field" type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>"></td>
		</tr>
		<?php
	}

	/**
	 * Builds safe CSS custom properties from sanitized option data.
	 *
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return string
	 */
	private function get_custom_css( $settings ) {
		$shadow = $settings['shadow_enabled'] ? '0 12px 24px ' . $settings['shadow_color'] : 'none';

		return sprintf(
			'.pwcr-ribbon{--pwcr-background:%1$s;--pwcr-text-color:%2$s;--pwcr-width:%3$dpx;--pwcr-padding:%4$dpx;--pwcr-top:%5$dpx;--pwcr-font-size:%6$dpx;--pwcr-line-height:%7$dpx;--pwcr-letter-spacing:%8$dpx;--pwcr-font-family:%9$s;--pwcr-font-weight:%10$s;--pwcr-transform:%11$s;--pwcr-shadow:%12$s;--pwcr-z-index:%13$d;}',
			$settings['background'],
			$settings['text_color'],
			$settings['width'],
			$settings['padding'],
			$settings['top_offset'],
			$settings['font_size'],
			$settings['line_height'],
			$settings['letter_spacing'],
			$settings['font_family'],
			$settings['font_weight'],
			$settings['text_transform'],
			$shadow,
			$settings['z_index']
		);
	}

	/**
	 * Returns stored settings merged with new defaults.
	 *
	 * @return array<string,mixed>
	 */
	private function get_settings() {
		$stored = get_option( self::OPTION_NAME, array() );
		$stored = is_array( $stored ) ? $stored : array();

		return wp_parse_args( $stored, self::get_defaults() );
	}

	/**
	 * Returns initial settings.
	 *
	 * @return array<string,mixed>
	 */
	private static function get_defaults() {
		return array(
			'enabled'        => true,
			'text'           => 'PREVIEW',
			'side'           => 'right',
			'background'     => '#c93828',
			'text_color'     => '#ffffff',
			'shadow_enabled' => true,
			'shadow_color'   => '#999999',
			'width'          => 390,
			'padding'        => 14,
			'top_offset'     => 74,
			'font_size'      => 31,
			'line_height'    => 39,
			'letter_spacing' => 7,
			'font_family'    => 'Georgia, "Times New Roman", serif',
			'font_weight'    => '400',
			'text_transform' => 'uppercase',
			'z_index'        => 99999,
		);
	}

	/**
	 * Returns supported CSS font stacks.
	 *
	 * @return array<string,string>
	 */
	private static function get_font_choices() {
		return array(
			'Georgia, "Times New Roman", serif'             => __( 'Georgia / Times New Roman', 'peterwolf-corner-ribbon' ),
			'"Times New Roman", Times, serif'                => __( 'Times New Roman', 'peterwolf-corner-ribbon' ),
			'Arial, Helvetica, sans-serif'                  => __( 'Arial / Helvetica', 'peterwolf-corner-ribbon' ),
			'"Trebuchet MS", Arial, sans-serif'              => __( 'Trebuchet MS', 'peterwolf-corner-ribbon' ),
			'"Courier New", Courier, monospace'              => __( 'Courier New', 'peterwolf-corner-ribbon' ),
			'system-ui, -apple-system, "Segoe UI", sans-serif' => __( 'System UI', 'peterwolf-corner-ribbon' ),
		);
	}

	/**
	 * Validates an option against an allowed value set.
	 *
	 * @param array<string,mixed> $input    Submitted settings.
	 * @param string              $key      Option key.
	 * @param array<int,string>   $allowed  Supported values.
	 * @param string              $fallback Fallback value.
	 * @return string
	 */
	private function allowed_value( $input, $key, $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';

		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * Validates a hexadecimal color setting.
	 *
	 * @param array<string,mixed> $input    Submitted settings.
	 * @param string              $key      Option key.
	 * @param string              $fallback Fallback color.
	 * @return string
	 */
	private function sanitize_color( $input, $key, $fallback ) {
		$color = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';

		return $color ? $color : $fallback;
	}

	/**
	 * Clamps a numeric setting to its safe bounds.
	 *
	 * @param array<string,mixed> $input    Submitted settings.
	 * @param string              $key      Option key.
	 * @param int                 $min      Minimum accepted value.
	 * @param int                 $max      Maximum accepted value.
	 * @param int                 $fallback Fallback value.
	 * @return int
	 */
	private function bounded_number( $input, $key, $min, $max, $fallback ) {
		if ( ! isset( $input[ $key ] ) || ! is_numeric( $input[ $key ] ) ) {
			return $fallback;
		}

		return max( $min, min( $max, (int) $input[ $key ] ) );
	}

	/**
	 * Uses file modification times during development to invalidate cached assets.
	 *
	 * @param string $relative_path Asset path relative to the plugin directory.
	 * @return string
	 */
	private function get_asset_version( $relative_path ) {
		$absolute_path = PWCR_DIR . ltrim( $relative_path, '/' );

		return file_exists( $absolute_path ) ? (string) filemtime( $absolute_path ) : PWCR_VERSION;
	}
}
