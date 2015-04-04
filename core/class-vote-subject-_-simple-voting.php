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
 * @subpackage Simple_Voting/processor
 * @author     Seoringer <seoringer@gmail.com>
 */
class Vote_Subject___Simple_Voting {

	private $MAX_CONTENT_LENGTH = 500; 	// No more than $this->MAX_CONTENT_LENGTH symbols can be stored in the database.
	private $MAX_URL_LENGTH = 300;		// No more than $this->MAX_URL_LENGTH symbols can be stored in the database.
	
	private $errors;

	private $subjects_table_name;
	private $votes_table_name;
	
	private $subjectID;
	private $subjectCreated;
	private $subjectContent;
	private $subjectURL;
	private $subjectRandomKey;
	private $rating;
	private $votesCount;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $voting_args, $voting_content ) {

		$this->errors = array();
		
		global $wpdb;
		$this->subjects_table_name = $wpdb->prefix . 'simple_voting_subjects';
		$this->votes_table_name = $wpdb->prefix . 'simple_voting_votes';

		$this->subjectID = 0;
		$this->subjectContent = substr( trim( strip_tags( $voting_content ) ), 0, $this->MAX_CONTENT_LENGTH );
		$this->subjectURL = $this->extractURL( $voting_content );
		$this->subjectRandomKey = "";
		$this->rating = 0;
		$this->votesCount = 0;
		
		if( empty( $this->subjectContent ) ) {
			$this->errors[] = "subjectContent is empty.";
			if( isset( $_GET["sv_textID"] ) || isset( $_POST["sv_textID"] ) ) {
				if( isset( $_GET["sv_textID"] ) )	$sv_textID = $_GET["sv_textID"];
				else								$sv_textID = $_POST["sv_textID"];
				if( strlen( $sv_textID ) > 3 ) {
					$this->subjectRandomKey = substr( $sv_textID, 0, 3 );
					$this->subjectID = intval( substr( $sv_textID, 3 ) );
				}
			} else {
				$this->errors[] = "sv_textID is NOT set.";
				if( is_single() || is_page() ) {
					$this->subjectContent = get_the_title();
				}
			}
		}
		
		if( !empty( $this->subjectID ) || !empty( $this->subjectContent ) ) {
			$this->errors[] = "synchroniseWithDatabase.";
			$this->synchroniseWithDatabase();
		} else {
			$this->errors[] = "Can not initialise object.";
		}
	}

	//******************************************************************************************************
	public function isComplete() {
		return ("" != trim( $this->getContent() ));
	}

	//******************************************************************************************************
	public function getErrors( $errorsDelimiter = "" ) {
		return implode( $this->errors, $errorsDelimiter );
	}

	//******************************************************************************************************
	public function getID() {
		return $this->subjectID;
	}

	//******************************************************************************************************
	public function getTextID() {
		return $this->subjectRandomKey . $this->subjectID;
	}

	//******************************************************************************************************
	public function getContent() {
		return $this->subjectContent;
	}

	//******************************************************************************************************
	public function getVotesCount() {
		return $this->votesCount;
	}

	//******************************************************************************************************
	public function getRating( $precision = 1 ) {
		return $this->rating;
	}

	//******************************************************************************************************
	public function getDefaultVotingPage() {
		return plugin_dir_url( dirname( __FILE__ ) ) . "core/vote-processor-_-simple-voting.php";
	}

	//******************************************************************************************************
	public function getAdminHtmlCode() {
		$result = "";
		if( current_user_can( 'manage_options' ) ) {
			$actionURL = admin_url('admin-ajax.php');
			$svNonce = wp_create_nonce( "admin-form-simple-voting" );
			
			$result = <<<adminHTML
				<div class='admin-area_-simple-voting'>
					<form method='post' name='svAdminForm' action="{$actionURL}" class="admin-form_-simple-voting">
						<input type="hidden" name="action" value="admin_simple_voting"></input>
						<input type="hidden" name="sv_textID" value="{$this->getTextID()}"></input>
						<input type="hidden" name="sv_nonce" value="{$svNonce}"></input>
						<input style="width:100%;" type="submit" value="You are admin. Check answers"></input>
					</form>
				</div>
adminHTML;
		}
		return $result;
	}
	
	//******************************************************************************************************
	public function getHtmlStatistics() {
		
		$resultClassSuffix = "best";	// "best" by default.
		$votingResult = $this->getRating( 2 );
		if( $votingResult < 2 ) {
			$resultClassSuffix = "worst";
		} else if( $votingResult < 4 ) {
			$resultClassSuffix = "bad";
		} else if( $votingResult < 7 ) {
			$resultClassSuffix = "average";
		} else if( $votingResult < 9.5 ) {
			$resultClassSuffix = "good";
		}
		
		return( implode( array(
			"<div class='voting-result-_-simple-voting voting-result-{$resultClassSuffix}-_-simple-voting'>",
				"{$votingResult}<span class='resulting-space-_-simple-voting'>span</span>({$this->getVotesCount()} ",
				"<span class='resulting-men-_-simple-voting'>voters</span>)",
			"</div>",
			$this->getAdminHtmlCode()
		) )	);
	}
	
	//******************************************************************************************************
	public function getXmlStatistics() {
		
		return implode( array(
			"<simple-voting>",
				"<sv_subjectId>",
					"{$this->getTextID()}",
				"</sv_subjectId>",
				"<sv_result>",
					"{$this->getRating( 2 )}",
				"</sv_result>",
				"<sv_votes>",
					"{$this->getVotesCount()}",
				"</sv_votes>",
				"<sv_htmlStatistics>",
					"<![CDATA[",
						"{$this->getHtmlStatistics()}",
					"]]>",
				"</sv_htmlStatistics>",
			"</simple-voting>" )
			);
	}
	
	//******************************************************************************************************
	public function getURL() {
		return $this->subjectURL;
	}
	
	//******************************************************************************************************
	public function enlistVote( $voteValue, $sUserName, $sUserEmail, $sUserComment ) {
		global $wpdb;
		
		$sql = "INSERT INTO {$this->votes_table_name} (id_subject, voteValue, userName, userEmail, userComment) VALUES (%d, %d, %s, %s, %s) ON DUPLICATE KEY UPDATE voteValue = %d, userName = %s, userComment = %s";
		$sql = $wpdb->prepare( $sql, $this->getID(), $voteValue, $sUserName, $sUserEmail, $sUserComment,
								$voteValue, $sUserName, $sUserComment);
		$wpdb->query($sql);
		
		// Now we must recalculate and rewrite subject statistics.
		$this->votesCount = $wpdb->get_var( $wpdb->prepare( 'SELECT count(1) FROM '. $this->votes_table_name.' WHERE id_subject = %d', $this->subjectID ) );
		$votesSum = $wpdb->get_var( $wpdb->prepare( 'SELECT SUM(voteValue) FROM '. $this->votes_table_name.' WHERE id_subject = %d', $this->subjectID ) );

		$this->rating = 0;
		if( $this->getVotesCount() > 0 ) {
			$this->rating = round( $votesSum / $this->getVotesCount(), 2 );
		}
		
		$wpdb->update( 
			$this->subjects_table_name,
			array( 
				'rating' => $this->getRating(),
				'votes' => $this->getVotesCount()
			), 
			array( 'id' => $this->getID() ), 
			array( 
				'%s',
				'%d'
			), 
			array( '%d' ) 
		);
	}
	
	//******************************************************************************************************
	private function synchroniseWithDatabase() {

		// Will try to load data either by $this->subjectContent or by ($this->subjectID AND $this->subjectRandomKey)
	
		global $wpdb;

		$subjects =
			$wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM '. $this->subjects_table_name.' WHERE content = %s OR (id = %d AND randomKey = %s)',
					$this->subjectContent,
					$this->subjectID,
					$this->subjectRandomKey
				)
			);
			
		if( count( $subjects ) > 1 ) {/* We have a trouble, but I do not know how to tell about it. */}
		if( count( $subjects ) > 0 ) {
			$this->subjectID = $subjects[0]->id;
			$this->subjectCreated = $subjects[0]->created;
			$this->subjectContent = $subjects[0]->content;
			$this->subjectURL = $subjects[0]->URL;
			$this->subjectRandomKey = $subjects[0]->randomKey;
			$this->rating = $subjects[0]->rating;
			$this->votesCount = $subjects[0]->votes;
		} else if( !empty( $this->subjectContent ) ) {
		
			// This subject is not yet in the database.
			// Let's add it!

			$this->subjectRandomKey = $this->generateRandomString( 3 );
			
			$wpdb->insert( $this->subjects_table_name, 
				array(
					'content' => $this->subjectContent,
					'URL' => $this->subjectURL,
					'randomKey' => $this->subjectRandomKey ),
				array( '%s', '%s', '%s' )
			);
			
			$this->subjectID = $wpdb->insert_id;
		}
	}
	
	//******************************************************************************************************
	public function getVotesAsHtmlTable() {
		
		$result = "";
		
		if( $this->isComplete() ) {
			
			$result .= implode( array(
				"<h2>{$this->getContent()}</h2>",
				"<table class='votes-table-_-simple-voting'>",
					"<thead>",
						"<th>".__("Time", "simple-voting")."</th>",
						"<th>".__("Rating", "simple-voting")."</th>",
						"<th>".__("User name", "simple-voting")."</th>",
						"<th>".__("User EMail", "simple-voting")."</th>",
						"<th>".__("User comment", "simple-voting")."</th>",
					"</thead>",
					"<tbody>"
			));
			
			global $wpdb;

			$votes =
				$wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM '. $this->votes_table_name.' WHERE id_subject = %d ORDER BY voteTime',
						$this->subjectID
					)
				);

			foreach( $votes as $key => $row) {
				$result .= "<tr>";
				$result .= "<td>{$row->voteTime}</td>";
				$result .= "<td>{$row->voteValue}</td>";
				$result .= "<td>{$row->userName}</td>";
				$result .= "<td>{$row->userEmail}</td>";
				$result .= "<td>{$row->userComment}</td>";
				$result .= "</tr>";
			}
			
			$result .= "</tbody></table>";
		}
		return $result;
	}
	
	//******************************************************************************************************
	private function generateRandomString( $length ) {
		$result = "";

		for( $i = 0; $i < $length; $i++ ) {
			$result .= chr( 65 + mt_rand( 0, 25 ) );
		}

		return $result;
	}
	
	//******************************************************************************************************
	private function extractURL( $raw_content ) {
		$result = "";
		
		$content = trim( strip_tags( implode( explode( "\n", $raw_content ), " " ) ) );
		
		$URL_startPos = strpos( strtolower( $content ), "http" );
		if( false === $URL_startPos )  {
			// Nothing to do :)
		} else {
			$URL_finishPos = strpos( $content, " ", $URL_startPos );
			if( false === $URL_finishPos )  {
				$URL_length = strlen( $content );	// Till the end of content.
			} else {
				$URL_length = $URL_finishPos - $URL_startPos;
			}
			$result = substr( $content, $URL_startPos, $URL_length );
		}
		return substr( $result, 0, $this->MAX_URL_LENGTH );
	}
}
