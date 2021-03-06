<?php

	class EduAdminAPIController {
		var $namespace;

		public function __construct( $_edu ) {
			$this->namespace = "edu/v1";
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/courselist', array(
				'methods'  => 'POST',
				'callback' => 'edu_listview_courselist',
				'args'     => array(
					'objectIds'        => array( 'required' => true ),
					'baseUrl'          => array(),
					'courseFolder'     => array(),
					'showcoursedays'   => array(),
					'spotsleft'        => array(),
					'fewspots'         => array(),
					'spotsettings'     => array(),
					'city'             => array(),
					'category'         => array(),
					'subject'          => array(),
					'subjectid'        => array(),
					'courselevel'      => array(),
					'showcoursetimes'  => array(),
					'showcourseprices' => array(),
					'showweekdays'     => array(),
					'currency'         => array(),
					'search'           => array(),
					'showimages'       => array(),
					'template'         => array(),
					'numberofevents'   => array(),
					'fetchmonths'      => array(),
					'showvenue'        => array(),
					'groupbycity'      => array(),
					'showmore'         => array(),
					'eid'              => array(),
					'eventinquiry'     => array(),
					'orderby'          => array(),
					'order'            => array(),
				),
			) );

			register_rest_route( $this->namespace, '/courselist/events', array(
				'methods'  => 'POST',
				'callback' => 'edu_api_listview_eventlist',
				'args'     => array(
					'baseUrl'          => array(),
					'courseFolder'     => array(),
					'showcoursedays'   => array(),
					'spotsleft'        => array(),
					'fewspots'         => array(),
					'spotsettings'     => array(),
					'city'             => array(),
					'category'         => array(),
					'subject'          => array(),
					'subjectid'        => array(),
					'courselevel'      => array(),
					'showcoursetimes'  => array(),
					'showcourseprices' => array(),
					'showweekdays'     => array(),
					'currency'         => array(),
					'search'           => array(),
					'showimages'       => array(),
					'template'         => array(),
					'numberofevents'   => array(),
					'fetchmonths'      => array(),
					'showvenue'        => array(),
					'groupbycity'      => array(),
					'showmore'         => array(),
					'eid'              => array(),
					'eventinquiry'     => array(),
					'orderby'          => array(),
					'order'            => array(),
				),
			) );

			register_rest_route( $this->namespace, '/eventlist', array(
				'methods'  => 'POST',
				'callback' => 'edu_api_eventlist',
				'args'     => array(
					'objectid'       => array( 'required' => true ),
					'city'           => array(),
					'groupbycity'    => array(),
					'baseUrl'        => array(),
					'courseFolder'   => array(),
					'showmore'       => array(),
					'spotsleft'      => array(),
					'fewspots'       => array(),
					'spotsettings'   => array(),
					'eid'            => array(),
					'numberofevents' => array(),
					'fetchmonths'    => array(),
					'showvenue'      => array(),
					'eventinquiry'   => array(),
				),
			) );

			register_rest_route( $this->namespace, '/loginwidget', array(
				'methods'  => 'POST',
				'callback' => 'edu_api_loginwidget',
				'args'     => array(
					'baseUrl'      => array(),
					'courseFolder' => array(),
					'logintext'    => array(),
					'logouttext'   => array(),
					'guesttext'    => array(),
				),
			) );

			register_rest_route( $this->namespace, '/coupon/check', array(
				'methods'  => 'POST',
				'callback' => 'edu_api_check_coupon_code',
				'args'     => array(
					'code'       => array( 'required' => true ),
					'objectId'   => array( 'required' => true ),
					'categoryId' => array( 'required' => true ),
				),
			) );
		}
	}