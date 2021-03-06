<?php
	defined( 'ABSPATH' ) or die( 'This plugin must be run within the scope of WordPress.' );

	include_once( "pluginSettings.php" );
	include_once( "settingsPage.php" );
	include_once( "generalSettings.php" );
	include_once( "listSettings.php" );

	include_once( "detailSettings.php" );
	include_once( "bookingSettings.php" );
	include_once( "textSettings.php" );
	include_once( "styleSettings.php" );

	add_action( 'admin_init', 'eduadmin_settings_init' );
	add_action( 'admin_menu', 'eduadmin_backend_menu' );
	add_action( 'admin_enqueue_scripts', 'eduadmin_backend_content' );
	add_action( 'wp_enqueue_scripts', 'eduadmin_frontend_content', PHP_INT_MAX );
	add_action( 'add_meta_boxes', 'eduadmin_shortcode_metabox' );
	add_action( 'wp_footer', 'eduadmin_printJavascript' );

	function eduadmin_page_title( $title, $sep = "|" ) {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		global $eduapi;
		global $edutoken;
		global $wp;

		if ( $sep == null || empty( $sep ) ) {
			$sep = "|";
		}

		if ( isset( $wp ) && isset( $wp->query_vars ) && isset( $wp->query_vars["courseId"] ) ) {
			$edo = get_transient( 'eduadmin-listCourses' );
			if ( ! $edo ) {
				$filtering = new XFiltering();
				$f         = new XFilter( 'ShowOnWeb', '=', 'true' );
				$filtering->AddItem( $f );

				$edo = $eduapi->GetEducationObject( $edutoken, '', $filtering->ToString() );
				set_transient( 'eduadmin-listCourses', $edo, 6 * HOUR_IN_SECONDS );
			}

			foreach ( $edo as $object ) {
				$id = $object->ObjectID;
				if ( $id == $wp->query_vars["courseId"] ) {
					$selectedCourse = $object;
					break;
				}
			}

			if ( $selectedCourse != null ) {
				$titleField = get_option( 'eduadmin-pageTitleField', 'PublicName' );
				if ( stristr( $titleField, "attr_" ) !== false ) {
					$attrid = substr( $titleField, 5 );
					$ft     = new XFiltering();
					$f      = new XFilter( 'ObjectID', '=', $selectedCourse->ObjectID );
					$ft->AddItem( $f );
					$f = new XFilter( 'AttributeID', '=', $attrid );
					$ft->AddItem( $f );
					$objAttr = $eduapi->GetObjectAttribute( $edutoken, '', $ft->ToString() );
					if ( ! empty( $objAttr ) ) {
						$attr = $objAttr[0];
						switch ( $attr->AttributeTypeID ) {
							case 5:
								$value = $attr->AttributeAlternative;
								break;
								/*case 7:
									$value = $attr->AttributeDate;*/
								break;
							default:
								$value = $attr->AttributeValue;
								break;
						}
						if ( ! empty( $value ) && stristr( $title, $value ) === false ) {
							$title = $value . " " . $sep . " " . $title;
						} else {
							$title = $selectedCourse->ObjectName . " " . $sep . " " . $title;
						}
					} else {
						$title = $selectedCourse->ObjectName . " " . $sep . " " . $title;
					}
				} else {
					if ( ! empty( $selectedCourse->{$titleField} ) && stristr( $title, $selectedCourse->{$titleField} ) === false ) {
						$title = $selectedCourse->{$titleField} . " " . $sep . " " . $title;
					} else {
						$title = $selectedCourse->ObjectName . " " . $sep . " " . $title;
					}
				}
			}
		}
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];

		return $title;
	}

	add_filter( 'pre_get_document_title', 'eduadmin_page_title', PHP_INT_MAX, 2 );
	add_filter( 'wp_title', 'eduadmin_page_title', PHP_INT_MAX, 2 );
	add_filter( 'aioseop_title', 'eduadmin_page_title', PHP_INT_MAX, 2 );

	function eduadmin_settings_init() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		/* Credential settings */
		register_setting( 'eduadmin-credentials', 'eduadmin-api-key' );
		register_setting( 'eduadmin-credentials', 'eduadmin-credentials_have_changed' );

		/* Rewrite settings */
		register_setting( 'eduadmin-rewrite', 'eduadmin-options_have_changed' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-rewriteBaseUrl' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-listViewPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-loginViewPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-detailViewPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-bookingViewPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-thankYouPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-interestObjectPage' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-interestEventPage' );

		/* Booking settings */
		register_setting( 'eduadmin-booking', 'eduadmin-useLogin' );
		register_setting( 'eduadmin-booking', 'eduadmin-allowCustomerRegistration' );
		register_setting( 'eduadmin-booking', 'eduadmin-loginField' );

		register_setting( 'eduadmin-booking', 'eduadmin-singlePersonBooking' );
		register_setting( 'eduadmin-booking', 'eduadmin-customerGroupId' );
		register_setting( 'eduadmin-booking', 'eduadmin-currency' );
		register_setting( 'eduadmin-booking', 'eduadmin-bookingTermsLink' );
		register_setting( 'eduadmin-booking', 'eduadmin-useBookingTermsCheckbox' );
		register_setting( 'eduadmin-booking', 'eduadmin-javascript' );
		register_setting( 'eduadmin-booking', 'eduadmin-customerMatching' );
		register_setting( 'eduadmin-booking', 'eduadmin-selectPricename' );
		register_setting( 'eduadmin-booking', 'eduadmin-fieldOrder' );
		register_setting( 'eduadmin-booking', 'eduadmin-allowInterestRegObject' );
		register_setting( 'eduadmin-booking', 'eduadmin-allowInterestRegEvent' );
		register_setting( 'eduadmin-booking', 'eduadmin-hideSubEventDateTime' );
		register_setting( 'eduadmin-booking', 'eduadmin-allowDiscountCode' );
		register_setting( 'eduadmin-booking', 'eduadmin-noInvoiceFreeEvents' );
		register_setting( 'eduadmin-booking', 'eduadmin-hideInvoiceEmailField' );
		register_setting( 'eduadmin-booking', 'eduadmin-showInvoiceInformation' );
		register_setting( 'eduadmin-booking', 'eduadmin-validateCivicRegNo' );
		register_setting( 'eduadmin-booking', 'eduadmin-useLimitedDiscount' );
		register_setting( 'eduadmin-booking', 'eduadmin-blockEditIfLoggedIn' );

		/* Phrase settings */
		register_setting( 'eduadmin-phrases', 'eduadmin-phrases' );

		/* Style settings */
		register_setting( 'eduadmin-style', 'eduadmin-style' );

		/* Detail settings */
		register_setting( 'eduadmin-details', 'eduadmin-showDetailHeaders' );
		register_setting( 'eduadmin-details', 'eduadmin-detailTemplate' );
		register_setting( 'eduadmin-details', 'eduadmin-groupEventsByCity' );
		register_setting( 'eduadmin-details', 'eduadmin-pageTitleField' );

		/* List settings */
		register_setting( 'eduadmin-list', 'eduadmin-showEventsInList' );
		register_setting( 'eduadmin-list', 'eduadmin-listTemplate' );

		register_setting( 'eduadmin-list', 'eduadmin-allowLocationSearch' );
		register_setting( 'eduadmin-list', 'eduadmin-allowSubjectSearch' );
		register_setting( 'eduadmin-list', 'eduadmin-allowCategorySearch' );
		register_setting( 'eduadmin-list', 'eduadmin-allowLevelSearch' );

		register_setting( 'eduadmin-list', 'eduadmin-listSortOrder' );

		register_setting( 'eduadmin-list', 'eduadmin-layout-descriptionfield' );

		register_setting( 'eduadmin-list', 'eduadmin-showCourseImage' );
		register_setting( 'eduadmin-list', 'eduadmin-showCourseDescription' );
		register_setting( 'eduadmin-list', 'eduadmin-showNextEventDate' );
		register_setting( 'eduadmin-list', 'eduadmin-showCourseLocations' );
		register_setting( 'eduadmin-list', 'eduadmin-showEventPrice' );
		register_setting( 'eduadmin-list', 'eduadmin-showCourseDays' );
		register_setting( 'eduadmin-list', 'eduadmin-showCourseTimes' );
		register_setting( 'eduadmin-list', 'eduadmin-showEventVenueName' );
		register_setting( 'eduadmin-list', 'eduadmin-showWeekDays' );

		/* Plugin settings */
		register_setting( 'eduadmin-plugins', 'eduadmin-plugin-settings' );

		/* Global settings */
		register_setting( 'eduadmin-rewrite', 'eduadmin-spotsLeft' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-spotsSettings' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-alwaysFewSpots' );
		register_setting( 'eduadmin-rewrite', 'eduadmin-monthsToFetch' );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_frontend_content() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		$styleVersion               = filemtime( EDUADMIN_PLUGIN_PATH . '/content/style/frontendstyle.css' );
		wp_register_style( 'eduadmin_frontend_style', plugins_url( 'content/style/frontendstyle.css', dirname( __FILE__ ) ), false, dateVersion( $styleVersion ) );
		$customcss = get_option( 'eduadmin-style', '' );
		wp_enqueue_style( 'eduadmin_frontend_style' );
		wp_add_inline_style( 'eduadmin_frontend_style', $customcss );

		$scriptVersion = filemtime( EDUADMIN_PLUGIN_PATH . '/content/script/educlient/edu.apiclient.js' );
		wp_register_script( 'eduadmin_apiclient_script', plugins_url( 'content/script/educlient/edu.apiclient.js', dirname( __FILE__ ) ), false, dateVersion( $scriptVersion ) );
		wp_localize_script( 'eduadmin_apiclient_script', 'wp_edu',
		                    array(
			                    'BaseUrl'        => home_url(),
			                    'BaseUrlScripts' => plugins_url( 'content/script', dirname( __FILE__ ) ),
			                    'CourseFolder'   => get_option( 'eduadmin-rewriteBaseUrl' ),
			                    'AjaxUrl'        => rest_url( 'edu/v1' ),
		                    ) );
		wp_enqueue_script( 'eduadmin_apiclient_script', false, array( 'jquery' ) );

		$scriptVersion = filemtime( EDUADMIN_PLUGIN_PATH . '/content/script/frontendjs.js' );
		wp_register_script( 'eduadmin_frontend_script', plugins_url( 'content/script/frontendjs.js', dirname( __FILE__ ) ), false, dateVersion( $scriptVersion ) );
		wp_enqueue_script( 'eduadmin_frontend_script', false, array( 'jquery' ) );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_backend_content() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		$styleVersion               = filemtime( EDUADMIN_PLUGIN_PATH . '/content/style/adminstyle.css' );
		wp_register_style( 'eduadmin_admin_style', plugins_url( 'content/style/adminstyle.css', dirname( __FILE__ ) ), false, dateVersion( $styleVersion ) );
		wp_enqueue_style( 'eduadmin_admin_style' );

		$scriptVersion = filemtime( EDUADMIN_PLUGIN_PATH . '/content/script/adminjs.js' );
		wp_register_script( 'eduadmin_admin_script', plugins_url( 'content/script/adminjs.js', dirname( __FILE__ ) ), false, dateVersion( $scriptVersion ) );
		wp_enqueue_script( 'eduadmin_admin_script', false, array( 'jquery' ) );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_backend_menu() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		$level                      = 'administrator';
		add_menu_page( 'EduAdmin', 'EduAdmin', $level, 'eduadmin-settings', 'edu_render_general_settings', 'dashicons-welcome-learn-more' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - General', 'eduadmin-booking' ), __( 'General settings', 'eduadmin-booking' ), $level, 'eduadmin-settings', 'edu_render_general_settings' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - List view', 'eduadmin-booking' ), __( 'List settings', 'eduadmin-booking' ), $level, 'eduadmin-settings-view', 'edu_render_list_settings_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Detail view', 'eduadmin-booking' ), __( 'Detail settings', 'eduadmin-booking' ), $level, 'eduadmin-settings-detail', 'edu_render_detail_settings_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Booking view', 'eduadmin-booking' ), __( 'Booking settings', 'eduadmin-booking' ), $level, 'eduadmin-settings-booking', 'edu_render_booking_settings_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Translation', 'eduadmin-booking' ), __( 'Translation', 'eduadmin-booking' ), $level, 'eduadmin-settings-text', 'edu_render_text_settings_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Style', 'eduadmin-booking' ), __( 'Style settings', 'eduadmin-booking' ), $level, 'eduadmin-settings-style', 'edu_render_style_settings_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Plugins', 'eduadmin-booking' ), __( 'Plugins', 'eduadmin-booking' ), $level, 'eduadmin-settings-plugins', 'edu_render_plugin_page' );
		add_submenu_page( 'eduadmin-settings', __( 'EduAdmin - Api Authentication', 'eduadmin-booking' ), __( 'Api Authentication', 'eduadmin-booking' ), $level, 'eduadmin-settings-api', 'edu_render_settings_page' );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_shortcode_metabox() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		add_meta_box( 'eduadmin-metabox', __( 'EduAdmin - Shortcodes', 'eduadmin-booking' ), 'eduadmin_create_metabox', null, 'side', 'high' );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_create_metabox() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		include_once( "_metaBox.php" );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}

	function eduadmin_RewriteJavaScript( $script ) {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		global $eduapi;
		global $edutoken;

		if ( isset( $_REQUEST['edu-thankyou'] ) ) {
			if ( stripos( $script, "$" ) !== false ) {
				$ft = new XFiltering();
				$f  = new XFilter( 'EventCustomerLnkID', '=', intval( $_REQUEST['edu-thankyou'] ) );
				$ft->AddItem( $f );
				$bookingInfo = $eduapi->GetEventBooking( $edutoken, '', $ft->ToString() );

				$script = str_replace(
					array(
						'$bookingno$',
						'$productname$',
						'$totalsum$',
						'$participants$',
					),
					array(
						esc_js( $bookingInfo[0]->EventCustomerLnkID ), // $bookingno$
						esc_js( $bookingInfo[0]->ObjectName ), // $productname$
						esc_js( $bookingInfo[0]->TotalPrice ), // $totalsum$
						esc_js( $bookingInfo[0]->ParticipantNr ) // $participants$
					),
					$script
				);
			}
			EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];

			return $script;
		}
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];

		return '';
	}

	function eduadmin_printJavascript() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		if ( trim( get_option( 'eduadmin-javascript', '' ) ) != '' && isset( EDU()->session['eduadmin-printJS'] ) ) {
			$str    = "<script type=\"text/javascript\">\n";
			$script = get_option( 'eduadmin-javascript' );
			$str    .= eduadmin_RewriteJavaScript( $script );
			$str    .= "\n</script>";
			unset( EDU()->session['eduadmin-printJS'] );
			echo $str;
		}
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
	}