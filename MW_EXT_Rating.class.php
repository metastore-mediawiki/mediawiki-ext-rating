<?php

namespace MediaWiki\Extension\MW_EXT_Rating;

use OutputPage, Parser, PPFrame, Skin;
use MediaWiki\Extension\MW_EXT_Core\MW_EXT_Core;

/**
 * Class MW_EXT_Rating
 */
class MW_EXT_Rating {

	/**
	 * Register tag function.
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws \MWException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'rating', [ __CLASS__, 'onRenderTag' ], Parser::SFH_OBJECT_ARGS );

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
	 * @throws \ConfigException
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onRenderTag( Parser $parser, PPFrame $frame, $args = [] ) {
		// Get options parser.
		$getOption = MW_EXT_Core::extractOptions( $args, $frame );

		// Argument: title.
		$getTitle = MW_EXT_Core::outClear( $getOption['title'] ?? '' ?: '' );
		$outTitle = $getTitle;

		// Argument: count.
		$getCount = MW_EXT_Core::outClear( $getOption['count'] ?? '' ?: '' );
		$outCount = $getCount;

		// Argument: icon-plus.
		$getIconPlus = MW_EXT_Core::outClear( $getOption['icon-plus'] ?? '' ?: 'fas fa-star' );
		$outIconPlus = $getIconPlus;

		// Argument: icon-minus.
		$getIconMinus = MW_EXT_Core::outClear( $getOption['icon-minus'] ?? '' ?: 'far fa-star' );
		$outIconMinus = $getIconMinus;

		// Setting: MW_EXT_Rating_minCount.
		$setMinCount = MW_EXT_Core::getConfig( 'MW_EXT_Rating_minCount' );

		// Setting: MW_EXT_Rating_maxCount.
		$setMaxCount = MW_EXT_Core::getConfig( 'MW_EXT_Rating_maxCount' );

		// Check rating title, count, set error category.
		if ( empty( $outTitle ) || ! ctype_digit( $getCount ) || $getCount > $setMaxCount ) {
			$parser->addTrackingCategory( 'mw-ext-rating-error-category' );

			return null;
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
		$outHTML = '<div class="mw-ext-rating mw-ext-rating-count-' . $outCount . ' navigation-not-searchable" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">';
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
	 * Load resource function.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 *
	 * @return bool
	 * -------------------------------------------------------------------------------------------------------------- */

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( [ 'ext.mw.rating.styles' ] );

		return true;
	}
}
