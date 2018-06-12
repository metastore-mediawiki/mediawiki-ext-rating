<?php

/**
 * Class MW_EXT_Rating
 */
class MW_EXT_Rating {

	/**
	 * * Clear DATA (escape html).
	 *
	 * @param $string
	 *
	 * @return string
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function clearData( $string ) {
		$outString = htmlspecialchars( trim( $string ), ENT_QUOTES );

		return $outString;
	}

	/**
	 * Get configuration parameters.
	 *
	 * @param $getData
	 *
	 * @return mixed
	 * @throws ConfigException
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function getConfig( $getData ) {
		$context   = new RequestContext();
		$getConfig = $context->getConfig()->get( $getData );

		return $getConfig;
	}

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'rating', __CLASS__ . '::onRenderTag', Parser::SFH_OBJECT_ARGS );

		return true;
	}

	/**
	 * Render tag function.
	 *
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param array $args
	 *
	 * @return string
	 * @throws ConfigException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onRenderTag( Parser $parser, PPFrame $frame, array $args ) {
		// Get options parser.
		$getOptions = self::extractOptions( $args, $frame );

		// Argument: title.
		$getTitle = self::clearData( $getOptions['title'] ?? '' ?: '' );
		$outTitle = $getTitle;

		// Argument: count.
		$getCount = self::clearData( $getOptions['count'] ?? '' ?: '' );
		$outCount = $getCount;

		// Argument: icon-plus.
		$getIconPlus = self::clearData( $getOptions['icon-plus'] ?? '' ?: 'fas fa-star' );
		$outIconPlus = $getIconPlus;

		// Argument: icon-minus.
		$getIconMinus = self::clearData( $getOptions['icon-minus'] ?? '' ?: 'far fa-star' );
		$outIconMinus = $getIconMinus;

		// Setting: MW_EXT_Rating_minCount.
		$setMinCount = self::getConfig( 'MW_EXT_Rating_minCount' );

		// Setting: MW_EXT_Rating_maxCount.
		$setMaxCount = self::getConfig( 'MW_EXT_Rating_maxCount' );

		// Check rating title, count, set error category.
		if ( empty( $outTitle ) || ! ctype_digit( $getCount ) || $getCount > $setMaxCount ) {
			$parser->addTrackingCategory( 'mw-ext-rating-error-category' );

			return false;
		}

		$outStars = '';

		// Out rating: icon-plus.
		for ( $i = 1; $i <= $getCount; $i ++ ) {
			$outStars .= '<span class="' . $outIconPlus . ' fa-fw mw-ext-rating-star mw-ext-rating-star-plus"></span>';
		}

		// Out rating: icon-minus.
		while ( $i <= $setMaxCount ) {
			$outStars .= '<span class="' . $outIconMinus . ' fa-fw mw-ext-rating-star mw-ext-rating-star-minus"></span>';
			$i ++;
		}

		// Out HTML.
		$outHTML = '<div class="mw-ext-rating mw-ext-rating-count-' . $outCount . '" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
		$outHTML .= '<div class="mw-ext-rating-body"><div class="mw-ext-rating-content">';
		$outHTML .= '<div class="mw-ext-rating-text">' . $outTitle . '</div>';
		$outHTML .= '<div class="mw-ext-rating-count">' . $outStars . '</div>';
		$outHTML .= '</div></div>';
		$outHTML .= '<meta itemprop="worstRating" content = "' . $setMinCount . '" />';
		$outHTML .= '<meta itemprop="ratingValue" content = "' . $outCount . '" />';
		$outHTML .= '<meta itemprop="bestRating" content = "' . $setMaxCount . '" />';
		$outHTML .= '</div>';

		// Out parser.
		$outParser = $outHTML;

		return $outParser;
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true.
	 *
	 * @param array $options
	 * @param PPFrame $frame
	 *
	 * @return array
	 * -------------------------------------------------------------------------------------------------------------- */

	private static function extractOptions( array $options, PPFrame $frame ) {
		$results = [];

		foreach ( $options as $option ) {
			$pair = explode( '=', $frame->expand( $option ), 2 );

			if ( count( $pair ) === 2 ) {
				$name             = self::clearData( $pair[0] );
				$value            = self::clearData( $pair[1] );
				$results[ $name ] = $value;
			}
		}

		return $results;
	}

	/**
	 * Load resource function.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 *
	 * @return bool
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( array( 'ext.mw.rating.styles' ) );

		return true;
	}
}
