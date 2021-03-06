<?php
	function sendForgottenPassword( $loginValue ) {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		$eduapi                     = EDU()->api;
		$edutoken                   = EDU()->get_token();
		$ccId                       = 0;

		$loginField = get_option( 'eduadmin-loginField', 'Email' );

		$filter = new XFiltering();
		$f      = new XFilter( $loginField, '=', sanitize_text_field( $loginValue ) );
		$filter->AddItem( $f );
		$f = new XFilter( 'CanLogin', '=', true );
		$filter->AddItem( $f );
		$cc = $eduapi->GetCustomerContact( $edutoken, '', $filter->ToString(), false );
		if ( count( $cc ) == 1 ) {
			$ccId = current( $cc )->CustomerContactID;
		}

		if ( $ccId > 0 && ! empty( current( $cc )->Email ) ) {
			$sent                       = $eduapi->SendCustomerContactPassword( $edutoken, $ccId, get_bloginfo( 'name' ) );
			EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
			return $sent;
		}
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
		return false;
	}

	function logoutUser() {
		EDU()->timers[ __METHOD__ ] = microtime( true );
		$surl                       = get_home_url();
		$cat                        = get_option( 'eduadmin-rewriteBaseUrl' );

		$baseUrl = $surl . '/' . $cat;

		unset( EDU()->session['eduadmin-loginUser'] );
		unset( EDU()->session['needsLogin'] );
		unset( EDU()->session['checkEmail'] );
		EDU()->session->regenerate_id( true );
		unset( $_COOKIE['eduadmin-loginUser'] );
		setcookie( 'eduadmin_loginUser', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		wp_redirect( $baseUrl . edu_getQueryString() );
		EDU()->timers[ __METHOD__ ] = microtime( true ) - EDU()->timers[ __METHOD__ ];
		exit();
	}

	add_action(
		'wp_loaded',
		function() {
			$apiKey = get_option( 'eduadmin-api-key' );

			if ( ! $apiKey || empty( $apiKey ) ) {
				add_action( 'admin_notices', array( 'EduAdmin', 'SetupWarning' ) );
			} else {
				$key = DecryptApiKey( $apiKey );
				if ( ! $key ) {
					add_action( 'admin_notices', array( 'EduAdmin', 'SetupWarning' ) );

					return;
				}

				$cat = get_option( 'eduadmin-rewriteBaseUrl' );

				if ( stristr( $_SERVER['REQUEST_URI'], "/$cat/profile/logout" ) !== false ) {
					logoutUser();
				}

				/* BACKEND FUNCTIONS FOR FORMS */
				if ( isset( $_POST['eduformloginaction'] ) ) {
					$act = sanitize_text_field( $_POST['eduformloginaction'] );
					if ( isset( $_POST['eduadminloginEmail'] ) ) {
						switch ( $act ) {
							case "forgot":
								$success                                  = sendForgottenPassword( $_POST['eduadminloginEmail'] );
								EDU()->session['eduadmin-forgotPassSent'] = $success;
								break;
						}
					} else {
						EDU()->session['eduadminLoginError'] = edu__( "You have to provide your login credentials." );
					}
				}
			}
		} );