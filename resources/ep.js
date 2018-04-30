/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Reviews
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

( function ( mw ) {

	mw.educationProgram = {
		msg: function () {
			return mw.msg.apply( this, arguments );
		}
	};

}( mediaWiki ) );
