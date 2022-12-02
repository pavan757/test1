<?php
if ( ! class_exists( 'Worky_Theme_Setup' ) ) {

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since 1.0.0
	 */
	class Worky_Theme_Setup {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * True if the page is a blog or archive.
		 *
		 * @since 1.0.0
		 * @var   Boolean
		 */
		private $is_blog = false;

		/**
		 * Sidebar position.
		 *
		 * @since 1.0.0
		 * @var   String
		 */
		public $sidebar_position = 'none';

		/**
		 * Loaded modules
		 *
		 * @var array
		 */
		public $modules = array();

		/**
		 * Theme version
		 *
		 * @var string
		 */
		public $version;

		/**
		 * Sets up needed actions/filters for the theme to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$template      = get_template();
			$theme_obj     = wp_get_theme( $template );
			$this->version = $theme_obj->get( 'Version' );

			// Load the theme modules.
			add_action( 'after_setup_theme', array( $this, 'worky_framework_loader' ), -20 );

			// Initialization of customizer.
			add_action( 'after_setup_theme', array( $this, 'worky_customizer' ) );

			// Initialization of breadcrumbs module
			add_action( 'wp_head', array( $this, 'worky_breadcrumbs' ) );

			// Language functions and translations setup.
			add_action( 'after_setup_theme', array( $this, 'l10n' ), 2 );

			// Handle theme supported features.
			add_action( 'after_setup_theme', array( $this, 'theme_support' ), 3 );

			// Load the theme includes.
			add_action( 'after_setup_theme', array( $this, 'includes' ), 4 );

			// Init properties.
			add_action( 'wp_head', array( $this, 'worky_init_properties' ) );

			// Register public assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 9 );

			// Enqueue scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			// Enqueue styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );

		}

		/**
		 * Retuns theme version
		 *
		 * @return string
		 */
		public function version() {
			return apply_filters( 'worky-theme/version', $this->version );
		}

		/**
		 * Load the theme modules.
		 *
		 * @since  1.0.0
		 */
		public function worky_framework_loader() {

			require get_theme_file_path( 'framework/loader.php' );

			new Worky_CX_Loader(
				array(
					get_theme_file_path( 'framework/modules/customizer/cherry-x-customizer.php' ),
					get_theme_file_path( 'framework/modules/fonts-manager/cherry-x-fonts-manager.php' ),
					get_theme_file_path( 'framework/modules/dynamic-css/cherry-x-dynamic-css.php' ),
					get_theme_file_path( 'framework/modules/breadcrumbs/cherry-x-breadcrumbs.php' ),
				)
			);

		}

		/**
		 * Run initialization of customizer.
		 *
		 * @since 1.0.0
		 */
		public function worky_customizer() {

			$this->customizer = new CX_Customizer( worky_get_customizer_options() );
			$this->dynamic_css = new CX_Dynamic_CSS( worky_get_dynamic_css_options() );

		}

		/**
		 * Run initialization of breadcrumbs.
		 *
		 * @since 1.0.0
		 */
		public function worky_breadcrumbs() {

			$this->breadcrumbs = new CX_Breadcrumbs( worky_get_breadcrumbs_options() );

		}

		/**
		 * Run init init properties.
		 *
		 * @since 1.0.0
		 */
		public function worky_init_properties() {

			$this->is_blog = is_home() || ( is_archive() && ! is_tax() && ! is_post_type_archive() ) ? true : false;

			// Blog list properties init
			if ( $this->is_blog ) {
				$this->sidebar_position = worky_theme()->customizer->get_value( 'blog_sidebar_position' );
			}

			// Single blog properties init
			if ( is_singular( 'post' ) ) {
				$this->sidebar_position = worky_theme()->customizer->get_value( 'single_sidebar_position' );
			}

		}

		/**
		 * Loads the theme translation file.
		 *
		 * @since 1.0.0
		 */
		public function l10n() {

			/*
			 * Make theme available for translation.
			 * Translations can be filed in the /languages/ directory.
			 */
			load_theme_textdomain( 'worky', get_theme_file_path( 'languages' ) );

		}

		/**
		 * Adds theme supported features.
		 *
		 * @since 1.0.0
		 */
		public function theme_support() {

			global $content_width;

			if ( ! isset( $content_width ) ) {
				$content_width = 1200;
			}

			// Enable support for Post Thumbnails on posts and pages.
			add_theme_support( 'post-thumbnails' );

			// Enable HTML5 markup structure.
			add_theme_support( 'html5', array(
				'comment-list', 'comment-form', 'search-form', 'gallery', 'caption',
			) );

			// Enable default title tag.
			add_theme_support( 'title-tag' );

			// Enable post formats.
			add_theme_support( 'post-formats', array(
				'gallery', 'image', 'link', 'quote', 'video', 'audio',
			) );

			// Enable custom background.
			add_theme_support( 'custom-background', array( 'default-color' => 'ffffff', ) );

			// Add default posts and comments RSS feed links to head.
			add_theme_support( 'automatic-feed-links' );

		}

		/**
		 * Loads the theme files supported by themes and template-related functions/classes.
		 *
		 * @since 1.0.0
		 */
		public function includes() {

			/**
			 * Configurations.
			 */
			require_once get_theme_file_path( 'config/layout.php' );
			require_once get_theme_file_path( 'config/menus.php' );
			require_once get_theme_file_path( 'config/sidebars.php' );
			require_once get_theme_file_path( 'inc/register-plugins.php' );

			require_if_theme_supports( 'post-thumbnails', get_theme_file_path( 'config/thumbnails.php' ) );

			/**
			 * Classes.
			*/
			require_once get_theme_file_path( 'inc/classes/class-widget-area.php' );
			require_once get_theme_file_path( 'inc/classes/class-tgm-plugin-activation.php' );

			/**
			 * Worky specific config files
			 */
			require_once get_theme_file_path( 'config/plugins-wizard.php' );
			require_once get_theme_file_path( 'config/data-importer.php' );

			/**
			 * Functions.
			 */
			require_once get_theme_file_path( 'inc/template-tags.php' );
			require_once get_theme_file_path( 'inc/template-menu.php' );
			require_once get_theme_file_path( 'inc/template-comment.php' );
			require_once get_theme_file_path( 'inc/template-related-posts.php' );
			require_once get_theme_file_path( 'inc/extras.php' );
			require_once get_theme_file_path( 'inc/customizer.php' );
			require_once get_theme_file_path( 'inc/breadcrumbs.php' );
			require_once get_theme_file_path( 'inc/context.php' );
			require_once get_theme_file_path( 'inc/hooks.php' );

			/**
             * Admin class
             */
			require_once get_theme_file_path( 'inc/classes/admin.php' );

		}

		/**
		 * Register assets.
		 *
		 * @since 1.0.0
		 */
		public function register_assets() {

			wp_register_script(
				'magnific-popup',
				get_theme_file_uri( 'assets/lib/magnific-popup/jquery.magnific-popup.min.js' ),
				array( 'jquery' ),
				'1.1.0',
				true
			);

			wp_register_script(
				'jquery-swiper',
				get_theme_file_uri( 'assets/lib/swiper/swiper.jquery.min.js' ),
				array( 'jquery' ),
				'4.3.3',
				true
			);

			wp_register_script(
				'jquery-totop',
				get_theme_file_uri( 'assets/js/jquery.ui.totop.min.js' ),
				array( 'jquery' ),
				'1.2.0',
				true
			);

			wp_register_script(
				'responsive-menu',
				get_theme_file_uri( 'assets/js/responsive-menu.js' ),
				array(),
				'1.0.0',
				true
			);

			// register style
			wp_register_style(
				'font-awesome',
				get_theme_file_uri( 'assets/lib/font-awesome/font-awesome.min.css' ),
				array(),
				'4.7.0'
			);

			wp_register_style(
				'magnific-popup',
				get_theme_file_uri( 'assets/lib/magnific-popup/magnific-popup.min.css' ),
				array(),
				'1.1.0'
			);

			wp_register_style(
				'jquery-swiper',
				get_theme_file_uri( 'assets/lib/swiper/swiper.min.css' ),
				array(),
				'4.3.3'
			);

		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {

			/**
			 * Filter the depends on main theme script.
			 *
			 * @since 1.0.0
			 * @var   array
			 */
			$scripts_depends = 	apply_filters( 'worky-theme/assets-depends/script', array(
				'jquery',
				'responsive-menu'
			) );

			if ( $this->is_blog || is_singular( 'post' ) ) {
				array_push( $scripts_depends, 'magnific-popup', 'jquery-swiper' );
			}

			wp_enqueue_script(
				'worky-theme-script',
				get_theme_file_uri( 'assets/js/theme-script.js' ),
				$scripts_depends,
				$this->version(),
				true
			);

			// Threaded Comments.
			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}

		}

		/**
		 * Enqueue styles.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_styles() {

			/**
			 * Filter the depends on main theme styles.
			 *
			 * @since 1.0.0
			 * @var   array
			 */
			$styles_depends = apply_filters( 'worky-theme/assets-depends/styles', array(
				'font-awesome',
			) );

			if ( $this->is_blog || is_singular( 'post' ) ) {
				array_push($styles_depends, 'magnific-popup', 'jquery-swiper');
			}

			wp_enqueue_style(
				'worky-theme-style',
				get_stylesheet_uri(),
				$styles_depends,
				$this->version()
			);

			if ( is_rtl() ) {
				wp_enqueue_style(
					'rtl',
					get_theme_file_uri( 'rtl.css' ),
					false,
					$this->version()
				);
			}

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}
	}
}

/**
 * Returns instanse of main theme configuration class.
 *
 * @since  1.0.0
 * @return object
 */
function worky_theme() {

	return Worky_Theme_Setup::get_instance();

}

worky_theme();
