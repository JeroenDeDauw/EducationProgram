/**
 * JavaScript for the Education Program MediaWiki extension.
 * @see https://www.mediawiki.org/wiki/Extension:Reviews
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

( function ( $, mw ) {

	var compatMode = null,
	ep = {
		msg: function () {
			// Yeah, doing it here, since "mw.loader.using( 'mediawiki.language',"
			// does not have mediaWiki.language loaded.
			if ( compatMode === null ) {
				compatMode = window.mediaWiki.language.gender === undefined;

				if ( compatMode ) {
					mw.language.gender = function( gender, forms ) {
						if ( !forms || forms.length === 0 ) {
							return '';
						}
						forms = mw.language.preConvertPlural( forms, 2 );
						if ( gender === 'male' ) {
							return forms[0];
						}
						if ( gender === 'female' ) {
							return forms[1];
						}
						return ( forms.length === 3 ) ? forms[2] : forms[0];
					};

					mw.jqueryMsg.htmlEmitter.prototype.gender = function( nodes ) {
						var gender, forms;
						if  ( nodes[0] && nodes[0].options instanceof mw.Map ){
							gender = nodes[0].options.get( 'gender' );
						} else {
							gender = nodes[0];
						}
						forms = nodes.slice(1);
						return this.language.gender( gender, forms );
					};
				}
			}

			if ( compatMode ) {
				return gM.apply( this, arguments );
			}
			else {
				return mw.msg.apply( this, arguments );
			}
		},

		msge: function () {
			return mw.html.escape( this.msg.apply( this, arguments ) );
		}
	};

	mw.educationProgram = ep;

}( jQuery, mediaWiki ) );
