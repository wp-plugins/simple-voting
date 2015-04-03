<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://seoringer.com/simple-voting-plugin-for-wordpress/
 * @since      1.0.0
 *
 * @package    Simple_Voting
 * @subpackage Simple_Voting/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Simple_Voting
 * @subpackage Simple_Voting/public
 * @author     Seoringer <seoringer@gmail.com>
 */
class Shortcode_Processor___Simple_Voting {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	//******************************************************************************************************
	public function enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public-_-simple-voting.css', array(), $this->version, 'all' );

	}

	//******************************************************************************************************
	public function enqueue_scripts() {

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/public-_-simple-voting.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	//******************************************************************************************************
	public function hookIt() {

	   add_shortcode( 'voting', array( $this, 'show_voting_form' ) );
	   add_shortcode( 'voted', array( $this, 'show_vote_result' ) );

	   //if ( (current_user_can('edit_posts') || current_user_can('edit_pages'))
	   //	&& get_user_option('rich_editing') == 'true' ) {

	   //   add_filter( 'mce_external_plugins', array( $this, 'add_mce_plugin' ) );
	   //   add_filter( 'mce_buttons', array( $this, 'register_mce_button' ) );
	   //}
	}
	
	//******************************************************************************************************
	public function show_vote_result( $atts, $content ) {
		
		$result = "";
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core/class-vote-subject-_-simple-voting.php';
		$voteSubject = new Vote_Subject___Simple_Voting( $atts, $content );
		
		if( $voteSubject->isComplete() ) {
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			wp_enqueue_style(
				"sv_votingStyle",
				$plugin_url . "core/css/voting-_-simple-voting.css"
			);
			
			$result = implode( array(
				"<div name='svVotingDiv{$voteSubject->getTextID()}' class='voting-area-_-simple-voting'>",
					$voteSubject->getHtmlStatistics(),
				"</div>"
			));
		}
		
		return $result;
	}
	
	//******************************************************************************************************
	public function show_voting_form( $atts, $content ) {
	
		$result = "";

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core/class-vote-subject-_-simple-voting.php';
		$voteSubject = new Vote_Subject___Simple_Voting( $atts, $content );

		if( $voteSubject->isComplete() ) {
            
			$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
			
			$userNameLabel = __("Please enter a username." );
			if( "Please enter a username." == $userNameLabel ) $userNameLabel = __("Your name:", "simple-voting");
			$userCommentLabel = __("Your Comment" );
			if( "Your Comment" == $userCommentLabel ) $userCommentLabel = __("Comments:", "simple-voting");
			//$ratingLabel = __("Please rate:", "simple-voting");
			$ratingLabel = " ";
			
			$svNonce = wp_create_nonce( "simple-voting" );
			
			$result .= <<<svHtml_part1
				<div name='svVotingDiv{$voteSubject->getTextID()}' class='voting-area-_-simple-voting'>
					<form method='post' name='svVotingForm' action='{$voteSubject->getURL()}'>
						
						<input type="hidden" name="action" value="simple_voting"></input>
						<input type="hidden" name="sv_vote" value="0"></input>
						<input type="hidden" name="sv_textID" value="{$voteSubject->getTextID()}"></input>
						<input type="hidden" name="sv_nonce" value="{$svNonce}"></input>
						
						<div class="user-data-_-simple-voting">
							<DIV name="svUserNameGroup">
								<label name="sv-user-name-label" class="text-label-_-simple-voting"><span class="mandatory">*</span>{$userNameLabel}</label>
								<input name="svUserName" class="text-input-_-simple-voting" type="text"></input>
							</DIV>
							<DIV name="svUserEmailGroup">
								<label name="sv-user-email-label" class="text-label-_-simple-voting"><span class="mandatory">*</span>Email:</label>
								<input name="svUserEmail" class="text-input-_-simple-voting" type="email"></input>
							</DIV>
							<DIV>
								<label name="sv-user-comment-label" class="text-label-_-simple-voting">{$userCommentLabel}</label>
								<textarea name="svUserComment" class="text-input-_-simple-voting user-comment-_-simple-voting"></textarea>
							</DIV>
						</div>
					</form>
					<div name="svVotingGroup">
						<div class='voting-label-_-simple-voting'>{$ratingLabel}</div>
						<div name="svVotingElements">
svHtml_part1;
			
			for($i = 1; $i <= 10; $i++) {
				$result .= <<<svHtml_voteSpan
					<span class='voting-element-_-simple-voting' onclick='voteClicked(event, "{$i}")' ><a href='#'>{$i}</a></span>
svHtml_voteSpan;
			}
			
			$result .= <<<svHtml_part2
						</div>
					</div>
					{$voteSubject->getAdminHtmlCode()}
				</div>
svHtml_part2;

			wp_enqueue_style(
				"sv_votingStyle",
				$plugin_url . "core/css/voting-_-simple-voting.css"
			);
			
			wp_enqueue_script(
				"sv_votingJS",
				$plugin_url . "core/js/voting-_-simple-voting.js",
				array( "wp-util" ),
				"1.0.0",
				true //makes sure this is enqueued in the footer
			);
			
			// We can not add ajax action right here, because of the wordpress limitations.
			// So, ajax action is adding in the Starter___Simple_Voting class.
		}
		
		return $result;
		//$resultStr .= "<xmp>";
	}
	
	//******************************************************************************************************
	public function processVote() {
		// This is ajax-called function.
		
		if(	!isset($_POST["sv_vote"]) ||
			!isset($_POST["sv_textID"]) || 
			!isset($_POST["svUserName"]) || 
			!isset($_POST["svUserEmail"]) || 
			!isset($_POST["svUserComment"]) || 
			!isset($_POST["sv_nonce"]) ) wp_send_json_error( "Wrong data set." );
			
		if( !wp_verify_nonce( $_POST['sv_nonce'], 'simple-voting' ) ) wp_send_json_error( "Wrong data source." );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core/class-vote-subject-_-simple-voting.php';
		$voteSubject = new Vote_Subject___Simple_Voting( "", "" );
	
		if( !$voteSubject->isComplete() ) wp_send_json_error( $voteSubject->getErrors() );
		
		$voteValue = intval( $_POST["sv_vote"] );
		if( 10 < $voteValue || $voteValue < 1 ) wp_send_json_error( "Wrong vote." );
		
		$voteSubject->enlistVote(
			$voteValue,
			$_POST["svUserName"],
			$_POST["svUserEmail"],
			$_POST["svUserComment"]
		);
		wp_send_json_success( $voteSubject->getXmlStatistics() ); // sends json_encoded success=true
	}

	//******************************************************************************************************
	public function processAdminAjaxRequest() {
		if(	!isset($_POST["sv_textID"]) || 
			!isset($_POST["sv_nonce"]) ) die( "Wrong data set." );

		if( !wp_verify_nonce( $_POST['sv_nonce'], 'admin-form-simple-voting' ) ) die( "Wrong data source." );
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core/class-vote-subject-_-simple-voting.php';
		$voteSubject = new Vote_Subject___Simple_Voting( "", "" );

		if( !$voteSubject->isComplete() ) die( $voteSubject->getErrors() );
		
		echo <<<htmlCode_part1
		<html>
			<head>
				<style>
					table, td, th {
						border: 1px solid black;
						border-collapse: collapse;
					}
				</style>
			</head>
			<body>
htmlCode_part1;

		echo $voteSubject->getVotesAsHtmlTable();
		
		echo <<<htmlCode_part2
			</body>
		</html>
htmlCode_part2;
		
		die;
	}
	
	/*
	//******************************************************************************************************
	public function add_mce_plugin( $plugin_array ) {
	
	   $plugin_array['simple_voting'] = plugin_dir_url( __FILE__ ) . 'js/simple-voting.js';
	   return $plugin_array;
	}

	//******************************************************************************************************
	public function register_mce_button( $buttons ) {
	
	   array_push( $buttons, "simple_voting_button" );
	   return $buttons;
	}
	*/
}
